<?php
/**
 * DIAGNOSTIC DU PROBLÈME DE FLUX SÉQUENTIEL
 * Pourquoi Ahmedou ne voit pas le document signé par Moustapha ?
 */

// Configuration d'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnostic du problème de flux séquentiel</h1>";
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
    .critical { background: #ffe6e6; border-left: 4px solid #ff0000; }
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
    // 1. ÉTAT ACTUEL DES DOCUMENTS SÉQUENTIELS
    // ========================================
    echo "<div class='section'>";
    echo "<h2>📄 État actuel des documents séquentiels</h2>";
    
    $stmt = $pdo->query("
        SELECT d.id, d.document_name, d.status, d.current_signature_index, d.sequential_signatures,
               d.created_at, d.updated_at
        FROM documents d
        WHERE d.sequential_signatures = 1
        ORDER BY d.id
    ");
    
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($documents) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Statut</th><th>Index</th><th>Séquentiel</th><th>Créé</th><th>Modifié</th></tr>";
        
        foreach ($documents as $doc) {
            $statusClass = $doc['status'] === 'in_progress' ? 'success' : 'warning';
            $sequentialClass = $doc['sequential_signatures'] ? 'success' : 'error';
            
            echo "<tr>";
            echo "<td>{$doc['id']}</td>";
            echo "<td>{$doc['document_name']}</td>";
            echo "<td class='$statusClass'>{$doc['status']}</td>";
            echo "<td>{$doc['current_signature_index']}</td>";
            echo "<td class='$sequentialClass'>" . ($doc['sequential_signatures'] ? 'OUI' : 'NON') . "</td>";
            echo "<td>{$doc['created_at']}</td>";
            echo "<td>{$doc['updated_at']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='error'>❌ Aucun document séquentiel trouvé !</p>";
    }
    
    echo "</div>";
    
    // ========================================
    // 2. ANALYSE DES SIGNATURES SÉQUENTIELLES
    // ========================================
    echo "<div class='section'>";
    echo "<h2>✍️ Analyse des signatures séquentielles</h2>";
    
    $stmt = $pdo->query("
        SELECT ss.document_id, d.document_name, ss.user_id, u.name as user_name,
               ss.signature_order, ss.status, ss.signed_at, ss.created_at,
               d.current_signature_index,
               CASE 
                   WHEN ss.signature_order = d.current_signature_index + 1 THEN 'OUI'
                   ELSE 'NON'
               END as is_current_turn,
               CASE 
                   WHEN ss.status = 'pending' AND ss.signature_order = d.current_signature_index + 1 THEN 'À SIGNER'
                   WHEN ss.status = 'signed' THEN 'SIGNÉ'
                   ELSE 'EN ATTENTE'
               END as action_status
        FROM sequential_signatures ss
        JOIN documents d ON ss.document_id = d.id
        JOIN users u ON ss.user_id = u.id
        WHERE d.sequential_signatures = 1
        ORDER BY ss.document_id, ss.signature_order
    ");
    
    $signatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($signatures) > 0) {
        echo "<table>";
        echo "<tr><th>Document</th><th>Signataire</th><th>Ordre</th><th>Statut</th><th>Index</th><th>Tour?</th><th>Action</th><th>Signé le</th></tr>";
        
        foreach ($signatures as $sig) {
            $statusClass = $sig['status'] === 'signed' ? 'success' : 'warning';
            $turnClass = $sig['is_current_turn'] === 'OUI' ? 'success' : 'info';
            $actionClass = strpos($sig['action_status'], 'À SIGNER') !== false ? 'success' : 'info';
            
            echo "<tr>";
            echo "<td>{$sig['document_name']} (ID: {$sig['document_id']})</td>";
            echo "<td>{$sig['user_name']} (ID: {$sig['user_id']})</td>";
            echo "<td>{$sig['signature_order']}</td>";
            echo "<td class='$statusClass'>{$sig['status']}</td>";
            echo "<td>{$sig['current_signature_index']}</td>";
            echo "<td class='$turnClass'>{$sig['is_current_turn']}</td>";
            echo "<td class='$actionClass'>{$sig['action_status']}</td>";
            echo "<td>" . ($sig['signed_at'] ? $sig['signed_at'] : 'N/A') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='error'>❌ Aucune signature séquentielle trouvée !</p>";
    }
    
    echo "</div>";
    
    // ========================================
    // 3. DIAGNOSTIC SPÉCIFIQUE POUR AHMEDOU
    // ========================================
    echo "<div class='section'>";
    echo "<h2>👤 Diagnostic spécifique pour Ahmedou</h2>";
    
    // Trouver Ahmedou
    $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE name LIKE '%Ahmedou%' OR email LIKE '%ahmedou%'");
    $stmt->execute();
    $ahmedou = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($ahmedou) {
        echo "<div class='debug-step'>";
        echo "<p><strong>Utilisateur Ahmedou trouvé:</strong> {$ahmedou['name']} (ID: {$ahmedou['id']})</p>";
        echo "</div>";
        
        // Vérifier les signatures séquentielles d'Ahmedou
        $stmt = $pdo->prepare("
            SELECT ss.document_id, d.document_name, ss.signature_order, ss.status,
                   d.current_signature_index,
                   CASE 
                       WHEN ss.signature_order = d.current_signature_index + 1 THEN 'OUI'
                       ELSE 'NON'
                   END as is_current_turn
            FROM sequential_signatures ss
            JOIN documents d ON ss.document_id = d.id
            WHERE ss.user_id = ? AND d.sequential_signatures = 1
            ORDER BY ss.document_id, ss.signature_order
        ");
        
        $stmt->execute([$ahmedou['id']]);
        $ahmedouSignatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>📋 Signatures séquentielles d'Ahmedou :</h3>";
        
        if (count($ahmedouSignatures) > 0) {
            echo "<table>";
            echo "<tr><th>Document</th><th>Ordre</th><th>Statut</th><th>Index</th><th>Tour?</th><th>Problème</th></tr>";
            
            foreach ($ahmedouSignatures as $sig) {
                $statusClass = $sig['status'] === 'signed' ? 'success' : 'warning';
                $turnClass = $sig['is_current_turn'] === 'OUI' ? 'success' : 'info';
                
                // Diagnostiquer le problème
                $problem = '';
                if ($sig['status'] === 'signed') {
                    $problem = 'Déjà signé';
                } elseif ($sig['is_current_turn'] === 'NON') {
                    $problem = 'Pas son tour';
                } elseif ($sig['status'] === 'pending' && $sig['is_current_turn'] === 'OUI') {
                    $problem = '✅ Doit signer';
                } else {
                    $problem = '❓ Statut inconnu';
                }
                
                echo "<tr>";
                echo "<td>{$sig['document_name']} (ID: {$sig['document_id']})</td>";
                echo "<td>{$sig['signature_order']}</td>";
                echo "<td class='$statusClass'>{$sig['status']}</td>";
                echo "<td>{$sig['current_signature_index']}</td>";
                echo "<td class='$turnClass'>{$sig['is_current_turn']}</td>";
                echo "<td>$problem</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p class='error'>❌ Ahmedou n'a aucune signature séquentielle !</p>";
        }
    } else {
        echo "<p class='error'>❌ Utilisateur Ahmedou non trouvé !</p>";
    }
    
    echo "</div>";
    
    // ========================================
    // 4. VÉRIFICATION DES PDF SIGNÉS
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
    
    $signedPdfs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($signedPdfs) > 0) {
        echo "<table>";
        echo "<tr><th>Document</th><th>Signé par</th><th>PDF</th><th>Signé le</th><th>URL</th></tr>";
        
        foreach ($signedPdfs as $pdf) {
            $pdfFile = basename($pdf['path_signed_pdf']);
            $pdfUrl = "http://localhost:8000/storage/" . str_replace('documents/', '', $pdf['path_signed_pdf']);
            
            echo "<tr>";
            echo "<td>{$pdf['document_name']} (ID: {$pdf['document_id']})</td>";
            echo "<td>{$pdf['signer_name']} (ID: {$pdf['signed_by']})</td>";
            echo "<td>$pdfFile</td>";
            echo "<td>{$pdf['signed_at']}</td>";
            echo "<td><a href='$pdfUrl' target='_blank'>Voir le PDF</a></td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Aucun PDF signé trouvé pour les documents séquentiels.</p>";
    }
    
    echo "</div>";
    
    // ========================================
    // 5. COMMANDES DE CORRECTION
    // ========================================
    echo "<div class='section'>";
    echo "<h2>🔧 Commandes de correction</h2>";
    
    echo "<h3>Si Ahmedou ne voit pas le document :</h3>";
    echo "<ol>";
    echo "<li><strong>Vérifiez que l'index <code>current_signature_index</code> est correct</strong></li>";
    echo "<li><strong>Vérifiez que le statut du document est 'in_progress'</strong></li>";
    echo "<li><strong>Vérifiez que la signature séquentielle d'Ahmedou a le statut 'pending'</strong></li>";
    echo "<li><strong>Vérifiez que l'ordre de signature d'Ahmedou correspond à l'index + 1</strong></li>";
    echo "</ol>";
    
    echo "<h3>Commandes SQL de diagnostic :</h3>";
    echo "<pre>";
    echo "-- Vérifier l'état des documents séquentiels\n";
    echo "SELECT id, document_name, status, current_signature_index, sequential_signatures FROM documents WHERE sequential_signatures = 1;\n\n";
    echo "-- Vérifier les signatures séquentielles\n";
    echo "SELECT ss.*, u.name as user_name FROM sequential_signatures ss JOIN users u ON ss.user_id = u.id WHERE ss.document_id IN (SELECT id FROM documents WHERE sequential_signatures = 1);\n\n";
    echo "-- Vérifier les PDF signés\n";
    echo "SELECT ds.*, u.name as signer_name FROM document_signatures ds JOIN users u ON ds.signed_by = u.id WHERE ds.document_id IN (SELECT id FROM documents WHERE sequential_signatures = 1);\n";
    echo "</pre>";
    
    echo "<h3>🔗 URLs de test :</h3>";
    echo "<ul>";
    echo "<li><a href='/signatures-simple' target='_blank'>Liste des signatures séquentielles (connectez-vous avec Ahmedou)</a></li>";
    echo "<li><a href='/signatures-simple/debug' target='_blank'>Debug des signatures séquentielles</a></li>";
    echo "<li><a href='/test-sequential-pdf-flow.php' target='_blank'>Test du flux PDF séquentiel</a></li>";
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
