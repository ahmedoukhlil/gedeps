<?php
/**
 * DIAGNOSTIC DES DOCUMENTS SÉQUENTIELS DANS L'HISTORIQUE
 * Vérifier pourquoi les documents séquentiels n'apparaissent pas
 */

// Configuration d'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnostic des documents séquentiels dans l'historique</h1>";
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
    echo "<h2>🔄 Vérification des documents séquentiels</h2>";
    
    $stmt = $pdo->query("
        SELECT id, document_name, status, sequential_signatures, uploaded_by, signer_id, created_at, updated_at
        FROM documents 
        WHERE sequential_signatures = 1
        ORDER BY id
    ");
    
    $sequentialDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='debug-step'>";
    echo "<p><strong>Documents séquentiels trouvés:</strong> " . count($sequentialDocs) . "</p>";
    echo "</div>";
    
    if (count($sequentialDocs) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Statut</th><th>Séquentiel</th><th>Uploadé par</th><th>Signataire</th><th>Créé</th><th>Modifié</th></tr>";
        
        foreach ($sequentialDocs as $doc) {
            $statusClass = $doc['status'] === 'signed' ? 'success' : 'warning';
            $sequentialClass = $doc['sequential_signatures'] ? 'success' : 'error';
            
            echo "<tr>";
            echo "<td>{$doc['id']}</td>";
            echo "<td>{$doc['document_name']}</td>";
            echo "<td class='$statusClass'>{$doc['status']}</td>";
            echo "<td class='$sequentialClass'>" . ($doc['sequential_signatures'] ? 'OUI' : 'NON') . "</td>";
            echo "<td>{$doc['uploaded_by']}</td>";
            echo "<td>{$doc['signer_id']}</td>";
            echo "<td>{$doc['created_at']}</td>";
            echo "<td>{$doc['updated_at']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Aucun document séquentiel trouvé.</p>";
    }
    
    echo "</div>";
    
    // ========================================
    // 2. VÉRIFICATION DES SIGNATURES SÉQUENTIELLES
    // ========================================
    echo "<div class='section'>";
    echo "<h2>✍️ Vérification des signatures séquentielles</h2>";
    
    $stmt = $pdo->query("
        SELECT ss.document_id, d.document_name, ss.user_id, u.name as user_name,
               ss.signature_order, ss.status, ss.signed_at, ss.created_at
        FROM sequential_signatures ss
        JOIN documents d ON ss.document_id = d.id
        JOIN users u ON ss.user_id = u.id
        WHERE d.sequential_signatures = 1
        ORDER BY ss.document_id, ss.signature_order
    ");
    
    $signatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($signatures) > 0) {
        echo "<table>";
        echo "<tr><th>Document</th><th>Signataire</th><th>Ordre</th><th>Statut</th><th>Signé le</th></tr>";
        
        foreach ($signatures as $sig) {
            $statusClass = $sig['status'] === 'signed' ? 'success' : 'warning';
            
            echo "<tr>";
            echo "<td>{$sig['document_name']} (ID: {$sig['document_id']})</td>";
            echo "<td>{$sig['user_name']} (ID: {$sig['user_id']})</td>";
            echo "<td>{$sig['signature_order']}</td>";
            echo "<td class='$statusClass'>{$sig['status']}</td>";
            echo "<td>" . ($sig['signed_at'] ? $sig['signed_at'] : 'N/A') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Aucune signature séquentielle trouvée.</p>";
    }
    
    echo "</div>";
    
    // ========================================
    // 3. SIMULATION DE LA REQUÊTE D'HISTORIQUE POUR CHAQUE UTILISATEUR
    // ========================================
    echo "<div class='section'>";
    echo "<h2>👥 Simulation de la requête d'historique par utilisateur</h2>";
    
    // Récupérer tous les utilisateurs
    $stmt = $pdo->query("SELECT id, name, email FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        echo "<div class='debug-step'>";
        echo "<h3>👤 Utilisateur: {$user['name']} (ID: {$user['id']})</h3>";
        
        // Simuler la requête d'historique pour cet utilisateur
        $stmt = $pdo->prepare("
            SELECT d.id, d.document_name, d.status, d.sequential_signatures,
                   d.uploaded_by, d.signer_id,
                   CASE 
                       WHEN d.sequential_signatures = 1 THEN 'Séquentiel'
                       ELSE 'Unique'
                   END as type
            FROM documents d
            WHERE (
                (d.signer_id = ? AND d.status IN ('signed', 'pending', 'paraphed', 'signed_and_paraphed'))
                OR 
                (d.sequential_signatures = 1 AND d.status IN ('in_progress', 'signed') 
                 AND EXISTS (SELECT 1 FROM sequential_signatures ss WHERE ss.document_id = d.id AND ss.user_id = ?))
            )
            ORDER BY d.updated_at DESC
        ");
        
        $stmt->execute([$user['id'], $user['id']]);
        $userDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Documents dans l'historique:</strong> " . count($userDocs) . "</p>";
        
        if (count($userDocs) > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Nom</th><th>Type</th><th>Statut</th><th>Uploadé par</th><th>Signataire</th></tr>";
            
            foreach ($userDocs as $doc) {
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
        } else {
            echo "<p class='info'>Aucun document dans l'historique pour cet utilisateur.</p>";
        }
        
        echo "</div>";
    }
    
    echo "</div>";
    
    // ========================================
    // 4. DIAGNOSTIC SPÉCIFIQUE
    // ========================================
    echo "<div class='section'>";
    echo "<h2>🔍 Diagnostic spécifique</h2>";
    
    echo "<h3>Problèmes potentiels :</h3>";
    echo "<ol>";
    echo "<li><strong>Documents séquentiels avec statut 'signed'</strong> : Peuvent ne pas apparaître si l'utilisateur n'a pas participé</li>";
    echo "<li><strong>Documents séquentiels avec statut 'in_progress'</strong> : Doivent apparaître pour les participants</li>";
    echo "<li><strong>Permissions utilisateur</strong> : Chaque utilisateur ne voit que ses documents</li>";
    echo "</ol>";
    
    echo "<h3>🔗 URLs de test :</h3>";
    echo "<ul>";
    echo "<li><a href='/documents/history' target='_blank'>Page d'historique des documents</a></li>";
    echo "<li><a href='/test-history-display.php' target='_blank'>Test de l'affichage de l'historique</a></li>";
    echo "<li><a href='/debug-db.php' target='_blank'>Diagnostic base de données</a></li>";
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
