<?php

use App\Models\Document;
use App\Models\User;
use App\Models\SequentialSignature;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création d'un Document Séquentiel de Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f7f6; color: #333; }
        .container { max-width: 900px; margin: auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        h1, h2 { color: #0056b3; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px; margin-top: 30px; }
        h1 { font-size: 28px; }
        h2 { font-size: 22px; }
        .section { background-color: #e9f7ef; border-left: 5px solid #28a745; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .section.warning { background-color: #fff3cd; border-left: 5px solid #ffc107; }
        .section.error { background-color: #f8d7da; border-left: 5px solid #dc3545; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { color: #007bff; }
        .highlight { background-color: #e6f7ff; }
        .button { display: inline-block; padding: 10px 20px; margin-top: 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; transition: background-color 0.3s ease; }
        .button:hover { background-color: #0056b3; }
        .code { background-color: #f0f0f0; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📝 Création d'un Document Séquentiel de Test</h1>
        <p class="info">Ce script crée un document séquentiel de test pour vérifier les notifications.</p>

        <h2>✅ Connexion à la base de données</h2>
        <?php
        try {
            DB::connection()->getPdo();
            echo "<p class='success'>✅ Connexion à la base de données réussie</p>";
        } catch (\Exception $e) {
            echo "<p class='error'>❌ Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
            exit;
        }

        echo "<h2>👥 Vérification des Utilisateurs</h2>";
        
        // Vérifier qu'il y a au moins 3 utilisateurs
        $users = User::where('id', '!=', 1)->take(3)->get();
        
        if ($users->count() < 2) {
            echo "<p class='error'>❌ Pas assez d'utilisateurs pour créer un document séquentiel (minimum 2 requis)</p>";
            echo "<p class='info'>💡 Créez d'abord des utilisateurs dans l'application.</p>";
        } else {
            echo "<p class='success'>✅ Utilisateurs disponibles : " . $users->count() . "</p>";
            
            echo "<div class='section'>";
            echo "<h3>👤 Utilisateurs Disponibles</h3>";
            echo "<table>";
            echo "<thead><tr><th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th></tr></thead>";
            echo "<tbody>";
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>{$user->id}</td>";
                echo "<td>{$user->name}</td>";
                echo "<td>{$user->email}</td>";
                echo "<td>" . ($user->isAdmin() ? 'Admin' : ($user->isAgent() ? 'Agent' : 'Utilisateur')) . "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</div>";

            echo "<h2>📄 Création du Document de Test</h2>";
            
            try {
                // Créer un fichier PDF de test
                $testPdfContent = "%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj

2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj

3 0 obj
<<
/Type /Page
/Parent 2 0 R
/MediaBox [0 0 612 792]
/Contents 4 0 R
>>
endobj

4 0 obj
<<
/Length 44
>>
stream
BT
/F1 12 Tf
72 720 Td
(Document de Test Séquentiel) Tj
ET
endstream
endobj

xref
0 5
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
0000000204 00000 n 
trailer
<<
/Size 5
/Root 1 0 R
>>
startxref
297
%%EOF";

                $filename = 'test-sequential-' . time() . '.pdf';
                $path = 'documents/' . $filename;
                
                // Sauvegarder le fichier
                Storage::disk('public')->put($path, $testPdfContent);
                
                // Créer le document
                $document = Document::create([
                    'document_name' => 'Test Séquentiel - ' . date('Y-m-d H:i'),
                    'type' => 'rapport',
                    'description' => 'Document de test pour les signatures séquentielles',
                    'path_original' => $path,
                    'filename_original' => $filename,
                    'file_size' => strlen($testPdfContent),
                    'mime_type' => 'application/pdf',
                    'status' => 'in_progress',
                    'uploaded_by' => 1, // Admin
                    'sequential_signatures' => true,
                    'current_signature_index' => 0,
                    'signature_queue' => $users->pluck('id')->toArray(),
                    'completed_signatures' => [],
                ]);
                
                echo "<p class='success'>✅ Document créé avec succès : <strong>{$document->document_name}</strong></p>";
                
                // Créer les signatures séquentielles
                $order = 1;
                foreach ($users as $user) {
                    SequentialSignature::create([
                        'document_id' => $document->id,
                        'user_id' => $user->id,
                        'signature_order' => $order,
                        'status' => 'pending'
                    ]);
                    $order++;
                }
                
                echo "<p class='success'>✅ Signatures séquentielles créées : " . $users->count() . " signataires</p>";
                
                // Notifier le premier signataire
                $firstSigner = $users->first();
                $agent = User::find(1); // Admin
                
                $notificationService = new NotificationService();
                $result = $notificationService->notifyDocumentAssigned($document, $firstSigner, $agent);
                
                if ($result) {
                    echo "<p class='success'>✅ Notification envoyée au premier signataire : <strong>{$firstSigner->name}</strong></p>";
                } else {
                    echo "<p class='error'>❌ Échec de l'envoi de la notification au premier signataire</p>";
                }
                
                echo "<div class='section'>";
                echo "<h3>📊 Résumé du Document Créé</h3>";
                echo "<p><strong>ID :</strong> {$document->id}</p>";
                echo "<p><strong>Nom :</strong> {$document->document_name}</p>";
                echo "<p><strong>Statut :</strong> {$document->status}</p>";
                echo "<p><strong>Type :</strong> {$document->type}</p>";
                echo "<p><strong>Signataires :</strong> " . $users->count() . "</p>";
                echo "<p><strong>Index actuel :</strong> {$document->current_signature_index}</p>";
                echo "</div>";
                
                echo "<div class='section'>";
                echo "<h3>👥 Ordre des Signatures</h3>";
                echo "<table>";
                echo "<thead><tr><th>Ordre</th><th>Nom</th><th>Email</th><th>Statut</th></tr></thead>";
                echo "<tbody>";
                foreach ($document->sequentialSignatures as $signature) {
                    echo "<tr>";
                    echo "<td>{$signature->signature_order}</td>";
                    echo "<td>{$signature->user->name}</td>";
                    echo "<td>{$signature->user->email}</td>";
                    echo "<td class='info'>{$signature->status}</td>";
                    echo "</tr>";
                }
                echo "</tbody></table>";
                echo "</div>";
                
            } catch (\Exception $e) {
                echo "<p class='error'>❌ Erreur lors de la création du document : " . $e->getMessage() . "</p>";
            }
        }

        echo "<h2>🔗 URLs de Test</h2>";
        echo "<ul>";
        echo "<li><a href='/test-sequential-notifications.php' target='_blank'>Test des notifications</a></li>";
        echo "<li><a href='/signatures-simple' target='_blank'>Signatures séquentielles</a></li>";
        echo "<li><a href='/documents/history' target='_blank'>Historique des documents</a></li>";
        echo "<li><a href='/fix-sequential-notifications.php' target='_blank'>Correction des notifications</a></li>";
        echo "</ul>";

        echo "<h2>💡 Instructions</h2>";
        echo "<div class='section'>";
        echo "<h3>🧪 Test du Workflow</h3>";
        echo "<p>Pour tester le workflow séquentiel :</p>";
        echo "<ol>";
        echo "<li>Connectez-vous avec le premier signataire</li>";
        echo "<li>Allez sur <code>/signatures-simple</code></li>";
        echo "<li>Signez le document</li>";
        echo "<li>Vérifiez que le deuxième signataire reçoit une notification</li>";
        echo "<li>Répétez pour tous les signataires</li>";
        echo "<li>Vérifiez que l'agent reçoit une notification de finalisation</li>";
        echo "</ol>";
        echo "</div>";

        echo "<p class='info'>Document de test créé le " . date('Y-m-d H:i:s') . "</p>";
        ?>
    </div>
</body>
</html>
