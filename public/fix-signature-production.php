<?php
/**
 * Correction Automatique des Signatures en Production
 * GEDEPS - Système de Gestion Électronique de Documents
 */

// Bootstrap Laravel
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

echo "<!DOCTYPE html>";
echo "<html lang='fr'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Correction Signatures Production - GEDEPS</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }";
echo ".success { background: #d4edda; border-color: #c3e6cb; color: #155724; }";
echo ".error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }";
echo ".warning { background: #fff3cd; border-color: #ffeaa7; color: #856404; }";
echo ".info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }";
echo ".btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }";
echo ".btn:hover { background: #0056b3; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🔧 Correction Automatique des Signatures en Production</h1>";
echo "<p><strong>Serveur:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'Local') . "</p>";
echo "<p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>";

$corrections = [];

// 1. Vérifier et corriger APP_URL
echo "<div class='section info'>";
echo "<h2>🌐 Correction APP_URL</h2>";

$currentAppUrl = config('app.url');
$serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
$serverPort = $_SERVER['SERVER_PORT'] ?? '80';
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

$expectedUrl = $protocol . '://' . $serverName;
if ($serverPort !== '80' && $serverPort !== '443') {
    $expectedUrl .= ':' . $serverPort;
}

echo "<p><strong>URL actuelle:</strong> $currentAppUrl</p>";
echo "<p><strong>URL attendue:</strong> $expectedUrl</p>";

if ($currentAppUrl !== $expectedUrl) {
    echo "<div class='warning'>⚠️ APP_URL incorrect, correction nécessaire</div>";
    $corrections[] = "APP_URL doit être défini sur: $expectedUrl";
} else {
    echo "<div class='success'>✅ APP_URL correct</div>";
}

echo "</div>";

// 2. Vérifier et recréer le lien symbolique
echo "<div class='section info'>";
echo "<h2>🔗 Correction du Lien Symbolique</h2>";

$publicStoragePath = public_path('storage');
$storagePath = storage_path('app/public');

echo "<p><strong>Chemin public/storage:</strong> $publicStoragePath</p>";
echo "<p><strong>Chemin storage/app/public:</strong> $storagePath</p>";

if (!is_link($publicStoragePath) && !is_dir($publicStoragePath)) {
    echo "<div class='warning'>⚠️ Lien symbolique manquant, tentative de création...</div>";
    
    try {
        // Supprimer l'ancien lien s'il existe
        if (file_exists($publicStoragePath)) {
            unlink($publicStoragePath);
        }
        
        // Créer le nouveau lien symbolique
        if (symlink($storagePath, $publicStoragePath)) {
            echo "<div class='success'>✅ Lien symbolique créé avec succès</div>";
            $corrections[] = "Lien symbolique public/storage recréé";
        } else {
            echo "<div class='error'>❌ Échec de création du lien symbolique</div>";
            $corrections[] = "Échec de création du lien symbolique - vérifiez les permissions";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erreur lors de la création du lien: " . $e->getMessage() . "</div>";
        $corrections[] = "Erreur lors de la création du lien symbolique: " . $e->getMessage();
    }
} else {
    echo "<div class='success'>✅ Lien symbolique existe</div>";
}

echo "</div>";

// 3. Vérifier et corriger les permissions
echo "<div class='section info'>";
echo "<h2>🔐 Correction des Permissions</h2>";

$directories = [
    storage_path(),
    storage_path('app'),
    storage_path('app/public'),
    storage_path('logs'),
    storage_path('framework'),
    storage_path('framework/cache'),
    storage_path('framework/sessions'),
    storage_path('framework/views'),
    bootstrap_path('cache')
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir);
        $status = $writable ? '✅ Accessible' : '❌ Non accessible';
        echo "<p><strong>$dir:</strong> $status</p>";
        
        if (!$writable) {
            $corrections[] = "Permissions insuffisantes pour: $dir";
        }
    } else {
        echo "<p><strong>$dir:</strong> ❌ Répertoire manquant</p>";
        $corrections[] = "Répertoire manquant: $dir";
    }
}

echo "</div>";

// 4. Vider les caches
echo "<div class='section info'>";
echo "<h2>🗑️ Nettoyage des Caches</h2>";

$cacheCommands = [
    'config:clear' => 'Cache de configuration',
    'route:clear' => 'Cache des routes',
    'view:clear' => 'Cache des vues',
    'cache:clear' => 'Cache général'
];

foreach ($cacheCommands as $command => $description) {
    try {
        Artisan::call($command);
        echo "<div class='success'>✅ $description vidé</div>";
        $corrections[] = "Cache vidé: $description";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erreur lors du vidage de $description: " . $e->getMessage() . "</div>";
        $corrections[] = "Erreur lors du vidage de $description: " . $e->getMessage();
    }
}

echo "</div>";

// 5. Optimiser la configuration
echo "<div class='section info'>";
echo "<h2>⚙️ Optimisation de la Configuration</h2>";

$optimizeCommands = [
    'config:cache' => 'Cache de configuration',
    'route:cache' => 'Cache des routes',
    'view:cache' => 'Cache des vues'
];

foreach ($optimizeCommands as $command => $description) {
    try {
        Artisan::call($command);
        echo "<div class='success'>✅ $description optimisé</div>";
        $corrections[] = "Configuration optimisée: $description";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erreur lors de l'optimisation de $description: " . $e->getMessage() . "</div>";
        $corrections[] = "Erreur lors de l'optimisation de $description: " . $e->getMessage();
    }
}

echo "</div>";

// 6. Test des routes après correction
echo "<div class='section info'>";
echo "<h2>🧪 Test des Routes après Correction</h2>";

$baseUrl = config('app.url');
$testRoutes = [
    '/signatures/user-signature' => 'Signature utilisateur',
    '/signatures/user-paraphe' => 'Paraphe utilisateur',
    '/test-signature-simple' => 'Test route simple'
];

foreach ($testRoutes as $route => $description) {
    $url = $baseUrl . $route;
    echo "<p><strong>$description:</strong> <a href='$url' target='_blank'>$route</a></p>";
}

echo "</div>";

// 7. Résumé des corrections
echo "<div class='section " . (empty($corrections) ? 'success' : 'warning') . "'>";
echo "<h2>📋 Résumé des Corrections</h2>";

if (empty($corrections)) {
    echo "<div class='success'>✅ Aucune correction nécessaire</div>";
} else {
    echo "<h3>Corrections effectuées:</h3>";
    echo "<ul>";
    foreach ($corrections as $correction) {
        echo "<li>$correction</li>";
    }
    echo "</ul>";
}

echo "</div>";

// 8. Commandes manuelles si nécessaire
echo "<div class='section warning'>";
echo "<h2>🛠️ Commandes Manuelles si Nécessaire</h2>";

echo "<h3>Si les problèmes persistent:</h3>";
echo "<ol>";
echo "<li><strong>Vérifier APP_URL dans .env:</strong> <code>APP_URL=$expectedUrl</code></li>";
echo "<li><strong>Recréer le lien symbolique:</strong> <code>php artisan storage:link</code></li>";
echo "<li><strong>Corriger les permissions:</strong> <code>chmod -R 775 storage/ bootstrap/cache/</code></li>";
echo "<li><strong>Redémarrer le serveur web:</strong> <code>systemctl restart apache2</code> ou <code>systemctl restart nginx</code></li>";
echo "<li><strong>Vérifier les logs:</strong> <code>tail -f storage/logs/laravel.log</code></li>";
echo "</ol>";

echo "<h3>URLs de test:</h3>";
echo "<ul>";
echo "<li><a href='{$baseUrl}/signatures/user-signature' target='_blank'>Test signature utilisateur</a></li>";
echo "<li><a href='{$baseUrl}/diagnostic-signature-prod.php' target='_blank'>Diagnostic complet</a></li>";
echo "</ul>";

echo "</div>";

echo "<div class='section success'>";
echo "<h2>✅ Correction Terminée</h2>";
echo "<p>Ce script de correction a été exécuté le " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Si les problèmes persistent, consultez les logs Laravel dans <code>storage/logs/laravel.log</code></p>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
