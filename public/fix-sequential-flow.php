<?php
/**
 * CORRECTION AUTOMATIQUE DU FLUX SÉQUENTIEL
 * Corriger les problèmes de flux séquentiel pour qu'Ahmedou voie le document
 */

// Configuration d'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Correction automatique du flux séquentiel</h1>";
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
    // 1. DIAGNOSTIC AVANT CORRECTION
    // ========================================
    echo "<div class='section'>";
    echo "<h2>🔍 Diagnostic avant correction</h2>";
    
    $stmt = $pdo->query("
        SELECT d.id, d.document_name, d.status, d.current_signature_index,
               COUNT(ss.id) as total_signers,
               COUNT(CASE WHEN ss.status = 'signed' THEN 1 END) as signed_count,
               COUNT(CASE WHEN ss.status = 'pending' THEN 1 END) as pending_count
        FROM documents d
        LEFT JOIN sequential_signatures ss ON d.id = ss.document_id
        WHERE d.sequential_signatures = 1
        GROUP BY d.id, d.document_name, d.status, d.current_signature_index
        ORDER BY d.id
    ");
    
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='debug-step'>";
    echo "<p><strong>Documents séquentiels trouvés:</strong> " . count($documents) . "</p>";
    echo "</div>";
    
    if (count($documents) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Statut</th><th>Index</th><th>Signataires</th><th>Signées</th><th>En attente</th><th>Problème</th></tr>";
        
        foreach ($documents as $doc) {
            $statusClass = $doc['status'] === 'in_progress' ? 'success' : 'warning';
            $progressClass = $doc['signed_count'] > 0 ? 'success' : 'info';
            
            // Diagnostiquer le problème
            $problem = '';
            if ($doc['status'] !== 'in_progress') {
                $problem = 'Statut incorrect';
            } elseif ($doc['current_signature_index'] == 0 && $doc['signed_count'] > 0) {
                $problem = 'Index non mis à jour';
            } elseif ($doc['pending_count'] == 0) {
                $problem = 'Aucun signataire en attente';
            } else {
                $problem = '✅ OK';
            }
            
            echo "<tr>";
            echo "<td>{$doc['id']}</td>";
            echo "<td>{$doc['document_name']}</td>";
            echo "<td class='$statusClass'>{$doc['status']}</td>";
            echo "<td>{$doc['current_signature_index']}</td>";
            echo "<td>{$doc['total_signers']}</td>";
            echo "<td class='$progressClass'>{$doc['signed_count']}</td>";
            echo "<td>{$doc['pending_count']}</td>";
            echo "<td>$problem</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "</div>";
    
    // ========================================
    // 2. CORRECTION AUTOMATIQUE
    // ========================================
    echo "<div class='section'>";
    echo "<h2>🔧 Correction automatique</h2>";
    
    $corrections = [];
    
    foreach ($documents as $doc) {
        $documentId = $doc['id'];
        $corrections[$documentId] = [];
        
        echo "<div class='debug-step'>";
        echo "<h3>📄 Document: {$doc['document_name']} (ID: {$documentId})</h3>";
        
        // Correction 1: Statut du document
        if ($doc['status'] !== 'in_progress') {
            $stmt = $pdo->prepare("UPDATE documents SET status = 'in_progress' WHERE id = ?");
            $stmt->execute([$documentId]);
            $corrections[$documentId][] = "Statut corrigé vers 'in_progress'";
            echo "<p class='success'>✅ Statut corrigé vers 'in_progress'</p>";
        }
        
        // Correction 2: Index de signature
        if ($doc['signed_count'] > 0 && $doc['current_signature_index'] == 0) {
            // Calculer le bon index basé sur les signatures
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as signed_count 
                FROM sequential_signatures 
                WHERE document_id = ? AND status = 'signed'
            ");
            $stmt->execute([$documentId]);
            $signedCount = $stmt->fetch(PDO::FETCH_ASSOC)['signed_count'];
            
            if ($signedCount > 0) {
                $stmt = $pdo->prepare("UPDATE documents SET current_signature_index = ? WHERE id = ?");
                $stmt->execute([$signedCount, $documentId]);
                $corrections[$documentId][] = "Index mis à jour vers $signedCount";
                echo "<p class='success'>✅ Index mis à jour vers $signedCount</p>";
            }
        }
        
        // Correction 3: Vérifier les signatures séquentielles
        $stmt = $pdo->prepare("
            SELECT ss.*, u.name as user_name 
            FROM sequential_signatures ss 
            JOIN users u ON ss.user_id = u.id 
            WHERE ss.document_id = ? 
            ORDER BY ss.signature_order
        ");
        $stmt->execute([$documentId]);
        $signatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Signatures séquentielles :</h4>";
        echo "<table>";
        echo "<tr><th>Signataire</th><th>Ordre</th><th>Statut</th><th>Index</th><th>Tour?</th><th>Action</th></tr>";
        
        foreach ($signatures as $sig) {
            $statusClass = $sig['status'] === 'signed' ? 'success' : 'warning';
            $turnClass = $sig['signature_order'] == $doc['current_signature_index'] + 1 ? 'success' : 'info';
            
            $action = '';
            if ($sig['status'] === 'signed') {
                $action = '✅ Signé';
            } elseif ($sig['signature_order'] == $doc['current_signature_index'] + 1) {
                $action = '🎯 À signer';
            } else {
                $action = '⏳ En attente';
            }
            
            echo "<tr>";
            echo "<td>{$sig['user_name']} (ID: {$sig['user_id']})</td>";
            echo "<td>{$sig['signature_order']}</td>";
            echo "<td class='$statusClass'>{$sig['status']}</td>";
            echo "<td>{$doc['current_signature_index']}</td>";
            echo "<td class='$turnClass'>" . ($sig['signature_order'] == $doc['current_signature_index'] + 1 ? 'OUI' : 'NON') . "</td>";
            echo "<td>$action</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</div>";
    }
    
    echo "</div>";
    
    // ========================================
    // 3. VÉRIFICATION APRÈS CORRECTION
    // ========================================
    echo "<div class='section'>";
    echo "<h2>✅ Vérification après correction</h2>";
    
    $stmt = $pdo->query("
        SELECT d.id, d.document_name, d.status, d.current_signature_index,
               COUNT(ss.id) as total_signers,
               COUNT(CASE WHEN ss.status = 'signed' THEN 1 END) as signed_count,
               COUNT(CASE WHEN ss.status = 'pending' THEN 1 END) as pending_count
        FROM documents d
        LEFT JOIN sequential_signatures ss ON d.id = ss.document_id
        WHERE d.sequential_signatures = 1
        GROUP BY d.id, d.document_name, d.status, d.current_signature_index
        ORDER BY d.id
    ");
    
    $documentsAfter = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($documentsAfter) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Statut</th><th>Index</th><th>Signataires</th><th>Signées</th><th>En attente</th><th>Statut</th></tr>";
        
        foreach ($documentsAfter as $doc) {
            $statusClass = $doc['status'] === 'in_progress' ? 'success' : 'warning';
            $progressClass = $doc['signed_count'] > 0 ? 'success' : 'info';
            
            $finalStatus = '';
            if ($doc['status'] === 'in_progress' && $doc['pending_count'] > 0) {
                $finalStatus = '✅ Prêt pour le prochain signataire';
            } elseif ($doc['status'] === 'in_progress' && $doc['pending_count'] == 0) {
                $finalStatus = '✅ Toutes les signatures terminées';
            } else {
                $finalStatus = '❌ Problème persistant';
            }
            
            echo "<tr>";
            echo "<td>{$doc['id']}</td>";
            echo "<td>{$doc['document_name']}</td>";
            echo "<td class='$statusClass'>{$doc['status']}</td>";
            echo "<td>{$doc['current_signature_index']}</td>";
            echo "<td>{$doc['total_signers']}</td>";
            echo "<td class='$progressClass'>{$doc['signed_count']}</td>";
            echo "<td>{$doc['pending_count']}</td>";
            echo "<td>$finalStatus</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "</div>";
    
    // ========================================
    // 4. RÉSULTATS FINAUX
    // ========================================
    echo "<div class='section'>";
    echo "<h2>🎯 Résultats finaux</h2>";
    
    echo "<h3>✅ Corrections effectuées :</h3>";
    $totalCorrections = 0;
    foreach ($corrections as $docId => $correctionsList) {
        if (count($correctionsList) > 0) {
            $totalCorrections += count($correctionsList);
            echo "<p><strong>Document ID $docId:</strong></p>";
            echo "<ul>";
            foreach ($correctionsList as $correction) {
                echo "<li>$correction</li>";
            }
            echo "</ul>";
        }
    }
    
    if ($totalCorrections == 0) {
        echo "<p class='success'>✅ Aucune correction nécessaire - Le flux séquentiel est déjà correct !</p>";
    } else {
        echo "<p class='success'>✅ $totalCorrections correction(s) effectuée(s)</p>";
    }
    
    echo "<h3>🔗 URLs de test :</h3>";
    echo "<ul>";
    echo "<li><a href='/signatures-simple' target='_blank'>Liste des signatures séquentielles (connectez-vous avec Ahmedou)</a></li>";
    echo "<li><a href='/signatures-simple/debug' target='_blank'>Debug des signatures séquentielles</a></li>";
    echo "<li><a href='/debug-sequential-flow-issue.php' target='_blank'>Diagnostic du problème de flux</a></li>";
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
