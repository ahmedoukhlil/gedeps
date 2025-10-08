<?php
/**
 * TEST DU FLUX DES SIGNATURES SÉQUENTIELLES
 * Script pour diagnostiquer les problèmes de passage au signataire suivant
 */

// Configuration d'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Test du Flux des Signatures Séquentielles</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

// ========================================
// 1. VÉRIFICATION DES DOCUMENTS SÉQUENTIELS
// ========================================
echo "<div class='section'>";
echo "<h2>📄 Documents avec signatures séquentielles</h2>";

try {
    // Simuler l'environnement Laravel
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    // Récupérer tous les documents avec signatures séquentielles
    $documents = \App\Models\Document::where('sequential_signatures', true)
        ->with(['sequentialSignatures.user'])
        ->get();
    
    if ($documents->count() > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Statut</th><th>Index Actuel</th><th>Signataires</th><th>Actions</th></tr>";
        
        foreach ($documents as $document) {
            $signatures = $document->sequentialSignatures;
            $signatureList = [];
            
            foreach ($signatures as $signature) {
                $status = $signature->status;
                $order = $signature->signature_order;
                $userName = $signature->user->name ?? 'Inconnu';
                $signatureList[] = "#$order: $userName ($status)";
            }
            
            echo "<tr>";
            echo "<td>{$document->id}</td>";
            echo "<td>{$document->document_name}</td>";
            echo "<td>{$document->status}</td>";
            echo "<td>{$document->current_signature_index}</td>";
            echo "<td>" . implode('<br>', $signatureList) . "</td>";
            echo "<td>";
            echo "<a href='/signatures-simple/{$document->id}' target='_blank'>Voir</a> | ";
            echo "<a href='/signatures-simple/debug' target='_blank'>Debug</a>";
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>Aucun document avec signatures séquentielles trouvé.</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Erreur lors de la récupération des documents: " . $e->getMessage() . "</p>";
}

echo "</div>";

// ========================================
// 2. TEST POUR CHAQUE UTILISATEUR
// ========================================
echo "<div class='section'>";
echo "<h2>👥 Test pour chaque utilisateur</h2>";

try {
    $users = \App\Models\User::whereHas('sequentialSignatures')->get();
    
    if ($users->count() > 0) {
        echo "<table>";
        echo "<tr><th>Utilisateur</th><th>Documents à signer</th><th>Documents en attente</th><th>Documents complétés</th></tr>";
        
        foreach ($users as $user) {
            $userId = $user->id;
            
            // Récupérer les documents pour cet utilisateur
            $userDocuments = \App\Models\Document::where('sequential_signatures', true)
                ->whereIn('status', ['in_progress', 'pending'])
                ->whereHas('sequentialSignatures', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->with(['sequentialSignatures'])
                ->get();
            
            $toSign = 0;
            $waiting = 0;
            $completed = 0;
            
            foreach ($userDocuments as $document) {
                $userSignature = $document->sequentialSignatures()
                    ->where('user_id', $userId)
                    ->first();
                
                if ($userSignature) {
                    if ($userSignature->status === 'signed') {
                        $completed++;
                    } elseif ($userSignature->status === 'pending' && $userSignature->signature_order == $document->current_signature_index + 1) {
                        $toSign++;
                    } else {
                        $waiting++;
                    }
                }
            }
            
            echo "<tr>";
            echo "<td>{$user->name} (ID: {$user->id})</td>";
            echo "<td class='" . ($toSign > 0 ? 'success' : 'info') . "'>$toSign</td>";
            echo "<td class='" . ($waiting > 0 ? 'warning' : 'info') . "'>$waiting</td>";
            echo "<td class='info'>$completed</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>Aucun utilisateur avec signatures séquentielles trouvé.</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Erreur lors de la récupération des utilisateurs: " . $e->getMessage() . "</p>";
}

echo "</div>";

// ========================================
// 3. DIAGNOSTIC DÉTAILLÉ
// ========================================
echo "<div class='section'>";
echo "<h2>🔍 Diagnostic détaillé</h2>";

try {
    $documents = \App\Models\Document::where('sequential_signatures', true)
        ->whereIn('status', ['in_progress', 'pending'])
        ->with(['sequentialSignatures.user'])
        ->get();
    
    foreach ($documents as $document) {
        echo "<h3>Document: {$document->document_name} (ID: {$document->id})</h3>";
        echo "<p><strong>Statut:</strong> {$document->status}</p>";
        echo "<p><strong>Index actuel:</strong> {$document->current_signature_index}</p>";
        
        $signatures = $document->sequentialSignatures;
        echo "<h4>Signataires:</h4>";
        echo "<table>";
        echo "<tr><th>Ordre</th><th>Utilisateur</th><th>Statut</th><th>Tour actuel?</th><th>Doit apparaître?</th></tr>";
        
        foreach ($signatures as $signature) {
            $isCurrentTurn = $signature->signature_order == $document->current_signature_index + 1;
            $shouldShow = $signature->status === 'pending' && $isCurrentTurn;
            
            $turnClass = $isCurrentTurn ? 'success' : 'info';
            $showClass = $shouldShow ? 'success' : 'info';
            
            echo "<tr>";
            echo "<td>{$signature->signature_order}</td>";
            echo "<td>{$signature->user->name} (ID: {$signature->user_id})</td>";
            echo "<td class='" . ($signature->status === 'signed' ? 'success' : 'warning') . "'>{$signature->status}</td>";
            echo "<td class='$turnClass'>" . ($isCurrentTurn ? 'OUI' : 'NON') . "</td>";
            echo "<td class='$showClass'>" . ($shouldShow ? 'OUI' : 'NON') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "<hr>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Erreur lors du diagnostic: " . $e->getMessage() . "</p>";
}

echo "</div>";

// ========================================
// 4. COMMANDES DE CORRECTION
// ========================================
echo "<div class='section'>";
echo "<h2>🔧 Commandes de correction</h2>";

echo "<h3>Si le problème persiste, exécutez ces commandes :</h3>";
echo "<pre>";
echo "# Vérifier les signatures séquentielles\n";
echo "php artisan tinker\n";
echo "\\App\\Models\\SequentialSignature::all();\n\n";
echo "# Vérifier un document spécifique\n";
echo "\\App\\Models\\Document::find(1)->sequentialSignatures;\n\n";
echo "# Forcer la mise à jour d'un document\n";
echo "\\App\\Models\\Document::find(1)->update(['current_signature_index' => 0]);\n\n";
echo "# Vérifier les logs\n";
echo "tail -f storage/logs/laravel.log\n";
echo "</pre>";

echo "<h3>URLs de test :</h3>";
echo "<ul>";
echo "<li><a href='/signatures-simple' target='_blank'>Liste des signatures séquentielles</a></li>";
echo "<li><a href='/signatures-simple/debug' target='_blank'>Debug des signatures séquentielles</a></li>";
echo "</ul>";

echo "</div>";

echo "<hr>";
echo "<p><em>Test généré le " . date('Y-m-d H:i:s') . "</em></p>";
?>
