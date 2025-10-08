<?php
/**
 * CORRECTION AUTOMATIQUE DES SIGNATURES - GEDEPS
 * Script de correction pour les problèmes de signature en production
 */

// Configuration d'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Correction des Signatures - GEDEPS</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
    .command { background: #000; color: #0f0; padding: 10px; border-radius: 3px; font-family: monospace; }
</style>";

// Fonction pour exécuter des commandes
function runCommand($command, $description) {
    echo "<div class='section'>";
    echo "<h3>$description</h3>";
    echo "<div class='command'>$command</div>";
    
    $output = [];
    $return_code = 0;
    exec($command . ' 2>&1', $output, $return_code);
    
    if ($return_code === 0) {
        echo "<p class='success'>✅ Commande exécutée avec succès</p>";
        if (!empty($output)) {
            echo "<pre>" . implode("\n", $output) . "</pre>";
        }
    } else {
        echo "<p class='error'>❌ Erreur lors de l'exécution (code: $return_code)</p>";
        if (!empty($output)) {
            echo "<pre>" . implode("\n", $output) . "</pre>";
        }
    }
    echo "</div>";
    return $return_code === 0;
}

// ========================================
// 1. CORRECTION DES PERMISSIONS
// ========================================
echo "<div class='section'>";
echo "<h2>📁 Correction des permissions</h2>";

$directories_to_fix = [
    'storage/app',
    'storage/app/public',
    'storage/app/public/documents',
    'storage/app/public/documents/signed',
    'storage/app/public/signatures',
    'storage/logs',
    'bootstrap/cache'
];

foreach ($directories_to_fix as $dir) {
    $full_path = __DIR__ . '/../' . $dir;
    if (file_exists($full_path)) {
        // Définir les permissions
        chmod($full_path, 0755);
        echo "<p class='success'>✅ Permissions corrigées pour $dir</p>";
    } else {
        // Créer le dossier s'il n'existe pas
        if (mkdir($full_path, 0755, true)) {
            echo "<p class='success'>✅ Dossier créé: $dir</p>";
        } else {
            echo "<p class='error'>❌ Impossible de créer le dossier: $dir</p>";
        }
    }
}

echo "</div>";

// ========================================
// 2. CORRECTION DU PROPRIÉTAIRE
// ========================================
echo "<div class='section'>";
echo "<h2>👤 Correction du propriétaire</h2>";

// Détecter l'utilisateur web
$web_user = 'www-data'; // Par défaut pour Ubuntu/Debian
if (function_exists('posix_getpwuid')) {
    $process_user = posix_getpwuid(posix_geteuid());
    if ($process_user && isset($process_user['name'])) {
        $web_user = $process_user['name'];
    }
}

echo "<p class='info'>Utilisateur web détecté: $web_user</p>";

// Changer le propriétaire des dossiers
$chown_command = "chown -R $web_user:$web_user storage/ bootstrap/cache/";
runCommand($chown_command, "Changement du propriétaire des dossiers");

echo "</div>";

// ========================================
// 3. NETTOYAGE DU CACHE
// ========================================
echo "<div class='section'>";
echo "<h2>🧹 Nettoyage du cache</h2>";

// Vider le cache Laravel
runCommand("cd " . dirname(__DIR__) . " && php artisan cache:clear", "Vidage du cache Laravel");
runCommand("cd " . dirname(__DIR__) . " && php artisan config:clear", "Vidage du cache de configuration");
runCommand("cd " . dirname(__DIR__) . " && php artisan route:clear", "Vidage du cache des routes");
runCommand("cd " . dirname(__DIR__) . " && php artisan view:clear", "Vidage du cache des vues");

echo "</div>";

// ========================================
// 4. RÉGÉNÉRATION DES LIENS SYMBOLIQUES
// ========================================
echo "<div class='section'>";
echo "<h2>🔗 Régénération des liens symboliques</h2>";

// Supprimer l'ancien lien s'il existe
$public_storage = __DIR__ . '/storage';
if (is_link($public_storage)) {
    unlink($public_storage);
    echo "<p class='info'>Ancien lien symbolique supprimé</p>";
}

// Créer le nouveau lien
runCommand("cd " . dirname(__DIR__) . " && php artisan storage:link", "Création du lien symbolique storage");

echo "</div>";

// ========================================
// 5. VÉRIFICATION DES EXTENSIONS PHP
// ========================================
echo "<div class='section'>";
echo "<h2>🔧 Vérification des extensions PHP</h2>";

$required_extensions = ['gd', 'imagick', 'curl', 'json', 'mbstring', 'openssl'];

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✅ Extension $ext disponible</p>";
    } else {
        echo "<p class='error'>❌ Extension $ext manquante</p>";
        echo "<p class='warning'>Pour installer: <code>sudo apt-get install php-$ext</code></p>";
    }
}

echo "</div>";

// ========================================
// 6. TEST DE CRÉATION DE SIGNATURE
// ========================================
echo "<div class='section'>";
echo "<h2>🧪 Test de création de signature</h2>";

try {
    if (extension_loaded('gd')) {
        // Créer un dossier de test
        $test_dir = __DIR__ . '/../storage/app/public/test-signatures';
        if (!file_exists($test_dir)) {
            mkdir($test_dir, 0755, true);
        }
        
        // Créer une image de test
        $image = imagecreate(200, 100);
        if ($image) {
            $bg_color = imagecolorallocate($image, 255, 255, 255);
            $text_color = imagecolorallocate($image, 0, 0, 0);
            $border_color = imagecolorallocate($image, 0, 0, 255);
            
            // Dessiner un rectangle
            imagerectangle($image, 5, 5, 195, 95, $border_color);
            
            // Ajouter du texte
            imagestring($image, 5, 50, 40, 'TEST', $text_color);
            
            $test_file = $test_dir . '/test-signature-' . time() . '.png';
            if (imagepng($image, $test_file)) {
                echo "<p class='success'>✅ Test de création de signature réussi</p>";
                echo "<p class='info'>Fichier de test créé: " . basename($test_file) . "</p>";
                
                // Vérifier que le fichier est accessible
                $public_url = '/storage/test-signatures/' . basename($test_file);
                echo "<p class='info'>URL publique: <a href='$public_url' target='_blank'>$public_url</a></p>";
            } else {
                echo "<p class='error'>❌ Échec de sauvegarde de la signature de test</p>";
            }
            
            imagedestroy($image);
        } else {
            echo "<p class='error'>❌ Impossible de créer une image de test</p>";
        }
    } else {
        echo "<p class='error'>❌ Extension GD non disponible</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur lors du test: " . $e->getMessage() . "</p>";
}

echo "</div>";

// ========================================
// 7. VÉRIFICATION DE LA CONFIGURATION
// ========================================
echo "<div class='section'>";
echo "<h2>⚙️ Vérification de la configuration</h2>";

// Vérifier le fichier .env
$env_path = __DIR__ . '/../.env';
if (file_exists($env_path)) {
    $env_content = file_get_contents($env_path);
    
    // Vérifier les variables importantes
    $important_vars = [
        'APP_ENV' => 'production',
        'APP_DEBUG' => 'false',
        'FILESYSTEM_DISK' => 'public'
    ];
    
    foreach ($important_vars as $var => $expected) {
        if (preg_match("/^$var=(.*)$/m", $env_content, $matches)) {
            $value = trim($matches[1]);
            if ($value === $expected) {
                echo "<p class='success'>✅ $var = $value (correct)</p>";
            } else {
                echo "<p class='warning'>⚠️ $var = $value (attendu: $expected)</p>";
            }
        } else {
            echo "<p class='error'>❌ $var non défini</p>";
        }
    }
} else {
    echo "<p class='error'>❌ Fichier .env non trouvé</p>";
}

echo "</div>";

// ========================================
// 8. COMMANDES DE DIAGNOSTIC AVANCÉ
// ========================================
echo "<div class='section'>";
echo "<h2>🔍 Commandes de diagnostic avancé</h2>";

echo "<h3>Commandes à exécuter sur le serveur :</h3>";
echo "<pre>";
echo "# Vérifier les permissions détaillées\n";
echo "ls -la storage/\n\n";
echo "# Vérifier les logs en temps réel\n";
echo "tail -f storage/logs/laravel.log\n\n";
echo "# Vérifier l'espace disque\n";
echo "df -h\n\n";
echo "# Vérifier la mémoire\n";
echo "free -h\n\n";
echo "# Vérifier les processus PHP\n";
echo "ps aux | grep php\n\n";
echo "# Redémarrer les services web\n";
echo "sudo systemctl restart apache2  # ou nginx\n";
echo "sudo systemctl restart php8.1-fpm  # ou version appropriée\n";
echo "</pre>";

echo "<h3>Si les problèmes persistent :</h3>";
echo "<ol>";
echo "<li><strong>Vérifiez les logs:</strong> <code>tail -f storage/logs/laravel.log</code></li>";
echo "<li><strong>Vérifiez les permissions:</strong> <code>ls -la storage/</code></li>";
echo "<li><strong>Redémarrez les services:</strong> <code>sudo systemctl restart apache2</code></li>";
echo "<li><strong>Vérifiez la configuration PHP:</strong> <code>php -m</code></li>";
echo "<li><strong>Testez manuellement:</strong> Créez un fichier PHP simple pour tester GD</li>";
echo "</ol>";

echo "</div>";

// ========================================
// 9. SCRIPT DE TEST MANUEL
// ========================================
echo "<div class='section'>";
echo "<h2>🧪 Script de test manuel</h2>";

echo "<p>Créez un fichier <code>test-signature-manual.php</code> avec le contenu suivant :</p>";
echo "<pre>";
echo "<?php\n";
echo "// Test manuel de création de signature\n";
echo "header('Content-Type: image/png');\n";
echo "\n";
echo "// Créer une image\n";
echo "\$image = imagecreate(300, 150);\n";
echo "\$bg = imagecolorallocate(\$image, 255, 255, 255);\n";
echo "\$text = imagecolorallocate(\$image, 0, 0, 0);\n";
echo "\$border = imagecolorallocate(\$image, 0, 0, 255);\n";
echo "\n";
echo "// Dessiner\n";
echo "imagerectangle(\$image, 10, 10, 290, 140, \$border);\n";
echo "imagestring(\$image, 5, 100, 60, 'SIGNATURE TEST', \$text);\n";
echo "\n";
echo "// Afficher\n";
echo "imagepng(\$image);\n";
echo "imagedestroy(\$image);\n";
echo "?>";
echo "</pre>";

echo "</div>";

echo "<hr>";
echo "<p><em>Correction générée le " . date('Y-m-d H:i:s') . "</em></p>";
echo "<p><strong>Note:</strong> Après avoir exécuté ce script, testez la création de signature dans votre application.</p>";
?>
