<?php
/**
 * DIAGNOSTIC DE LA LISTE DES SIGNATURES SÉQUENTIELLES
 * Script pour identifier pourquoi les documents n'apparaissent pas
 */

// Configuration d'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnostic - Signatures Séquentielles</h1>";
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
    .debug-step { background: #f9f9f9; padding: 10px; margin: 10px 0; border-left: 4px solid #007cba; }
</style>";

// ========================================
// 1. VÉRIFICATION DE L'UTILISATEUR CONNECTÉ
// ========================================
echo "<div class='section'>";
echo "<h2>👤 Utilisateur connecté</h2>";

try {
    // Simuler l'environnement Laravel
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    // Démarrer la session
    session_start();
    
    // Vérifier l'utilisateur connecté
    $user = auth()->user();
    if ($user) {
        echo "<div class='debug-step'>";
        echo "<p class='success'>✅ Utilisateur connecté: {$user->name} (ID: {$user->id})</p>";
        echo "<p><strong>Rôle:</strong> " . ($user->role ? $user->role->name : 'Aucun rôle') . "</p>";
        echo "<p><strong>Est admin:</strong> " . ($user->isAdmin() ? 'OUI' : 'NON') . "</p>";
        echo "</div>";
    } else {
        echo "<p class='error'>❌ Aucun utilisateur connecté</p>";
        echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
        echo "<p><strong>Session data:</strong> " . print_r($_SESSION, true) . "</p>";
        echo "<p>Connectez-vous d'abord à l'application.</p>";
        echo "<p><a href='/login' target='_blank'>Aller à la page de connexion</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur lors de la vérification de l'utilisateur: " . $e->getMessage() . "</p>";
}

echo "</div>";

// ========================================
// 2. VÉRIFICATION DES DOCUMENTS SÉQUENTIELS
// ========================================
echo "<div class='section'>";
echo "<h2>📄 Documents avec signatures séquentielles</h2>";

try {
    $documents = \App\Models\Document::where('sequential_signatures', true)
        ->whereIn('status', ['in_progress', 'pending'])
        ->get();
    
    echo "<div class='debug-step'>";
    echo "<p><strong>Total documents séquentiels:</strong> " . $documents->count() . "</p>";
    echo "</div>";
    
    if ($documents->count() > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Statut</th><th>Index</th><th>Signataires</th></tr>";
        
        foreach ($documents as $document) {
            $signatures = $document->sequentialSignatures;
            $signatureCount = $signatures->count();
            
            echo "<tr>";
            echo "<td>{$document->id}</td>";
            echo "<td>{$document->document_name}</td>";
            echo "<td>{$document->status}</td>";
            echo "<td>{$document->current_signature_index}</td>";
            echo "<td>{$signatureCount} signataire(s)</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Aucun document avec signatures séquentielles trouvé.</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur lors de la récupération des documents: " . $e->getMessage() . "</p>";
}

echo "</div>";

// ========================================
// 3. VÉRIFICATION DES SIGNATURES SÉQUENTIELLES
// ========================================
echo "<div class='section'>";
echo "<h2>✍️ Signatures séquentielles</h2>";

try {
    $user = auth()->user();
    if ($user) {
        $userId = $user->id;
        
        // Récupérer les signatures séquentielles pour cet utilisateur
        $userSignatures = \App\Models\SequentialSignature::where('user_id', $userId)
            ->with(['document', 'user'])
            ->get();
        
        echo "<div class='debug-step'>";
        echo "<p><strong>Signatures séquentielles pour cet utilisateur:</strong> " . $userSignatures->count() . "</p>";
        echo "</div>";
        
        if ($userSignatures->count() > 0) {
            echo "<table>";
            echo "<tr><th>Document</th><th>Ordre</th><th>Statut</th><th>Document Statut</th><th>Index</th><th>Tour?</th></tr>";
            
            foreach ($userSignatures as $signature) {
                $document = $signature->document;
                $isCurrentTurn = $signature->signature_order == $document->current_signature_index + 1;
                $shouldShow = $signature->status === 'pending' && $isCurrentTurn;
                
                $turnClass = $isCurrentTurn ? 'success' : 'info';
                $showClass = $shouldShow ? 'success' : 'info';
                
                echo "<tr>";
                echo "<td>{$document->document_name} (ID: {$document->id})</td>";
                echo "<td>{$signature->signature_order}</td>";
                echo "<td class='" . ($signature->status === 'signed' ? 'success' : 'warning') . "'>{$signature->status}</td>";
                echo "<td>{$document->status}</td>";
                echo "<td>{$document->current_signature_index}</td>";
                echo "<td class='$turnClass'>" . ($isCurrentTurn ? 'OUI' : 'NON') . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p class='warning'>⚠️ Aucune signature séquentielle trouvée pour cet utilisateur.</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur lors de la récupération des signatures: " . $e->getMessage() . "</p>";
}

echo "</div>";

// ========================================
// 4. SIMULATION DE LA LOGIQUE DU CONTRÔLEUR
// ========================================
echo "<div class='section'>";
echo "<h2>🧪 Simulation de la logique du contrôleur</h2>";

try {
    $user = auth()->user();
    if ($user) {
        $userId = $user->id;
        
        // Reproduire exactement la logique du contrôleur
        $allDocuments = \App\Models\Document::where('sequential_signatures', true)
            ->whereIn('status', ['in_progress', 'pending'])
            ->whereHas('sequentialSignatures', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['sequentialSignatures.user', 'uploader'])
            ->orderBy('updated_at', 'desc')
            ->get();
        
        echo "<div class='debug-step'>";
        echo "<p><strong>Documents trouvés par la requête:</strong> " . $allDocuments->count() . "</p>";
        echo "</div>";
        
        $documentsToSign = collect();
        $documentsWaiting = collect();
        $documentsCompleted = collect();
        
        foreach ($allDocuments as $document) {
            $userSignature = $document->sequentialSignatures()
                ->where('user_id', $userId)
                ->first();
            
            if ($userSignature) {
                if ($userSignature->status === 'signed') {
                    $documentsCompleted->push($document);
                } elseif ($userSignature->status === 'pending' && $userSignature->signature_order == $document->current_signature_index + 1) {
                    $documentsToSign->push($document);
                } else {
                    $documentsWaiting->push($document);
                }
            }
        }
        
        echo "<div class='debug-step'>";
        echo "<p><strong>Documents à signer:</strong> " . $documentsToSign->count() . "</p>";
        echo "<p><strong>Documents en attente:</strong> " . $documentsWaiting->count() . "</p>";
        echo "<p><strong>Documents complétés:</strong> " . $documentsCompleted->count() . "</p>";
        echo "</div>";
        
        if ($documentsToSign->count() > 0) {
            echo "<h3>📝 Documents à signer:</h3>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Nom</th><th>Statut</th><th>Index</th><th>Ordre Signature</th></tr>";
            
            foreach ($documentsToSign as $document) {
                $userSignature = $document->sequentialSignatures()
                    ->where('user_id', $userId)
                    ->first();
                
                echo "<tr>";
                echo "<td>{$document->id}</td>";
                echo "<td>{$document->document_name}</td>";
                echo "<td>{$document->status}</td>";
                echo "<td>{$document->current_signature_index}</td>";
                echo "<td>{$userSignature->signature_order}</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p class='warning'>⚠️ Aucun document à signer trouvé.</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur lors de la simulation: " . $e->getMessage() . "</p>";
}

echo "</div>";

// ========================================
// 5. COMMANDES DE CORRECTION
// ========================================
echo "<div class='section'>";
echo "<h2>🔧 Commandes de correction</h2>";

echo "<h3>Si aucun document n'apparaît, vérifiez :</h3>";
echo "<ol>";
echo "<li><strong>L'utilisateur a-t-il des signatures séquentielles ?</strong></li>";
echo "<li><strong>Les documents sont-ils en statut 'in_progress' ou 'pending' ?</strong></li>";
echo "<li><strong>L'index current_signature_index est-il correct ?</strong></li>";
echo "<li><strong>Les signatures séquentielles ont-elles le bon statut 'pending' ?</strong></li>";
echo "</ol>";

echo "<h3>Commandes de diagnostic :</h3>";
echo "<pre>";
echo "# Vérifier les signatures séquentielles\n";
echo "php artisan tinker\n";
echo "\\App\\Models\\SequentialSignature::where('user_id', 1)->get();\n\n";
echo "# Vérifier un document spécifique\n";
echo "\\App\\Models\\Document::find(1)->sequentialSignatures;\n\n";
echo "# Vérifier l'index actuel\n";
echo "\\App\\Models\\Document::where('sequential_signatures', true)->get(['id', 'current_signature_index']);\n";
echo "</pre>";

echo "<h3>URLs de test :</h3>";
echo "<ul>";
echo "<li><a href='/signatures-simple' target='_blank'>Liste des signatures séquentielles</a></li>";
echo "<li><a href='/signatures-simple/debug' target='_blank'>Debug des signatures séquentielles</a></li>";
echo "<li><a href='/test-sequential-flow.php' target='_blank'>Test complet du flux</a></li>";
echo "</ul>";

echo "</div>";

echo "<hr>";
echo "<p><em>Diagnostic généré le " . date('Y-m-d H:i:s') . "</em></p>";
?>
