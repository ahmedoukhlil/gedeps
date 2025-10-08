<?php
/**
 * DIAGNOSTIC BASE DE DONNÉES - Signatures Séquentielles
 * Script pour vérifier directement la base de données
 */

// Configuration d'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnostic Base de Données - Signatures Séquentielles</h1>";
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
    // 1. VÉRIFICATION DES DOCUMENTS SÉQUENTIELS
    // ========================================
    echo "<div class='section'>";
    echo "<h2>📄 Documents avec signatures séquentielles</h2>";
    
    $stmt = $pdo->query("
        SELECT id, document_name, status, current_signature_index, created_at, updated_at
        FROM documents 
        WHERE sequential_signatures = 1 
        AND status IN ('in_progress', 'pending')
        ORDER BY updated_at DESC
    ");
    
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='debug-step'>";
    echo "<p><strong>Total documents séquentiels:</strong> " . count($documents) . "</p>";
    echo "</div>";
    
    if (count($documents) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Statut</th><th>Index</th><th>Créé</th><th>Modifié</th></tr>";
        
        foreach ($documents as $doc) {
            echo "<tr>";
            echo "<td>{$doc['id']}</td>";
            echo "<td>{$doc['document_name']}</td>";
            echo "<td>{$doc['status']}</td>";
            echo "<td>{$doc['current_signature_index']}</td>";
            echo "<td>{$doc['created_at']}</td>";
            echo "<td>{$doc['updated_at']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Aucun document avec signatures séquentielles trouvé.</p>";
    }
    
    echo "</div>";
    
    // ========================================
    // 2. VÉRIFICATION DES SIGNATURES SÉQUENTIELLES
    // ========================================
    echo "<div class='section'>";
    echo "<h2>✍️ Signatures séquentielles</h2>";
    
    $stmt = $pdo->query("
        SELECT ss.id, ss.document_id, ss.user_id, ss.signature_order, ss.status, ss.created_at,
               d.document_name, u.name as user_name
        FROM sequential_signatures ss
        JOIN documents d ON ss.document_id = d.id
        JOIN users u ON ss.user_id = u.id
        ORDER BY ss.document_id, ss.signature_order
    ");
    
    $signatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='debug-step'>";
    echo "<p><strong>Total signatures séquentielles:</strong> " . count($signatures) . "</p>";
    echo "</div>";
    
    if (count($signatures) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Document</th><th>Utilisateur</th><th>Ordre</th><th>Statut</th><th>Créé</th></tr>";
        
        foreach ($signatures as $sig) {
            $statusClass = $sig['status'] === 'signed' ? 'success' : 'warning';
            echo "<tr>";
            echo "<td>{$sig['id']}</td>";
            echo "<td>{$sig['document_name']} (ID: {$sig['document_id']})</td>";
            echo "<td>{$sig['user_name']} (ID: {$sig['user_id']})</td>";
            echo "<td>{$sig['signature_order']}</td>";
            echo "<td class='$statusClass'>{$sig['status']}</td>";
            echo "<td>{$sig['created_at']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Aucune signature séquentielle trouvée.</p>";
    }
    
    echo "</div>";
    
    // ========================================
    // 3. ANALYSE PAR UTILISATEUR
    // ========================================
    echo "<div class='section'>";
    echo "<h2>👥 Analyse par utilisateur</h2>";
    
    $stmt = $pdo->query("
        SELECT u.id, u.name, u.email,
               COUNT(ss.id) as total_signatures,
               SUM(CASE WHEN ss.status = 'pending' THEN 1 ELSE 0 END) as pending_signatures,
               SUM(CASE WHEN ss.status = 'signed' THEN 1 ELSE 0 END) as signed_signatures
        FROM users u
        LEFT JOIN sequential_signatures ss ON u.id = ss.user_id
        GROUP BY u.id, u.name, u.email
        ORDER BY u.name
    ");
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Utilisateur</th><th>Email</th><th>Total</th><th>En attente</th><th>Signées</th></tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['name']} (ID: {$user['id']})</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['total_signatures']}</td>";
        echo "<td class='warning'>{$user['pending_signatures']}</td>";
        echo "<td class='success'>{$user['signed_signatures']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "</div>";
    
    // ========================================
    // 4. COMMANDES DE CORRECTION
    // ========================================
    echo "<div class='section'>";
    echo "<h2>🔧 Commandes de correction</h2>";
    
    echo "<h3>Si aucun document n'apparaît :</h3>";
    echo "<ol>";
    echo "<li><strong>Vérifiez qu'il y a des documents avec <code>sequential_signatures = 1</code></strong></li>";
    echo "<li><strong>Vérifiez que les documents ont le statut 'in_progress' ou 'pending'</strong></li>";
    echo "<li><strong>Vérifiez qu'il y a des signatures séquentielles pour l'utilisateur</strong></li>";
    echo "<li><strong>Vérifiez que l'index <code>current_signature_index</code> est correct</strong></li>";
    echo "</ol>";
    
    echo "<h3>URLs de test :</h3>";
    echo "<ul>";
    echo "<li><a href='/signatures-simple' target='_blank'>Liste des signatures séquentielles</a></li>";
    echo "<li><a href='/signatures-simple/debug' target='_blank'>Debug des signatures séquentielles</a></li>";
    echo "<li><a href='/test-sequential-flow.php' target='_blank'>Test complet du flux</a></li>";
    echo "</ul>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section'>";
    echo "<h2 class='error'>❌ Erreur de connexion à la base de données</h2>";
    echo "<p class='error'>Erreur: " . $e->getMessage() . "</p>";
    echo "<p>Vérifiez la configuration de la base de données dans <code>.env</code></p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><em>Diagnostic généré le " . date('Y-m-d H:i:s') . "</em></p>";
?>
