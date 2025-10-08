<?php

use App\Models\Document;
use App\Models\User;
use App\Models\SequentialSignature;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

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
    <title>Test des Notifications Séquentielles</title>
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
        <h1>🧪 Test des Notifications Séquentielles</h1>
        <p class="info">Ce script teste le système de notifications par email pour les signatures séquentielles.</p>

        <h2>✅ Connexion à la base de données</h2>
        <?php
        try {
            DB::connection()->getPdo();
            echo "<p class='success'>✅ Connexion à la base de données réussie</p>";
        } catch (\Exception $e) {
            echo "<p class='error'>❌ Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
            exit;
        }

        echo "<h2>📧 Test des Notifications</h2>";
        
        // Trouver un document séquentiel pour tester
        $document = Document::where('sequential_signatures', true)
            ->where('status', 'in_progress')
            ->with(['sequentialSignatures.user'])
            ->first();

        if (!$document) {
            echo "<p class='error'>❌ Aucun document séquentiel en cours trouvé pour les tests.</p>";
            echo "<p class='info'>💡 Créez d'abord un document avec signatures séquentielles.</p>";
        } else {
            echo "<p class='success'>✅ Document séquentiel trouvé : <strong>{$document->document_name}</strong></p>";
            
            // Afficher les informations du document
            echo "<div class='section'>";
            echo "<h3>📄 Informations du Document</h3>";
            echo "<p><strong>ID :</strong> {$document->id}</p>";
            echo "<p><strong>Nom :</strong> {$document->document_name}</p>";
            echo "<p><strong>Statut :</strong> {$document->status}</p>";
            echo "<p><strong>Index actuel :</strong> {$document->current_signature_index}</p>";
            echo "<p><strong>Progression :</strong> {$document->getSignatureProgress()}%</p>";
            echo "</div>";

            // Afficher les signataires
            echo "<div class='section'>";
            echo "<h3>👥 Signataires</h3>";
            echo "<table>";
            echo "<thead><tr><th>Ordre</th><th>Nom</th><th>Email</th><th>Statut</th><th>Signé le</th></tr></thead>";
            echo "<tbody>";
            foreach ($document->sequentialSignatures as $signature) {
                $statusClass = $signature->status === 'signed' ? 'success' : 'info';
                $signedAt = $signature->signed_at ? $signature->signed_at->format('d/m/Y H:i') : 'N/A';
                echo "<tr>";
                echo "<td>{$signature->signature_order}</td>";
                echo "<td>{$signature->user->name}</td>";
                echo "<td>{$signature->user->email}</td>";
                echo "<td class='{$statusClass}'>{$signature->status}</td>";
                echo "<td>{$signedAt}</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</div>";

            // Test des notifications
            echo "<h2>📧 Test des Notifications</h2>";
            
            $notificationService = new NotificationService();
            
            // Test 1: Notification au prochain signataire
            echo "<div class='section'>";
            echo "<h3>🔄 Test 1: Notification au prochain signataire</h3>";
            
            $nextSigner = $document->sequentialSignatures()
                ->where('signature_order', $document->current_signature_index + 1)
                ->first();
            
            if ($nextSigner) {
                echo "<p class='info'>Prochain signataire : <strong>{$nextSigner->user->name}</strong> ({$nextSigner->user->email})</p>";
                
                // Simuler un signataire précédent
                $previousSigner = User::find(1); // Admin par défaut
                
                try {
                    $result = $notificationService->notifyNextSequentialSigner($document, $nextSigner->user, $previousSigner);
                    if ($result) {
                        echo "<p class='success'>✅ Notification au prochain signataire envoyée avec succès</p>";
                    } else {
                        echo "<p class='error'>❌ Échec de l'envoi de la notification au prochain signataire</p>";
                    }
                } catch (\Exception $e) {
                    echo "<p class='error'>❌ Erreur lors de l'envoi de la notification : " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<p class='info'>ℹ️ Aucun prochain signataire trouvé (document peut-être terminé)</p>";
            }
            echo "</div>";

            // Test 2: Notification de finalisation
            echo "<div class='section'>";
            echo "<h3>🎉 Test 2: Notification de finalisation</h3>";
            
            $agent = $document->uploader;
            if ($agent) {
                echo "<p class='info'>Agent à notifier : <strong>{$agent->name}</strong> ({$agent->email})</p>";
                
                try {
                    $result = $notificationService->notifySequentialSignatureCompleted($document, $agent);
                    if ($result) {
                        echo "<p class='success'>✅ Notification de finalisation envoyée avec succès</p>";
                    } else {
                        echo "<p class='error'>❌ Échec de l'envoi de la notification de finalisation</p>";
                    }
                } catch (\Exception $e) {
                    echo "<p class='error'>❌ Erreur lors de l'envoi de la notification de finalisation : " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<p class='error'>❌ Aucun agent trouvé pour ce document</p>";
            }
            echo "</div>";

            // Test 3: Notification d'assignation initiale
            echo "<div class='section'>";
            echo "<h3>📝 Test 3: Notification d'assignation initiale</h3>";
            
            $firstSigner = $document->sequentialSignatures()
                ->where('signature_order', 1)
                ->first();
            
            if ($firstSigner) {
                echo "<p class='info'>Premier signataire : <strong>{$firstSigner->user->name}</strong> ({$firstSigner->user->email})</p>";
                
                try {
                    $result = $notificationService->notifyDocumentAssigned($document, $firstSigner->user, $agent);
                    if ($result) {
                        echo "<p class='success'>✅ Notification d'assignation initiale envoyée avec succès</p>";
                    } else {
                        echo "<p class='error'>❌ Échec de l'envoi de la notification d'assignation initiale</p>";
                    }
                } catch (\Exception $e) {
                    echo "<p class='error'>❌ Erreur lors de l'envoi de la notification d'assignation : " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<p class='error'>❌ Aucun premier signataire trouvé</p>";
            }
            echo "</div>";
        }

        echo "<h2>📋 Configuration Email</h2>";
        echo "<div class='section'>";
        echo "<h3>⚙️ Vérification de la configuration</h3>";
        
        // Vérifier la configuration email
        $mailConfig = [
            'MAIL_MAILER' => env('MAIL_MAILER', 'non configuré'),
            'MAIL_HOST' => env('MAIL_HOST', 'non configuré'),
            'MAIL_PORT' => env('MAIL_PORT', 'non configuré'),
            'MAIL_USERNAME' => env('MAIL_USERNAME', 'non configuré'),
            'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION', 'non configuré'),
            'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS', 'non configuré'),
            'MAIL_FROM_NAME' => env('MAIL_FROM_NAME', 'non configuré'),
        ];
        
        echo "<table>";
        echo "<thead><tr><th>Paramètre</th><th>Valeur</th><th>Statut</th></tr></thead>";
        echo "<tbody>";
        foreach ($mailConfig as $key => $value) {
            $status = $value !== 'non configuré' ? 'success' : 'error';
            $statusText = $value !== 'non configuré' ? '✅ Configuré' : '❌ Non configuré';
            echo "<tr>";
            echo "<td><strong>{$key}</strong></td>";
            echo "<td>{$value}</td>";
            echo "<td class='{$status}'>{$statusText}</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
        echo "</div>";

        echo "<h2>🔗 URLs de test</h2>";
        echo "<ul>";
        echo "<li><a href='/signatures-simple' target='_blank'>Signatures séquentielles</a></li>";
        echo "<li><a href='/documents/history' target='_blank'>Historique des documents</a></li>";
        echo "<li><a href='/debug-sequential-flow-issue.php' target='_blank'>Diagnostic du flux séquentiel</a></li>";
        echo "</ul>";

        echo "<h2>💡 Recommandations</h2>";
        echo "<div class='section'>";
        echo "<h3>📧 Configuration Email</h3>";
        echo "<p>Pour que les notifications fonctionnent, assurez-vous que :</p>";
        echo "<ul>";
        echo "<li>La configuration SMTP est correcte dans le fichier <code>.env</code></li>";
        echo "<li>Les paramètres <code>MAIL_*</code> sont définis</li>";
        echo "<li>Le serveur peut envoyer des emails</li>";
        echo "<li>Les templates d'email existent dans <code>resources/views/emails/</code></li>";
        echo "</ul>";
        echo "</div>";

        echo "<p class='info'>Test généré le " . date('Y-m-d H:i:s') . "</p>";
        ?>
    </div>
</body>
</html>