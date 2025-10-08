<?php
/**
 * TEST DE SIGNATURE - GEDEPS
 * Script de test pour vérifier le fonctionnement des signatures
 */

// Configuration
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fonction pour créer une signature de test
function createTestSignature($text = 'TEST SIGNATURE', $width = 300, $height = 150) {
    // Créer l'image
    $image = imagecreate($width, $height);
    
    if (!$image) {
        return false;
    }
    
    // Couleurs
    $bg_color = imagecolorallocate($image, 255, 255, 255);
    $text_color = imagecolorallocate($image, 0, 0, 0);
    $border_color = imagecolorallocate($image, 0, 0, 255);
    
    // Dessiner le fond
    imagefill($image, 0, 0, $bg_color);
    
    // Dessiner une bordure
    imagerectangle($image, 5, 5, $width-6, $height-6, $border_color);
    
    // Ajouter le texte
    $font_size = 5;
    $text_width = imagefontwidth($font_size) * strlen($text);
    $text_height = imagefontheight($font_size);
    $x = ($width - $text_width) / 2;
    $y = ($height - $text_height) / 2;
    
    imagestring($image, $font_size, $x, $y, $text, $text_color);
    
    return $image;
}

// Fonction pour sauvegarder une signature
function saveTestSignature($image, $filename) {
    $dir = __DIR__ . '/../storage/app/public/test-signatures';
    
    // Créer le dossier s'il n'existe pas
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $filepath = $dir . '/' . $filename;
    $result = imagepng($image, $filepath);
    
    if ($result) {
        return [
            'success' => true,
            'filepath' => $filepath,
            'url' => '/storage/test-signatures/' . $filename,
            'size' => filesize($filepath)
        ];
    }
    
    return ['success' => false, 'error' => 'Impossible de sauvegarder l\'image'];
}

// Fonction pour tester la création de signature
function testSignatureCreation() {
    $results = [];
    
    // Test 1: Signature simple
    $image1 = createTestSignature('SIGNATURE TEST 1');
    if ($image1) {
        $result1 = saveTestSignature($image1, 'test-signature-1-' . time() . '.png');
        $results['test1'] = $result1;
        imagedestroy($image1);
    } else {
        $results['test1'] = ['success' => false, 'error' => 'Impossible de créer l\'image'];
    }
    
    // Test 2: Signature avec texte personnalisé
    $image2 = createTestSignature('SIGNATURE PERSONNALISÉE', 400, 200);
    if ($image2) {
        $result2 = saveTestSignature($image2, 'test-signature-2-' . time() . '.png');
        $results['test2'] = $result2;
        imagedestroy($image2);
    } else {
        $results['test2'] = ['success' => false, 'error' => 'Impossible de créer l\'image'];
    }
    
    // Test 3: Signature avec bordure complexe
    $image3 = imagecreate(250, 100);
    if ($image3) {
        $bg = imagecolorallocate($image3, 240, 240, 240);
        $text = imagecolorallocate($image3, 0, 0, 0);
        $border = imagecolorallocate($image3, 255, 0, 0);
        
        imagefill($image3, 0, 0, $bg);
        imagerectangle($image3, 5, 5, 245, 95, $border);
        imagestring($image3, 4, 50, 40, 'SIGNATURE COMPLEXE', $text);
        
        $result3 = saveTestSignature($image3, 'test-signature-3-' . time() . '.png');
        $results['test3'] = $result3;
        imagedestroy($image3);
    } else {
        $results['test3'] = ['success' => false, 'error' => 'Impossible de créer l\'image'];
    }
    
    return $results;
}

// Fonction pour vérifier les permissions
function checkPermissions() {
    $permissions = [];
    
    $dirs_to_check = [
        'storage/app',
        'storage/app/public',
        'storage/app/public/documents',
        'storage/app/public/signatures',
        'storage/app/public/test-signatures'
    ];
    
    foreach ($dirs_to_check as $dir) {
        $full_path = __DIR__ . '/../' . $dir;
        if (file_exists($full_path)) {
            $perms = substr(sprintf('%o', fileperms($full_path)), -4);
            $writable = is_writable($full_path);
            $readable = is_readable($full_path);
            
            $permissions[$dir] = [
                'exists' => true,
                'permissions' => $perms,
                'writable' => $writable,
                'readable' => $readable
            ];
        } else {
            $permissions[$dir] = [
                'exists' => false,
                'permissions' => null,
                'writable' => false,
                'readable' => false
            ];
        }
    }
    
    return $permissions;
}

// Fonction pour vérifier les extensions PHP
function checkExtensions() {
    $extensions = [
        'gd' => extension_loaded('gd'),
        'imagick' => extension_loaded('imagick'),
        'curl' => extension_loaded('curl'),
        'json' => extension_loaded('json'),
        'mbstring' => extension_loaded('mbstring'),
        'openssl' => extension_loaded('openssl')
    ];
    
    return $extensions;
}

// Exécution des tests
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'test':
            $results = testSignatureCreation();
            header('Content-Type: application/json');
            echo json_encode($results, JSON_PRETTY_PRINT);
            exit;
            
        case 'permissions':
            $permissions = checkPermissions();
            header('Content-Type: application/json');
            echo json_encode($permissions, JSON_PRETTY_PRINT);
            exit;
            
        case 'extensions':
            $extensions = checkExtensions();
            header('Content-Type: application/json');
            echo json_encode($extensions, JSON_PRETTY_PRINT);
            exit;
    }
}

// Interface HTML
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Signature - GEDEPS</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .info { color: blue; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .btn { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #005a87; }
        .result { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .result.success { background: #d4edda; border: 1px solid #c3e6cb; }
        .result.error { background: #f8d7da; border: 1px solid #f5c6cb; }
        .result.warning { background: #fff3cd; border: 1px solid #ffeaa7; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test de Signature - GEDEPS</h1>
        
        <div class="section">
            <h2>🔧 Vérifications système</h2>
            <button class="btn" onclick="checkExtensions()">Vérifier les extensions PHP</button>
            <button class="btn" onclick="checkPermissions()">Vérifier les permissions</button>
            <div id="system-results"></div>
        </div>
        
        <div class="section">
            <h2>✍️ Test de création de signature</h2>
            <button class="btn" onclick="testSignatures()">Créer des signatures de test</button>
            <div id="signature-results"></div>
        </div>
        
        <div class="section">
            <h2>📋 Résultats des tests</h2>
            <div id="test-results"></div>
        </div>
    </div>

    <script>
        async function checkExtensions() {
            try {
                const response = await fetch('?action=extensions');
                const data = await response.json();
                
                let html = '<h3>Extensions PHP:</h3>';
                for (const [ext, loaded] of Object.entries(data)) {
                    const status = loaded ? 'success' : 'error';
                    const icon = loaded ? '✅' : '❌';
                    html += `<div class="result ${status}">${icon} <strong>${ext}:</strong> ${loaded ? 'Disponible' : 'Manquante'}</div>`;
                }
                
                document.getElementById('system-results').innerHTML = html;
            } catch (error) {
                document.getElementById('system-results').innerHTML = `<div class="result error">❌ Erreur: ${error.message}</div>`;
            }
        }
        
        async function checkPermissions() {
            try {
                const response = await fetch('?action=permissions');
                const data = await response.json();
                
                let html = '<h3>Permissions des dossiers:</h3>';
                for (const [dir, info] of Object.entries(data)) {
                    if (info.exists) {
                        const status = (info.writable && info.readable) ? 'success' : 'warning';
                        const icon = (info.writable && info.readable) ? '✅' : '⚠️';
                        html += `<div class="result ${status}">${icon} <strong>${dir}:</strong> Permissions: ${info.permissions} - ${info.writable ? 'Écriture OK' : 'Écriture KO'} - ${info.readable ? 'Lecture OK' : 'Lecture KO'}</div>`;
                    } else {
                        html += `<div class="result error">❌ <strong>${dir}:</strong> Dossier inexistant</div>`;
                    }
                }
                
                document.getElementById('system-results').innerHTML = html;
            } catch (error) {
                document.getElementById('system-results').innerHTML = `<div class="result error">❌ Erreur: ${error.message}</div>`;
            }
        }
        
        async function testSignatures() {
            try {
                const response = await fetch('?action=test');
                const data = await response.json();
                
                let html = '<h3>Résultats des tests de signature:</h3>';
                for (const [test, result] of Object.entries(data)) {
                    if (result.success) {
                        html += `<div class="result success">✅ <strong>${test}:</strong> Signature créée avec succès</div>`;
                        html += `<div class="result info">📁 Fichier: ${result.filepath}</div>`;
                        html += `<div class="result info">🌐 URL: <a href="${result.url}" target="_blank">${result.url}</a></div>`;
                        html += `<div class="result info">📏 Taille: ${result.size} bytes</div>`;
                    } else {
                        html += `<div class="result error">❌ <strong>${test}:</strong> ${result.error}</div>`;
                    }
                }
                
                document.getElementById('signature-results').innerHTML = html;
            } catch (error) {
                document.getElementById('signature-results').innerHTML = `<div class="result error">❌ Erreur: ${error.message}</div>`;
            }
        }
        
        // Exécuter les vérifications au chargement
        window.onload = function() {
            checkExtensions();
            checkPermissions();
        };
    </script>
</body>
</html>
