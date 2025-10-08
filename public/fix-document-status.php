<?php
/**
 * CORRECTION DES STATUTS DES DOCUMENTS SÉQUENTIELS
 * Script pour corriger les statuts des documents avec signatures séquentielles
 */

// Configuration d'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Correction des statuts des documents séquentiels</h1>";
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
    // 1. VÉRIFICATION DES DOCUMENTS AVANT CORRECTION
    // ========================================
    echo "<div class='section'>";
    echo "<h2>📄 État actuel des documents séquentiels</h2>";
    
    $stmt = $pdo->query("
        SELECT id, document_name, status, current_signature_index, sequential_signatures
        FROM documents 
        WHERE sequential_signatures = 1
        ORDER BY id
    ");
    
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($documents) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Statut actuel</th><th>Index</th><th>Action</th></tr>";
        
        foreach ($documents as $doc) {
            $statusClass = in_array($doc['status'], ['in_progress', 'pending']) ? 'success' : 'warning';
            $action = in_array($doc['status'], ['in_progress', 'pending']) ? 'OK' : 'À corriger';
            
            echo "<tr>";
            echo "<td>{$doc['id']}</td>";
            echo "<td>{$doc['document_name']}</td>";
            echo "<td class='$statusClass'>{$doc['status']}</td>";
            echo "<td>{$doc['current_signature_index']}</td>";
            echo "<td class='" . ($action === 'OK' ? 'success' : 'warning') . "'>$action</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Aucun document avec signatures séquentielles trouvé.</p>";
    }
    
    echo "</div>";
    
    // ========================================
    // 2. CORRECTION DES STATUTS
    // ========================================
    echo "<div class='section'>";
    echo "<h2>🔧 Correction des statuts</h2>";
    
    // Corriger les documents qui ne sont pas en 'in_progress' ou 'pending'
    $stmt = $pdo->prepare("
        UPDATE documents 
        SET status = 'in_progress' 
        WHERE sequential_signatures = 1 
        AND status NOT IN ('in_progress', 'pending')
    ");
    
    $result = $stmt->execute();
    $affectedRows = $stmt->rowCount();
    
    echo "<div class='debug-step'>";
    echo "<p class='success'>✅ Correction effectuée</p>";
    echo "<p><strong>Documents corrigés:</strong> $affectedRows</p>";
    echo "</div>";
    
    echo "</div>";
    
    // ========================================
    // 3. VÉRIFICATION APRÈS CORRECTION
    // ========================================
    echo "<div class='section'>";
    echo "<h2>✅ État après correction</h2>";
    
    $stmt = $pdo->query("
        SELECT id, document_name, status, current_signature_index
        FROM documents 
        WHERE sequential_signatures = 1
        ORDER BY id
    ");
    
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($documents) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Nouveau statut</th><th>Index</th><th>Statut</th></tr>";
        
        foreach ($documents as $doc) {
            $statusClass = in_array($doc['status'], ['in_progress', 'pending']) ? 'success' : 'error';
            $statusText = in_array($doc['status'], ['in_progress', 'pending']) ? '✅ Correct' : '❌ Erreur';
            
            echo "<tr>";
            echo "<td>{$doc['id']}</td>";
            echo "<td>{$doc['document_name']}</td>";
            echo "<td class='$statusClass'>{$doc['status']}</td>";
            echo "<td>{$doc['current_signature_index']}</td>";
            echo "<td class='$statusClass'>$statusText</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "</div>";
    
    // ========================================
    // 4. VÉRIFICATION DES SIGNATURES SÉQUENTIELLES
    // ========================================
    echo "<div class='section'>";
    echo "<h2>✍️ Vérification des signatures séquentielles</h2>";
    
    $stmt = $pdo->query("
        SELECT ss.document_id, d.document_name, ss.user_id, u.name as user_name, 
               ss.signature_order, ss.status, d.current_signature_index,
               CASE 
                   WHEN ss.signature_order = d.current_signature_index + 1 THEN 'OUI'
                   ELSE 'NON'
               END as is_current_turn
        FROM sequential_signatures ss
        JOIN documents d ON ss.document_id = d.id
        JOIN users u ON ss.user_id = u.id
        WHERE d.sequential_signatures = 1
        ORDER BY ss.document_id, ss.signature_order
    ");
    
    $signatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($signatures) > 0) {
        echo "<table>";
        echo "<tr><th>Document</th><th>Utilisateur</th><th>Ordre</th><th>Statut</th><th>Index</th><th>Tour?</th><th>Action</th></tr>";
        
        foreach ($signatures as $sig) {
            $statusClass = $sig['status'] === 'signed' ? 'success' : 'warning';
            $turnClass = $sig['is_current_turn'] === 'OUI' ? 'success' : 'info';
            $action = ($sig['status'] === 'pending' && $sig['is_current_turn'] === 'OUI') ? 'À signer' : 
                     ($sig['status'] === 'signed' ? 'Signé' : 'En attente');
            
            echo "<tr>";
            echo "<td>{$sig['document_name']} (ID: {$sig['document_id']})</td>";
            echo "<td>{$sig['user_name']} (ID: {$sig['user_id']})</td>";
            echo "<td>{$sig['signature_order']}</td>";
            echo "<td class='$statusClass'>{$sig['status']}</td>";
            echo "<td>{$sig['current_signature_index']}</td>";
            echo "<td class='$turnClass'>{$sig['is_current_turn']}</td>";
            echo "<td class='" . ($action === 'À signer' ? 'success' : 'info') . "'>$action</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "</div>";
    
    // ========================================
    // 5. RÉSULTATS FINAUX
    // ========================================
    echo "<div class='section'>";
    echo "<h2>🎯 Résultats finaux</h2>";
    
    // Compter les documents à signer pour chaque utilisateur
    $stmt = $pdo->query("
        SELECT u.id, u.name,
               COUNT(CASE WHEN ss.status = 'pending' AND ss.signature_order = d.current_signature_index + 1 THEN 1 END) as documents_to_sign
        FROM users u
        LEFT JOIN sequential_signatures ss ON u.id = ss.user_id
        LEFT JOIN documents d ON ss.document_id = d.id AND d.sequential_signatures = 1
        GROUP BY u.id, u.name
        ORDER BY u.name
    ");
    
    $userStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📊 Documents à signer par utilisateur :</h3>";
    echo "<table>";
    echo "<tr><th>Utilisateur</th><th>Documents à signer</th><th>Statut</th></tr>";
    
    foreach ($userStats as $user) {
        $statusClass = $user['documents_to_sign'] > 0 ? 'success' : 'info';
        $statusText = $user['documents_to_sign'] > 0 ? '✅ Documents disponibles' : 'Aucun document';
        
        echo "<tr>";
        echo "<td>{$user['name']} (ID: {$user['id']})</td>";
        echo "<td class='$statusClass'>{$user['documents_to_sign']}</td>";
        echo "<td class='$statusClass'>$statusText</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h3>🔗 URLs de test :</h3>";
    echo "<ul>";
    echo "<li><a href='/signatures-simple' target='_blank'>Liste des signatures séquentielles</a></li>";
    echo "<li><a href='/signatures-simple/debug' target='_blank'>Debug des signatures séquentielles</a></li>";
    echo "<li><a href='/debug-db.php' target='_blank'>Diagnostic base de données</a></li>";
    echo "</ul>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section'>";
    echo "<h2 class='error'>❌ Erreur lors de la correction</h2>";
    echo "<p class='error'>Erreur: " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><em>Correction effectuée le " . date('Y-m-d H:i:s') . "</em></p>";
?>
