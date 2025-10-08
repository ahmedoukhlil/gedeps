<?php
/**
 * DIAGNOSTIC DES DOCUMENTS SIGNÉS SÉQUENTIELLEMENT
 * Vérifier comment les documents signés sont sauvegardés et transmis
 */

// Configuration d'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnostic des documents signés séquentiellement</h1>";
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

try {
    // Connexion directe à la base de données
    $host = 'localhost';
    $dbname = 'gedeps';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='section'>";
    echo "<h2>✅ Connexion à la base de données réussie</h2>";
    echo "</div>";
    
    // ========================================
    // 1. VÉRIFICATION DES DOCUMENTS SIGNÉS
    // ========================================
    echo "<div class='section'>";
    echo "<h2>📄 Documents avec signatures séquentielles</h2>";
    
    $stmt = $pdo->query("
        SELECT d.id, d.document_name, d.status, d.current_signature_index, d.filename_original, d.filename_signed,
               d.created_at, d.updated_at
        FROM documents d
        WHERE d.sequential_signatures = 1
        ORDER BY d.id
    ");
    
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='debug-step'>";
    echo "<p><strong>Total documents séquentiels:</strong> " . count($documents) . "</p>";
    echo "</div>";
    
    if (count($documents) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Statut</th><th>Index</th><th>Fichier original</th><th>Fichier signé</th><th>Créé</th><th>Modifié</th></tr>";
        
        foreach ($documents as $doc) {
            $statusClass = $doc['status'] === 'in_progress' ? 'success' : 'warning';
            $signedFile = $doc['filename_signed'] ? '✅ Oui' : '❌ Non';
            
            echo "<tr>";
            echo "<td>{$doc['id']}</td>";
            echo "<td>{$doc['document_name']}</td>";
            echo "<td class='$statusClass'>{$doc['status']}</td>";
            echo "<td>{$doc['current_signature_index']}</td>";
            echo "<td>{$doc['filename_original']}</td>";
            echo "<td>$signedFile</td>";
            echo "<td>{$doc['created_at']}</td>";
            echo "<td>{$doc['updated_at']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "</div>";
    
    // ========================================
    // 2. VÉRIFICATION DES FICHIERS DE SIGNATURES
    // ========================================
    echo "<div class='section'>";
    echo "<h2>📁 Fichiers de signatures dans le système</h2>";
    
    // Vérifier les fichiers dans le dossier de stockage
    $storagePath = __DIR__ . '/../storage/app/public/signatures/';
    $publicPath = __DIR__ . '/../public/storage/signatures/';
    
    echo "<div class='debug-step'>";
    echo "<p><strong>Dossier de stockage:</strong> $storagePath</p>";
    echo "<p><strong>Dossier public:</strong> $publicPath</p>";
    echo "</div>";
    
    if (is_dir($storagePath)) {
        $files = scandir($storagePath);
        $signatureFiles = array_filter($files, function($file) {
            return !in_array($file, ['.', '..']) && pathinfo($file, PATHINFO_EXTENSION) === 'png';
        });
        
        echo "<div class='debug-step'>";
        echo "<p><strong>Fichiers de signatures trouvés:</strong> " . count($signatureFiles) . "</p>";
        echo "</div>";
        
        if (count($signatureFiles) > 0) {
            echo "<table>";
            echo "<tr><th>Fichier</th><th>Taille</th><th>Modifié</th></tr>";
            
            foreach ($signatureFiles as $file) {
                $filePath = $storagePath . $file;
                $size = filesize($filePath);
                $modified = date('Y-m-d H:i:s', filemtime($filePath));
                
                echo "<tr>";
                echo "<td>$file</td>";
                echo "<td>" . number_format($size / 1024, 2) . " KB</td>";
                echo "<td>$modified</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
    } else {
        echo "<p class='warning'>⚠️ Dossier de stockage des signatures non trouvé.</p>";
    }
    
    echo "</div>";
    
    // ========================================
    // 3. VÉRIFICATION DU FLUX SÉQUENTIEL
    // ========================================
    echo "<div class='section'>";
    echo "<h2>🔄 Flux séquentiel des signatures</h2>";
    
    $stmt = $pdo->query("
        SELECT d.id as document_id, d.document_name, d.current_signature_index,
               ss.user_id, u.name as user_name, ss.signature_order, ss.status,
               CASE 
                   WHEN ss.signature_order = d.current_signature_index + 1 THEN 'OUI'
                   ELSE 'NON'
               END as is_current_turn,
               CASE 
                   WHEN ss.status = 'signed' THEN '✅ Signé'
                   WHEN ss.status = 'pending' AND ss.signature_order = d.current_signature_index + 1 THEN '🎯 À signer'
                   ELSE '⏳ En attente'
               END as action_status
        FROM documents d
        JOIN sequential_signatures ss ON d.id = ss.document_id
        JOIN users u ON ss.user_id = u.id
        WHERE d.sequential_signatures = 1
        ORDER BY d.id, ss.signature_order
    ");
    
    $flow = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($flow) > 0) {
        echo "<table>";
        echo "<tr><th>Document</th><th>Utilisateur</th><th>Ordre</th><th>Statut</th><th>Index</th><th>Tour?</th><th>Action</th></tr>";
        
        foreach ($flow as $item) {
            $statusClass = $item['status'] === 'signed' ? 'success' : 'warning';
            $turnClass = $item['is_current_turn'] === 'OUI' ? 'success' : 'info';
            $actionClass = strpos($item['action_status'], 'À signer') !== false ? 'success' : 'info';
            
            echo "<tr>";
            echo "<td>{$item['document_name']} (ID: {$item['document_id']})</td>";
            echo "<td>{$item['user_name']} (ID: {$item['user_id']})</td>";
            echo "<td>{$item['signature_order']}</td>";
            echo "<td class='$statusClass'>{$item['status']}</td>";
            echo "<td>{$item['current_signature_index']}</td>";
            echo "<td class='$turnClass'>{$item['is_current_turn']}</td>";
            echo "<td class='$actionClass'>{$item['action_status']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "</div>";
    
    // ========================================
    // 4. VÉRIFICATION DES FICHIERS PDF SIGNÉS
    // ========================================
    echo "<div class='section'>";
    echo "<h2>📄 Fichiers PDF signés</h2>";
    
    // Vérifier les fichiers PDF signés
    $pdfPath = __DIR__ . '/../storage/app/public/signed/';
    
    if (is_dir($pdfPath)) {
        $pdfFiles = scandir($pdfPath);
        $signedPdfs = array_filter($pdfFiles, function($file) {
            return !in_array($file, ['.', '..']) && pathinfo($file, PATHINFO_EXTENSION) === 'pdf';
        });
        
        echo "<div class='debug-step'>";
        echo "<p><strong>Fichiers PDF signés trouvés:</strong> " . count($signedPdfs) . "</p>";
        echo "</div>";
        
        if (count($signedPdfs) > 0) {
            echo "<table>";
            echo "<tr><th>Fichier PDF</th><th>Taille</th><th>Modifié</th><th>URL</th></tr>";
            
            foreach ($signedPdfs as $file) {
                $filePath = $pdfPath . $file;
                $size = filesize($filePath);
                $modified = date('Y-m-d H:i:s', filemtime($filePath));
                $url = "http://localhost:8000/storage/signed/$file";
                
                echo "<tr>";
                echo "<td>$file</td>";
                echo "<td>" . number_format($size / 1024, 2) . " KB</td>";
                echo "<td>$modified</td>";
                echo "<td><a href='$url' target='_blank'>Voir le PDF</a></td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
    } else {
        echo "<p class='warning'>⚠️ Dossier des PDF signés non trouvé.</p>";
    }
    
    echo "</div>";
    
    // ========================================
    // 5. RECOMMANDATIONS
    // ========================================
    echo "<div class='section'>";
    echo "<h2>💡 Recommandations pour le flux séquentiel</h2>";
    
    echo "<h3>🔧 Points à vérifier :</h3>";
    echo "<ol>";
    echo "<li><strong>Le document original</strong> doit être sauvegardé dans <code>filename_original</code></li>";
    echo "<li><strong>Le document signé</strong> doit être sauvegardé dans <code>filename_signed</code></li>";
    echo "<li><strong>L'index <code>current_signature_index</code></strong> doit être incrémenté après chaque signature</li>";
    echo "<li><strong>Le prochain signataire</strong> doit recevoir le document avec les signatures précédentes</li>";
    echo "</ol>";
    
    echo "<h3>🔍 URLs de test :</h3>";
    echo "<ul>";
    echo "<li><a href='/signatures-simple' target='_blank'>Liste des signatures séquentielles</a></li>";
    echo "<li><a href='/signatures-simple/debug' target='_blank'>Debug des signatures séquentielles</a></li>";
    echo "<li><a href='/test-sequential-flow.php' target='_blank'>Test complet du flux</a></li>";
    echo "</ul>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section'>";
    echo "<h2 class='error'>❌ Erreur lors du diagnostic</h2>";
    echo "<p class='error'>Erreur: " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><em>Diagnostic généré le " . date('Y-m-d H:i:s') . "</em></p>";
?>
