<?php
/**
 * TEST DU FLUX PDF SÉQUENTIEL
 * Vérifier que le bon PDF est affiché à chaque signataire
 */

// Configuration d'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Test du flux PDF séquentiel</h1>";
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
    // 1. ANALYSE DES DOCUMENTS SÉQUENTIELS
    // ========================================
    echo "<div class='section'>";
    echo "<h2>📄 Analyse des documents séquentiels</h2>";
    
    $stmt = $pdo->query("
        SELECT d.id, d.document_name, d.status, d.current_signature_index, d.path_original,
               COUNT(ss.id) as total_signers,
               COUNT(CASE WHEN ss.status = 'signed' THEN 1 END) as signed_count,
               COUNT(CASE WHEN ss.status = 'pending' THEN 1 END) as pending_count
        FROM documents d
        LEFT JOIN sequential_signatures ss ON d.id = ss.document_id
        WHERE d.sequential_signatures = 1
        GROUP BY d.id, d.document_name, d.status, d.current_signature_index, d.path_original
        ORDER BY d.id
    ");
    
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($documents) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Statut</th><th>Index</th><th>Signataires</th><th>Signées</th><th>En attente</th><th>PDF Original</th></tr>";
        
        foreach ($documents as $doc) {
            $statusClass = $doc['status'] === 'in_progress' ? 'success' : 'warning';
            $progressClass = $doc['signed_count'] > 0 ? 'success' : 'info';
            
            echo "<tr>";
            echo "<td>{$doc['id']}</td>";
            echo "<td>{$doc['document_name']}</td>";
            echo "<td class='$statusClass'>{$doc['status']}</td>";
            echo "<td>{$doc['current_signature_index']}</td>";
            echo "<td>{$doc['total_signers']}</td>";
            echo "<td class='$progressClass'>{$doc['signed_count']}</td>";
            echo "<td>{$doc['pending_count']}</td>";
            echo "<td>" . basename($doc['path_original']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Aucun document séquentiel trouvé.</p>";
    }
    
    echo "</div>";
    
    // ========================================
    // 2. VÉRIFICATION DES PDF SIGNÉS
    // ========================================
    echo "<div class='section'>";
    echo "<h2>📄 Vérification des PDF signés</h2>";
    
    $stmt = $pdo->query("
        SELECT ds.document_id, d.document_name, ds.signed_by, u.name as signer_name,
               ds.path_signed_pdf, ds.signed_at, ds.created_at
        FROM document_signatures ds
        JOIN documents d ON ds.document_id = d.id
        JOIN users u ON ds.signed_by = u.id
        WHERE d.sequential_signatures = 1
        ORDER BY ds.document_id, ds.signed_at
    ");
    
    $signatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($signatures) > 0) {
        echo "<table>";
        echo "<tr><th>Document</th><th>Signataire</th><th>PDF Signé</th><th>Signé le</th><th>URL</th></tr>";
        
        foreach ($signatures as $sig) {
            $pdfFile = basename($sig['path_signed_pdf']);
            $pdfUrl = "http://localhost:8000/storage/" . str_replace('documents/', '', $sig['path_signed_pdf']);
            
            echo "<tr>";
            echo "<td>{$sig['document_name']} (ID: {$sig['document_id']})</td>";
            echo "<td>{$sig['signer_name']} (ID: {$sig['signed_by']})</td>";
            echo "<td>$pdfFile</td>";
            echo "<td>{$sig['signed_at']}</td>";
            echo "<td><a href='$pdfUrl' target='_blank'>Voir le PDF</a></td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Aucun PDF signé trouvé.</p>";
    }
    
    echo "</div>";
    
    // ========================================
    // 3. SIMULATION DU FLUX SÉQUENTIEL
    // ========================================
    echo "<div class='section'>";
    echo "<h2>🔄 Simulation du flux séquentiel</h2>";
    
    foreach ($documents as $doc) {
        echo "<div class='debug-step'>";
        echo "<h3>📄 Document: {$doc['document_name']} (ID: {$doc['id']})</h3>";
        
        // Récupérer les signataires pour ce document
        $stmt = $pdo->prepare("
            SELECT ss.user_id, u.name as user_name, ss.signature_order, ss.status,
                   CASE 
                       WHEN ss.signature_order = ? + 1 THEN 'OUI'
                       ELSE 'NON'
                   END as is_current_turn
            FROM sequential_signatures ss
            JOIN users u ON ss.user_id = u.id
            WHERE ss.document_id = ?
            ORDER BY ss.signature_order
        ");
        
        $stmt->execute([$doc['current_signature_index'], $doc['id']]);
        $signers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr><th>Ordre</th><th>Signataire</th><th>Statut</th><th>Tour?</th><th>PDF à afficher</th></tr>";
        
        foreach ($signers as $signer) {
            $statusClass = $signer['status'] === 'signed' ? 'success' : 'warning';
            $turnClass = $signer['is_current_turn'] === 'OUI' ? 'success' : 'info';
            
            // Déterminer quel PDF afficher
            $pdfToShow = "Document original";
            if ($signer['status'] === 'signed' && $signer['signature_order'] > 1) {
                $pdfToShow = "Document avec signatures précédentes";
            } elseif ($signer['is_current_turn'] === 'OUI' && $doc['current_signature_index'] > 0) {
                $pdfToShow = "Document avec signatures précédentes";
            }
            
            echo "<tr>";
            echo "<td>{$signer['signature_order']}</td>";
            echo "<td>{$signer['user_name']} (ID: {$signer['user_id']})</td>";
            echo "<td class='$statusClass'>{$signer['status']}</td>";
            echo "<td class='$turnClass'>{$signer['is_current_turn']}</td>";
            echo "<td>$pdfToShow</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</div>";
    }
    
    echo "</div>";
    
    // ========================================
    // 4. RECOMMANDATIONS
    // ========================================
    echo "<div class='section'>";
    echo "<h2>💡 Recommandations</h2>";
    
    echo "<h3>✅ Corrections apportées :</h3>";
    echo "<ul>";
    echo "<li><strong>Méthode <code>getDocumentPdfUrl()</code></strong> ajoutée au contrôleur</li>";
    echo "<li><strong>Logique de détermination</strong> du bon PDF à afficher</li>";
    echo "<li><strong>Logs de débogage</strong> pour tracer le flux</li>";
    echo "</ul>";
    
    echo "<h3>🔍 Points à vérifier :</h3>";
    echo "<ol>";
    echo "<li><strong>Premier signataire</strong> : doit voir le document original</li>";
    echo "<li><strong>Signataires suivants</strong> : doivent voir le document avec les signatures précédentes</li>";
    echo "<li><strong>Logs Laravel</strong> : vérifier les messages de débogage</li>";
    echo "</ol>";
    
    echo "<h3>🔗 URLs de test :</h3>";
    echo "<ul>";
    echo "<li><a href='/signatures-simple' target='_blank'>Liste des signatures séquentielles</a></li>";
    echo "<li><a href='/signatures-simple/debug' target='_blank'>Debug des signatures séquentielles</a></li>";
    echo "<li><a href='/debug-signed-documents.php' target='_blank'>Diagnostic des documents signés</a></li>";
    echo "</ul>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section'>";
    echo "<h2 class='error'>❌ Erreur lors du test</h2>";
    echo "<p class='error'>Erreur: " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><em>Test généré le " . date('Y-m-d H:i:s') . "</em></p>";
?>
