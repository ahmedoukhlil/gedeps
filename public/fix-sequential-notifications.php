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
    <title>Correction des Notifications Séquentielles</title>
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
        <h1>🔧 Correction des Notifications Séquentielles</h1>
        <p class="info">Ce script corrige et teste le système de notifications par email pour les signatures séquentielles.</p>

        <h2>✅ Connexion à la base de données</h2>
        <?php
        try {
            DB::connection()->getPdo();
            echo "<p class='success'>✅ Connexion à la base de données réussie</p>";
        } catch (\Exception $e) {
            echo "<p class='error'>❌ Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
            exit;
        }

        echo "<h2>📧 Correction des Notifications</h2>";
        
        // Trouver tous les documents séquentiels
        $documents = Document::where('sequential_signatures', true)
            ->with(['sequentialSignatures.user', 'uploader'])
            ->get();

        if ($documents->isEmpty()) {
            echo "<p class='info'>ℹ️ Aucun document séquentiel trouvé.</p>";
        } else {
            echo "<p class='success'>✅ Documents séquentiels trouvés : " . $documents->count() . "</p>";
            
            $notificationService = new NotificationService();
            $correctionsCount = 0;
            
            foreach ($documents as $document) {
                echo "<div class='section'>";
                echo "<h3>📄 Document : {$document->document_name}</h3>";
                echo "<p><strong>Statut :</strong> {$document->status} | <strong>Index :</strong> {$document->current_signature_index}</p>";
                
                // Vérifier si le document est terminé
                if ($document->status === 'signed') {
                    echo "<p class='info'>📧 Envoi de la notification de finalisation à l'agent...</p>";
                    
                    try {
                        $result = $notificationService->notifySequentialSignatureCompleted($document, $document->uploader);
                        if ($result) {
                            echo "<p class='success'>✅ Notification de finalisation envoyée à {$document->uploader->name}</p>";
                            $correctionsCount++;
                        } else {
                            echo "<p class='error'>❌ Échec de l'envoi de la notification de finalisation</p>";
                        }
                    } catch (\Exception $e) {
                        echo "<p class='error'>❌ Erreur : " . $e->getMessage() . "</p>";
                    }
                } else {
                    // Document en cours - notifier le prochain signataire
                    $nextSigner = $document->sequentialSignatures()
                        ->where('signature_order', $document->current_signature_index + 1)
                        ->first();
                    
                    if ($nextSigner) {
                        echo "<p class='info'>📧 Envoi de la notification au prochain signataire : {$nextSigner->user->name}</p>";
                        
                        // Trouver le signataire précédent
                        $previousSigner = $document->sequentialSignatures()
                            ->where('signature_order', $document->current_signature_index)
                            ->first();
                        
                        $previousUser = $previousSigner ? $previousSigner->user : $document->uploader;
                        
                        try {
                            $result = $notificationService->notifyNextSequentialSigner($document, $nextSigner->user, $previousUser);
                            if ($result) {
                                echo "<p class='success'>✅ Notification envoyée à {$nextSigner->user->name}</p>";
                                $correctionsCount++;
                            } else {
                                echo "<p class='error'>❌ Échec de l'envoi de la notification</p>";
                            }
                        } catch (\Exception $e) {
                            echo "<p class='error'>❌ Erreur : " . $e->getMessage() . "</p>";
                        }
                    } else {
                        echo "<p class='info'>ℹ️ Aucun prochain signataire trouvé</p>";
                    }
                }
                echo "</div>";
            }
            
            echo "<div class='section'>";
            echo "<h3>📊 Résumé des Corrections</h3>";
            echo "<p class='success'>✅ <strong>{$correctionsCount}</strong> notification(s) envoyée(s) avec succès</p>";
            echo "<p class='info'>📧 Vérifiez les boîtes email des destinataires</p>";
            echo "</div>";
        }

        echo "<h2>🧪 Test de Configuration Email</h2>";
        echo "<div class='section'>";
        echo "<h3>⚙️ Vérification de la configuration</h3>";
        
        // Vérifier la configuration email
        $mailConfig = [
            'MAIL_MAILER' => env('MAIL_MAILER'),
            'MAIL_HOST' => env('MAIL_HOST'),
            'MAIL_PORT' => env('MAIL_PORT'),
            'MAIL_USERNAME' => env('MAIL_USERNAME'),
            'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
            'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
            'MAIL_FROM_NAME' => env('MAIL_FROM_NAME'),
        ];
        
        $configOk = true;
        echo "<table>";
        echo "<thead><tr><th>Paramètre</th><th>Valeur</th><th>Statut</th></tr></thead>";
        echo "<tbody>";
        foreach ($mailConfig as $key => $value) {
            $status = !empty($value) ? 'success' : 'error';
            $statusText = !empty($value) ? '✅ Configuré' : '❌ Non configuré';
            if (empty($value)) $configOk = false;
            echo "<tr>";
            echo "<td><strong>{$key}</strong></td>";
            echo "<td>" . ($value ?: 'Non défini') . "</td>";
            echo "<td class='{$status}'>{$statusText}</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
        
        if ($configOk) {
            echo "<p class='success'>✅ Configuration email complète</p>";
        } else {
            echo "<p class='error'>❌ Configuration email incomplète - Vérifiez le fichier .env</p>";
        }
        echo "</div>";

        echo "<h2>📋 Templates d'Email</h2>";
        echo "<div class='section'>";
        echo "<h3>📄 Vérification des templates</h3>";
        
        $templates = [
            'resources/views/emails/sequential-signature.blade.php' => 'Template séquentiel',
            'resources/views/emails/layout.blade.php' => 'Layout principal',
            'resources/views/emails/document-assigned.blade.php' => 'Document assigné',
            'resources/views/emails/document-signed.blade.php' => 'Document signé',
        ];
        
        echo "<table>";
        echo "<thead><tr><th>Fichier</th><th>Description</th><th>Statut</th></tr></thead>";
        echo "<tbody>";
        foreach ($templates as $file => $description) {
            $exists = file_exists($file);
            $status = $exists ? 'success' : 'error';
            $statusText = $exists ? '✅ Existe' : '❌ Manquant';
            echo "<tr>";
            echo "<td><code>{$file}</code></td>";
            echo "<td>{$description}</td>";
            echo "<td class='{$status}'>{$statusText}</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
        echo "</div>";

        echo "<h2>🔗 URLs de test</h2>";
        echo "<ul>";
        echo "<li><a href='/test-sequential-notifications.php' target='_blank'>Test des notifications</a></li>";
        echo "<li><a href='/signatures-simple' target='_blank'>Signatures séquentielles</a></li>";
        echo "<li><a href='/documents/history' target='_blank'>Historique des documents</a></li>";
        echo "<li><a href='/debug-sequential-flow-issue.php' target='_blank'>Diagnostic du flux</a></li>";
        echo "</ul>";

        echo "<h2>💡 Instructions</h2>";
        echo "<div class='section'>";
        echo "<h3>📧 Configuration Email</h3>";
        echo "<p>Pour que les notifications fonctionnent :</p>";
        echo "<ol>";
        echo "<li>Configurez les paramètres SMTP dans le fichier <code>.env</code></li>";
        echo "<li>Vérifiez que les templates d'email existent</li>";
        echo "<li>Testez l'envoi d'emails avec le script de test</li>";
        echo "<li>Vérifiez les logs Laravel pour les erreurs</li>";
        echo "</ol>";
        echo "</div>";

        echo "<p class='info'>Correction effectuée le " . date('Y-m-d H:i:s') . "</p>";
        ?>
    </div>
</body>
</html>