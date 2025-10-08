<?php
/**
 * RESTAURATION DES DOCUMENTS SÉQUENTIELS
 * Vérifier et restaurer les documents séquentiels manquants
 */

// Configuration d'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Restauration des documents séquentiels</h1>";
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
    // 1. VÉRIFICATION DE L'ÉTAT ACTUEL
    // ========================================
    echo "<div class='section'>";
    echo "<h2>🔍 Vérification de l'état actuel</h2>";
    
    // Vérifier tous les documents
    $stmt = $pdo->query("
        SELECT id, document_name, status, sequential_signatures, uploaded_by, signer_id, created_at, updated_at
        FROM documents 
        ORDER BY id DESC
        LIMIT 20
    ");
    
    $allDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='debug-step'>";
    echo "<p><strong>Derniers documents (20):</strong></p>";
    echo "</div>";
    
    if (count($allDocs) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Statut</th><th>Séquentiel</th><th>Uploadé par</th><th>Signataire</th><th>Créé</th></tr>";
        
        foreach ($allDocs as $doc) {
            $statusClass = $doc['status'] === 'signed' ? 'success' : 'warning';
            $sequentialClass = $doc['sequential_signatures'] ? 'success' : 'info';
            
            echo "<tr>";
            echo "<td>{$doc['id']}</td>";
            echo "<td>{$doc['document_name']}</td>";
            echo "<td class='$statusClass'>{$doc['status']}</td>";
            echo "<td class='$sequentialClass'>" . ($doc['sequential_signatures'] ? 'OUI' : 'NON') . "</td>";
            echo "<td>{$doc['uploaded_by']}</td>";
            echo "<td>{$doc['signer_id']}</td>";
            echo "<td>{$doc['created_at']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "</div>";
    
    // ========================================
    // 2. VÉRIFICATION DES SIGNATURES SÉQUENTIELLES
    // ========================================
    echo "<div class='section'>";
    echo "<h2>✍️ Vérification des signatures séquentielles</h2>";
    
    $stmt = $pdo->query("
        SELECT ss.id, ss.document_id, ss.user_id, ss.signature_order, ss.status, ss.created_at,
               d.document_name, d.sequential_signatures, u.name as user_name
        FROM sequential_signatures ss
        LEFT JOIN documents d ON ss.document_id = d.id
        LEFT JOIN users u ON ss.user_id = u.id
        ORDER BY ss.id DESC
    ");
    
    $signatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='debug-step'>";
    echo "<p><strong>Signatures séquentielles trouvées:</strong> " . count($signatures) . "</p>";
    echo "</div>";
    
    if (count($signatures) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Document</th><th>Signataire</th><th>Ordre</th><th>Statut</th><th>Document Séquentiel</th><th>Créé</th></tr>";
        
        foreach ($signatures as $sig) {
            $statusClass = $sig['status'] === 'signed' ? 'success' : 'warning';
            $docClass = $sig['sequential_signatures'] ? 'success' : 'error';
            
            echo "<tr>";
            echo "<td>{$sig['id']}</td>";
            echo "<td>{$sig['document_name']} (ID: {$sig['document_id']})</td>";
            echo "<td>{$sig['user_name']} (ID: {$sig['user_id']})</td>";
            echo "<td>{$sig['signature_order']}</td>";
            echo "<td class='$statusClass'>{$sig['status']}</td>";
            echo "<td class='$docClass'>" . ($sig['sequential_signatures'] ? 'OUI' : 'NON') . "</td>";
            echo "<td>{$sig['created_at']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Aucune signature séquentielle trouvée.</p>";
    }
    
    echo "</div>";
    
    // ========================================
    // 3. IDENTIFICATION DES DOCUMENTS À RESTAURER
    // ========================================
    echo "<div class='section'>";
    echo "<h2>🔧 Identification des documents à restaurer</h2>";
    
    // Chercher les documents qui ont des signatures séquentielles mais ne sont plus marqués comme séquentiels
    $stmt = $pdo->query("
        SELECT DISTINCT d.id, d.document_name, d.status, d.sequential_signatures, d.uploaded_by, d.signer_id
        FROM documents d
        INNER JOIN sequential_signatures ss ON d.id = ss.document_id
        WHERE d.sequential_signatures = 0
        ORDER BY d.id
    ");
    
    $docsToRestore = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='debug-step'>";
    echo "<p><strong>Documents à restaurer (avec signatures séquentielles mais marqués comme non-séquentiels):</strong> " . count($docsToRestore) . "</p>";
    echo "</div>";
    
    if (count($docsToRestore) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Statut</th><th>Séquentiel</th><th>Uploadé par</th><th>Signataire</th><th>Action</th></tr>";
        
        foreach ($docsToRestore as $doc) {
            echo "<tr>";
            echo "<td>{$doc['id']}</td>";
            echo "<td>{$doc['document_name']}</td>";
            echo "<td>{$doc['status']}</td>";
            echo "<td class='error'>NON (à corriger)</td>";
            echo "<td>{$doc['uploaded_by']}</td>";
            echo "<td>{$doc['signer_id']}</td>";
            echo "<td><button onclick='restoreDocument({$doc['id']})' class='bg-blue-500 text-white px-3 py-1 rounded'>Restaurer</button></td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='success'>✅ Aucun document à restaurer trouvé.</p>";
    }
    
    echo "</div>";
    
    // ========================================
    // 4. RESTAURATION AUTOMATIQUE
    // ========================================
    if (count($docsToRestore) > 0) {
        echo "<div class='section'>";
        echo "<h2>🔧 Restauration automatique</h2>";
        
        $restoredCount = 0;
        
        foreach ($docsToRestore as $doc) {
            try {
                // Restaurer le document comme séquentiel
                $stmt = $pdo->prepare("UPDATE documents SET sequential_signatures = 1 WHERE id = ?");
                $stmt->execute([$doc['id']]);
                
                echo "<div class='debug-step'>";
                echo "<p class='success'>✅ Document restauré: {$doc['document_name']} (ID: {$doc['id']})</p>";
                echo "</div>";
                
                $restoredCount++;
            } catch (Exception $e) {
                echo "<div class='debug-step'>";
                echo "<p class='error'>❌ Erreur lors de la restauration du document {$doc['id']}: " . $e->getMessage() . "</p>";
                echo "</div>";
            }
        }
        
        echo "<div class='debug-step'>";
        echo "<p class='success'>✅ $restoredCount document(s) restauré(s) avec succès</p>";
        echo "</div>";
        
        echo "</div>";
    }
    
    // ========================================
    // 5. VÉRIFICATION APRÈS RESTAURATION
    // ========================================
    echo "<div class='section'>";
    echo "<h2>✅ Vérification après restauration</h2>";
    
    $stmt = $pdo->query("
        SELECT id, document_name, status, sequential_signatures, uploaded_by, signer_id
        FROM documents 
        WHERE sequential_signatures = 1
        ORDER BY id
    ");
    
    $restoredDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='debug-step'>";
    echo "<p><strong>Documents séquentiels après restauration:</strong> " . count($restoredDocs) . "</p>";
    echo "</div>";
    
    if (count($restoredDocs) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Statut</th><th>Séquentiel</th><th>Uploadé par</th><th>Signataire</th></tr>";
        
        foreach ($restoredDocs as $doc) {
            $statusClass = $doc['status'] === 'signed' ? 'success' : 'warning';
            
            echo "<tr>";
            echo "<td>{$doc['id']}</td>";
            echo "<td>{$doc['document_name']}</td>";
            echo "<td class='$statusClass'>{$doc['status']}</td>";
            echo "<td class='success'>OUI</td>";
            echo "<td>{$doc['uploaded_by']}</td>";
            echo "<td>{$doc['signer_id']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "</div>";
    
    // ========================================
    // 6. RÉSULTATS FINAUX
    // ========================================
    echo "<div class='section'>";
    echo "<h2>🎯 Résultats finaux</h2>";
    
    echo "<h3>✅ Actions effectuées :</h3>";
    echo "<ul>";
    echo "<li><strong>Documents vérifiés:</strong> " . count($allDocs) . "</li>";
    echo "<li><strong>Signatures séquentielles trouvées:</strong> " . count($signatures) . "</li>";
    echo "<li><strong>Documents à restaurer:</strong> " . count($docsToRestore) . "</li>";
    echo "<li><strong>Documents restaurés:</strong> " . (isset($restoredCount) ? $restoredCount : 0) . "</li>";
    echo "</ul>";
    
    echo "<h3>🔗 URLs de test :</h3>";
    echo "<ul>";
    echo "<li><a href='/documents/history' target='_blank'>Page d'historique des documents</a></li>";
    echo "<li><a href='/test-history-display.php' target='_blank'>Test de l'affichage de l'historique</a></li>";
    echo "<li><a href='/debug-sequential-history.php' target='_blank'>Diagnostic des documents séquentiels</a></li>";
    echo "</ul>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section'>";
    echo "<h2 class='error'>❌ Erreur lors de la restauration</h2>";
    echo "<p class='error'>Erreur: " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><em>Restauration effectuée le " . date('Y-m-d H:i:s') . "</em></p>";
?>
