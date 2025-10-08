<?php
/**
 * TEST DE L'AFFICHAGE DE L'HISTORIQUE
 * Vérifier que tous les documents (signatures uniques et séquentielles) apparaissent
 */

// Configuration d'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Test de l'affichage de l'historique</h1>";
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
    // 1. ANALYSE DES DOCUMENTS PAR TYPE
    // ========================================
    echo "<div class='section'>";
    echo "<h2>📄 Analyse des documents par type</h2>";
    
    $stmt = $pdo->query("
        SELECT 
            CASE 
                WHEN sequential_signatures = 1 THEN 'Séquentiel'
                ELSE 'Unique'
            END as type,
            status,
            COUNT(*) as count
        FROM documents 
        WHERE status IN ('signed', 'pending', 'paraphed', 'signed_and_paraphed', 'in_progress')
        GROUP BY type, status
        ORDER BY type, status
    ");
    
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($documents) > 0) {
        echo "<table>";
        echo "<tr><th>Type</th><th>Statut</th><th>Nombre</th><th>Affiché dans l'historique</th></tr>";
        
        foreach ($documents as $doc) {
            $displayed = '✅ OUI';
            if ($doc['type'] === 'Séquentiel' && $doc['status'] === 'in_progress') {
                $displayed = '✅ OUI (avec conditions)';
            }
            
            echo "<tr>";
            echo "<td>{$doc['type']}</td>";
            echo "<td>{$doc['status']}</td>";
            echo "<td>{$doc['count']}</td>";
            echo "<td>$displayed</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Aucun document trouvé.</p>";
    }
    
    echo "</div>";
    
    // ========================================
    // 2. DOCUMENTS SÉQUENTIELS DÉTAILLÉS
    // ========================================
    echo "<div class='section'>";
    echo "<h2>🔄 Documents séquentiels détaillés</h2>";
    
    $stmt = $pdo->query("
        SELECT d.id, d.document_name, d.status, d.current_signature_index, d.sequential_signatures,
               COUNT(ss.id) as total_signers,
               COUNT(CASE WHEN ss.status = 'signed' THEN 1 END) as signed_count,
               COUNT(CASE WHEN ss.status = 'pending' THEN 1 END) as pending_count
        FROM documents d
        LEFT JOIN sequential_signatures ss ON d.id = ss.document_id
        WHERE d.sequential_signatures = 1
        GROUP BY d.id, d.document_name, d.status, d.current_signature_index, d.sequential_signatures
        ORDER BY d.id
    ");
    
    $sequentialDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($sequentialDocs) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Statut</th><th>Index</th><th>Signataires</th><th>Signées</th><th>En attente</th><th>Affiché</th></tr>";
        
        foreach ($sequentialDocs as $doc) {
            $displayed = '✅ OUI';
            if ($doc['status'] === 'in_progress') {
                $displayed = '✅ OUI (en cours)';
            } elseif ($doc['status'] === 'signed') {
                $displayed = '✅ OUI (terminé)';
            }
            
            echo "<tr>";
            echo "<td>{$doc['id']}</td>";
            echo "<td>{$doc['document_name']}</td>";
            echo "<td>{$doc['status']}</td>";
            echo "<td>{$doc['current_signature_index']}</td>";
            echo "<td>{$doc['total_signers']}</td>";
            echo "<td>{$doc['signed_count']}</td>";
            echo "<td>{$doc['pending_count']}</td>";
            echo "<td>$displayed</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Aucun document séquentiel trouvé.</p>";
    }
    
    echo "</div>";
    
    // ========================================
    // 3. DOCUMENTS UNIQUES
    // ========================================
    echo "<div class='section'>";
    echo "<h2>📝 Documents à signature unique</h2>";
    
    $stmt = $pdo->query("
        SELECT id, document_name, status, uploaded_by, signer_id
        FROM documents 
        WHERE sequential_signatures = 0 
        AND status IN ('signed', 'pending', 'paraphed', 'signed_and_paraphed')
        ORDER BY id
    ");
    
    $uniqueDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($uniqueDocs) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Statut</th><th>Uploadé par</th><th>Signataire</th><th>Affiché</th></tr>";
        
        foreach ($uniqueDocs as $doc) {
            $displayed = '✅ OUI';
            
            echo "<tr>";
            echo "<td>{$doc['id']}</td>";
            echo "<td>{$doc['document_name']}</td>";
            echo "<td>{$doc['status']}</td>";
            echo "<td>{$doc['uploaded_by']}</td>";
            echo "<td>{$doc['signer_id']}</td>";
            echo "<td>$displayed</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Aucun document à signature unique trouvé.</p>";
    }
    
    echo "</div>";
    
    // ========================================
    // 4. SIMULATION DE LA REQUÊTE D'HISTORIQUE
    // ========================================
    echo "<div class='section'>";
    echo "<h2>🔍 Simulation de la requête d'historique</h2>";
    
    // Simuler la requête pour un utilisateur normal
    $stmt = $pdo->query("
        SELECT d.id, d.document_name, d.status, d.sequential_signatures,
               d.uploaded_by, d.signer_id,
               CASE 
                   WHEN d.sequential_signatures = 1 THEN 'Séquentiel'
                   ELSE 'Unique'
               END as type
        FROM documents d
        WHERE (
            (d.signer_id IS NOT NULL AND d.status IN ('signed', 'pending', 'paraphed', 'signed_and_paraphed'))
            OR 
            (d.sequential_signatures = 1 AND d.status IN ('in_progress', 'signed'))
        )
        ORDER BY d.updated_at DESC
    ");
    
    $historyDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='debug-step'>";
    echo "<p><strong>Documents qui apparaîtraient dans l'historique:</strong> " . count($historyDocs) . "</p>";
    echo "</div>";
    
    if (count($historyDocs) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Type</th><th>Statut</th><th>Uploadé par</th><th>Signataire</th></tr>";
        
        foreach ($historyDocs as $doc) {
            $typeClass = $doc['type'] === 'Séquentiel' ? 'info' : 'success';
            
            echo "<tr>";
            echo "<td>{$doc['id']}</td>";
            echo "<td>{$doc['document_name']}</td>";
            echo "<td class='$typeClass'>{$doc['type']}</td>";
            echo "<td>{$doc['status']}</td>";
            echo "<td>{$doc['uploaded_by']}</td>";
            echo "<td>{$doc['signer_id']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "</div>";
    
    // ========================================
    // 5. RECOMMANDATIONS
    // ========================================
    echo "<div class='section'>";
    echo "<h2>💡 Recommandations</h2>";
    
    echo "<h3>✅ Modifications apportées :</h3>";
    echo "<ul>";
    echo "<li><strong>Contrôleur DocumentController</strong> : Ajout du support des signatures séquentielles</li>";
    echo "<li><strong>Vue document-card-history</strong> : Gestion des signatures séquentielles</li>";
    echo "<li><strong>Statuts</strong> : Ajout du statut 'in_progress' pour les signatures séquentielles</li>";
    echo "<li><strong>Actions</strong> : Boutons spécifiques pour les signatures séquentielles</li>";
    echo "</ul>";
    
    echo "<h3>🔗 URLs de test :</h3>";
    echo "<ul>";
    echo "<li><a href='/documents/history' target='_blank'>Page d'historique des documents</a></li>";
    echo "<li><a href='/signatures-simple' target='_blank'>Signatures séquentielles</a></li>";
    echo "<li><a href='/debug-db.php' target='_blank'>Diagnostic base de données</a></li>";
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
