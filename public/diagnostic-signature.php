<?php
/**
 * DIAGNOSTIC SIGNATURE - GEDEPS
 * Script de diagnostic pour les problèmes de signature en production
 */

// Configuration d'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnostic des Signatures - GEDEPS</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>";

// ========================================
// 1. VÉRIFICATION DE L'ENVIRONNEMENT
// ========================================
echo "<div class='section'>";
echo "<h2>🌍 Environnement</h2>";

// Version PHP
echo "<p><strong>Version PHP:</strong> " . phpversion() . "</p>";

// Système d'exploitation
echo "<p><strong>Système:</strong> " . php_uname() . "</p>";

// Serveur web
echo "<p><strong>Serveur:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Inconnu') . "</p>";

// Mémoire disponible
echo "<p><strong>Mémoire limit:</strong> " . ini_get('memory_limit') . "</p>";

// Timeout d'exécution
echo "<p><strong>Max execution time:</strong> " . ini_get('max_execution_time') . "s</p>";

echo "</div>";

// ========================================
// 2. VÉRIFICATION DES EXTENSIONS PHP
// ========================================
echo "<div class='section'>";
echo "<h2>🔧 Extensions PHP</h2>";

$required_extensions = [
    'gd' => 'Gestion des images (signatures)',
    'imagick' => 'ImageMagick (traitement avancé)',
    'curl' => 'Requêtes HTTP',
    'json' => 'Traitement JSON',
    'mbstring' => 'Chaînes multibytes',
    'openssl' => 'Cryptographie',
    'fileinfo' => 'Détection de type de fichier',
    'zip' => 'Archives ZIP',
    'xml' => 'Traitement XML',
    'dom' => 'Manipulation DOM'
];

foreach ($required_extensions as $ext => $description) {
    $status = extension_loaded($ext);
    $class = $status ? 'success' : 'error';
    $icon = $status ? '✅' : '❌';
    echo "<p class='$class'>$icon <strong>$ext:</strong> $description - " . ($status ? 'Installé' : 'Manquant') . "</p>";
}

echo "</div>";

// ========================================
// 3. VÉRIFICATION DES PERMISSIONS
// ========================================
echo "<div class='section'>";
echo "<h2>📁 Permissions des dossiers</h2>";

$directories = [
    'storage/app' => 'Stockage des fichiers',
    'storage/app/public' => 'Fichiers publics',
    'storage/app/public/documents' => 'Documents',
    'storage/app/public/documents/signed' => 'Documents signés',
    'storage/app/public/signatures' => 'Signatures',
    'storage/logs' => 'Logs',
    'bootstrap/cache' => 'Cache bootstrap'
];

foreach ($directories as $dir => $description) {
    $path = __DIR__ . '/../' . $dir;
    if (file_exists($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $writable = is_writable($path);
        $readable = is_readable($path);
        $class = ($writable && $readable) ? 'success' : 'error';
        $icon = ($writable && $readable) ? '✅' : '❌';
        echo "<p class='$class'>$icon <strong>$dir:</strong> $description - Permissions: $perms - " . ($writable ? 'Écriture OK' : 'Écriture KO') . " - " . ($readable ? 'Lecture OK' : 'Lecture KO') . "</p>";
    } else {
        echo "<p class='error'>❌ <strong>$dir:</strong> $description - Dossier inexistant</p>";
    }
}

echo "</div>";

// ========================================
// 4. VÉRIFICATION DES FICHIERS DE SIGNATURE
// ========================================
echo "<div class='section'>";
echo "<h2>✍️ Fichiers de signature</h2>";

$signature_paths = [
    'storage/app/public/signatures' => 'Dossier signatures',
    'storage/app/public/documents/signed' => 'Dossier documents signés'
];

foreach ($signature_paths as $path => $description) {
    $full_path = __DIR__ . '/../' . $path;
    if (file_exists($full_path)) {
        $files = scandir($full_path);
        $file_count = count($files) - 2; // Exclure . et ..
        echo "<p class='info'>📁 <strong>$description:</strong> $file_count fichiers</p>";
        
        // Lister quelques fichiers
        $sample_files = array_slice($files, 2, 5);
        if (!empty($sample_files)) {
            echo "<pre>Exemples: " . implode(', ', $sample_files) . "</pre>";
        }
    } else {
        echo "<p class='error'>❌ <strong>$description:</strong> Dossier inexistant</p>";
    }
}

echo "</div>";

// ========================================
// 5. VÉRIFICATION DES CONFIGURATIONS LARAVEL
// ========================================
echo "<div class='section'>";
echo "<h2>⚙️ Configuration Laravel</h2>";

// Vérifier le fichier .env
$env_path = __DIR__ . '/../.env';
if (file_exists($env_path)) {
    echo "<p class='success'>✅ Fichier .env trouvé</p>";
    
    $env_content = file_get_contents($env_path);
    $env_lines = explode("\n", $env_content);
    
    $important_vars = [
        'APP_ENV' => 'Environnement',
        'APP_DEBUG' => 'Mode debug',
        'DB_CONNECTION' => 'Base de données',
        'FILESYSTEM_DISK' => 'Système de fichiers',
        'QUEUE_CONNECTION' => 'Queue'
    ];
    
    foreach ($important_vars as $var => $description) {
        $found = false;
        foreach ($env_lines as $line) {
            if (strpos($line, $var . '=') === 0) {
                $value = trim(substr($line, strlen($var) + 1));
                $class = $value ? 'success' : 'warning';
                echo "<p class='$class'>📝 <strong>$var:</strong> $description = $value</p>";
                $found = true;
                break;
            }
        }
        if (!$found) {
            echo "<p class='warning'>⚠️ <strong>$var:</strong> $description - Non défini</p>";
        }
    }
} else {
    echo "<p class='error'>❌ Fichier .env non trouvé</p>";
}

echo "</div>";

// ========================================
// 6. VÉRIFICATION DES LOGS D'ERREUR
// ========================================
echo "<div class='section'>";
echo "<h2>📋 Logs d'erreur</h2>";

$log_paths = [
    'storage/logs/laravel.log' => 'Log Laravel principal',
    '/var/log/apache2/error.log' => 'Log Apache',
    '/var/log/nginx/error.log' => 'Log Nginx'
];

foreach ($log_paths as $path => $description) {
    if (file_exists($path)) {
        $size = filesize($path);
        $modified = date('Y-m-d H:i:s', filemtime($path));
        echo "<p class='info'>📄 <strong>$description:</strong> Taille: " . number_format($size / 1024, 2) . " KB - Modifié: $modified</p>";
        
        // Lire les dernières lignes d'erreur
        if ($size > 0) {
            $lines = file($path);
            $error_lines = array_filter($lines, function($line) {
                return stripos($line, 'error') !== false || stripos($line, 'exception') !== false;
            });
            
            if (!empty($error_lines)) {
                echo "<pre>Dernières erreurs:\n" . implode('', array_slice($error_lines, -3)) . "</pre>";
            }
        }
    } else {
        echo "<p class='warning'>⚠️ <strong>$description:</strong> Fichier non trouvé</p>";
    }
}

echo "</div>";

// ========================================
// 7. TEST DE CRÉATION DE SIGNATURE
// ========================================
echo "<div class='section'>";
echo "<h2>🧪 Test de création de signature</h2>";

try {
    // Test de création d'image
    if (extension_loaded('gd')) {
        $test_image = imagecreate(100, 50);
        if ($test_image) {
            $bg_color = imagecolorallocate($test_image, 255, 255, 255);
            $text_color = imagecolorallocate($test_image, 0, 0, 0);
            imagestring($test_image, 5, 10, 15, 'TEST', $text_color);
            
            $test_path = __DIR__ . '/test_signature.png';
            if (imagepng($test_image, $test_path)) {
                echo "<p class='success'>✅ Test de création d'image réussi</p>";
                unlink($test_path); // Supprimer le fichier de test
            } else {
                echo "<p class='error'>❌ Échec de sauvegarde de l'image de test</p>";
            }
            imagedestroy($test_image);
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
// 8. RECOMMANDATIONS
// ========================================
echo "<div class='section'>";
echo "<h2>💡 Recommandations</h2>";

echo "<h3>Si vous avez des erreurs de signature :</h3>";
echo "<ol>";
echo "<li><strong>Vérifiez les permissions:</strong> <code>chmod -R 755 storage/</code></li>";
echo "<li><strong>Vérifiez le propriétaire:</strong> <code>chown -R www-data:www-data storage/</code></li>";
echo "<li><strong>Videz le cache:</strong> <code>php artisan cache:clear</code></li>";
echo "<li><strong>Régénérez les liens:</strong> <code>php artisan storage:link</code></li>";
echo "<li><strong>Vérifiez les logs:</strong> <code>tail -f storage/logs/laravel.log</code></li>";
echo "</ol>";

echo "<h3>Commandes de diagnostic :</h3>";
echo "<pre>";
echo "# Vérifier les permissions\n";
echo "ls -la storage/\n\n";
echo "# Vérifier les logs en temps réel\n";
echo "tail -f storage/logs/laravel.log\n\n";
echo "# Vérifier l'espace disque\n";
echo "df -h\n\n";
echo "# Vérifier la mémoire\n";
echo "free -h\n";
echo "</pre>";

echo "</div>";

echo "<hr>";
echo "<p><em>Diagnostic généré le " . date('Y-m-d H:i:s') . "</em></p>";
?>
