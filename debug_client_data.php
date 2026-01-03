<?php
include_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get a client ID
$stmt = $db->query("SELECT id FROM clients LIMIT 1");
$client = $stmt->fetch(PDO::FETCH_ASSOC);
$clientId = $client['id'];

echo "Testing with Client ID: $clientId\n";

// Simulate the API logic
$q_service = "SELECT s.*, p.name as plan_name, p.speed_mbps 
              FROM services s 
              JOIN plans p ON s.plan_id = p.id 
              WHERE s.client_id = :cid 
              ORDER BY s.id DESC LIMIT 1";
try {
    $stmt = $db->prepare($q_service);
    $stmt->bindParam(":cid", $clientId);
    $stmt->execute();
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Service Query OK. Result: " . ($service ? "Found" : "Not Found") . "\n";
    if(!$service) {
        // Check if service exists without join
        $stmt2 = $db->prepare("SELECT * FROM services WHERE client_id = :cid");
        $stmt2->bindParam(":cid", $clientId);
        $stmt2->execute();
        $s2 = $stmt2->fetch(PDO::FETCH_ASSOC);
        echo "Raw Service Check: " . ($s2 ? "Found" : "Not Found") . "\n";
        if($s2) print_r($s2);
    }
} catch (Exception $e) {
    echo "Service Query Error: " . $e->getMessage() . "\n";
}

$q_invoices = "SELECT * FROM invoices 
               WHERE client_id = :cid 
               ORDER BY issue_date DESC 
               LIMIT 10";
try {
    $stmtInv = $db->prepare($q_invoices);
    $stmtInv->bindParam(":cid", $clientId);
    $stmtInv->execute();
    $invoices = $stmtInv->fetchAll(PDO::FETCH_ASSOC);
    echo "Invoices Query OK. Count: " . count($invoices) . "\n";
} catch (Exception $e) {
    echo "Invoices Query Error: " . $e->getMessage() . "\n";
}
?>
