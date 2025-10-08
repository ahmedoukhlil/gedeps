<?php
/**
 * Diagnostic des Signatures en Production
 * GEDEPS - Système de Gestion Électronique de Documents
 */

// Bootstrap Laravel
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\User;

echo "<!DOCTYPE html>";
echo "<html lang='fr'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Diagnostic Signatures Production - GEDEPS</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }";
echo ".success { background: #d4edda; border-color: #c3e6cb; color: #155724; }";
echo ".error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }";
echo ".warning { background: #fff3cd; border-color: #ffeaa7; color: #856404; }";
echo ".info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }";
echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; }";
echo "th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "th { background-color: #f2f2f2; }";
echo ".btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }";
echo ".btn:hover { background: #0056b3; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🔍 Diagnostic des Signatures en Production</h1>";
echo "<p><strong>Serveur:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'Local') . "</p>";
echo "<p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>";

// 1. Vérification de la configuration
echo "<div class='section info'>";
echo "<h2>📋 Configuration</h2>";

$appUrl = config('app.url');
$appEnv = config('app.env');
$debug = config('app.debug');

echo "<p><strong>APP_URL:</strong> $appUrl</p>";
echo "<p><strong>APP_ENV:</strong> $appEnv</p>";
echo "<p><strong>APP_DEBUG:</strong> " . ($debug ? 'true' : 'false') . "</p>";

if ($appEnv !== 'production') {
    echo "<div class='warning'>⚠️ L'environnement n'est pas configuré en production</div>";
}

if ($debug) {
    echo "<div class='warning'>⚠️ Le mode debug est activé en production</div>";
}

echo "</div>";

// 2. Vérification du stockage
echo "<div class='section info'>";
echo "<h2>💾 Stockage</h2>";

$storagePath = storage_path('app/public');
$publicPath = public_path('storage');

echo "<p><strong>Chemin de stockage:</strong> $storagePath</p>";
echo "<p><strong>Chemin public:</strong> $publicPath</p>";

if (is_dir($storagePath)) {
    echo "<div class='success'>✅ Répertoire de stockage existe</div>";
} else {
    echo "<div class='error'>❌ Répertoire de stockage manquant</div>";
}

if (is_link($publicPath) || is_dir($publicPath)) {
    echo "<div class='success'>✅ Lien symbolique public/storage existe</div>";
} else {
    echo "<div class='error'>❌ Lien symbolique public/storage manquant</div>";
}

// Vérifier les permissions
$storageWritable = is_writable($storagePath);
$publicWritable = is_writable($publicPath);

echo "<p><strong>Stockage accessible en écriture:</strong> " . ($storageWritable ? 'Oui' : 'Non') . "</p>";
echo "<p><strong>Public accessible en écriture:</strong> " . ($publicWritable ? 'Oui' : 'Non') . "</p>";

echo "</div>";

// 3. Vérification des utilisateurs et signatures
echo "<div class='section info'>";
echo "<h2>👥 Utilisateurs et Signatures</h2>";

try {
    $users = User::all();
    echo "<p><strong>Nombre d'utilisateurs:</strong> " . $users->count() . "</p>";
    
    $usersWithSignatures = $users->filter(function($user) {
        return !empty($user->signature_path);
    });
    
    echo "<p><strong>Utilisateurs avec signatures:</strong> " . $usersWithSignatures->count() . "</p>";
    
    if ($usersWithSignatures->count() > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Email</th><th>Chemin Signature</th><th>Fichier Existe</th></tr>";
        
        foreach ($usersWithSignatures as $user) {
            $fileExists = Storage::disk('public')->exists($user->signature_path);
            $fileStatus = $fileExists ? '✅ Oui' : '❌ Non';
            
            echo "<tr>";
            echo "<td>{$user->id}</td>";
            echo "<td>{$user->name}</td>";
            echo "<td>{$user->email}</td>";
            echo "<td>{$user->signature_path}</td>";
            echo "<td>$fileStatus</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erreur lors de la récupération des utilisateurs: " . $e->getMessage() . "</div>";
}

echo "</div>";

// 4. Test des routes
echo "<div class='section info'>";
echo "<h2>🛣️ Test des Routes</h2>";

$baseUrl = $appUrl;
$testRoutes = [
    '/signatures/user-signature',
    '/signatures/user-paraphe',
    '/test-signature-simple',
    '/public/user-signature'
];

foreach ($testRoutes as $route) {
    $url = $baseUrl . $route;
    echo "<p><strong>Test de la route:</strong> <a href='$url' target='_blank'>$route</a></p>";
}

echo "</div>";

// 5. Vérification des fichiers CSS/JS
echo "<div class='section info'>";
echo "<h2>📁 Fichiers Assets</h2>";

$assetFiles = [
    'public/css/app.css',
    'public/js/app.js',
    'public/js/pdf-overlay-unified-module.js',
    'public/build/manifest.json'
];

foreach ($assetFiles as $file) {
    $exists = file_exists($file);
    $status = $exists ? '✅ Existe' : '❌ Manquant';
    $size = $exists ? ' (' . filesize($file) . ' bytes)' : '';
    
    echo "<p><strong>$file:</strong> $status$size</p>";
}

echo "</div>";

// 6. Test de création d'image
echo "<div class='section info'>";
echo "<h2>🖼️ Test de Création d'Image</h2>";

if (extension_loaded('gd')) {
    echo "<div class='success'>✅ Extension GD disponible</div>";
    
    try {
        $testImage = imagecreate(200, 100);
        $bg = imagecolorallocate($testImage, 255, 255, 255);
        $text = imagecolorallocate($testImage, 0, 0, 0);
        
        imagestring($testImage, 5, 50, 40, 'TEST', $text);
        
        $testPath = storage_path('app/public/test-signature.png');
        if (imagepng($testImage, $testPath)) {
            echo "<div class='success'>✅ Test de création d'image réussi</div>";
            echo "<p><strong>Image de test créée:</strong> $testPath</p>";
            
            // Nettoyer le fichier de test
            unlink($testPath);
        } else {
            echo "<div class='error'>❌ Échec de création d'image</div>";
        }
        
        imagedestroy($testImage);
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erreur lors du test d'image: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='error'>❌ Extension GD non disponible</div>";
}

echo "</div>";

// 7. Commandes de correction
echo "<div class='section warning'>";
echo "<h2>🔧 Commandes de Correction</h2>";

echo "<h3>Si les signatures ne fonctionnent pas:</h3>";
echo "<ol>";
echo "<li><strong>Vérifier APP_URL:</strong> <code>php artisan config:clear && php artisan config:cache</code></li>";
echo "<li><strong>Recréer le lien symbolique:</strong> <code>php artisan storage:link</code></li>";
echo "<li><strong>Vérifier les permissions:</strong> <code>chmod -R 775 storage/</code></li>";
echo "<li><strong>Vider le cache:</strong> <code>php artisan cache:clear</code></li>";
echo "<li><strong>Redémarrer le serveur web:</strong> <code>systemctl restart apache2</code></li>";
echo "</ol>";

echo "<h3>URLs de test:</h3>";
echo "<ul>";
echo "<li><a href='{$baseUrl}/signatures/user-signature' target='_blank'>Test signature utilisateur</a></li>";
echo "<li><a href='{$baseUrl}/test-signature-simple' target='_blank'>Test route simple</a></li>";
echo "<li><a href='{$baseUrl}/public/user-signature' target='_blank'>Test route publique</a></li>";
echo "</ul>";

echo "</div>";

echo "<div class='section success'>";
echo "<h2>✅ Diagnostic Terminé</h2>";
echo "<p>Ce diagnostic a été généré le " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Si les problèmes persistent, vérifiez les logs Laravel dans <code>storage/logs/laravel.log</code></p>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
