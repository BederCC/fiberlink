<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);
error_reporting(E_ALL);

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!isset($_GET['client_id'])) {
    http_response_code(400);
    echo json_encode(["message" => "Client ID required"]);
    exit;
}

$clientId = intval($_GET['client_id']);

// 1. Fetch Service Info (Main active service)
$q_service = "SELECT s.*, p.name as plan_name, p.speed_mbps 
              FROM services s 
              JOIN plans p ON s.plan_id = p.id 
              WHERE s.client_id = :cid 
              ORDER BY s.id DESC LIMIT 1";
$stmt = $db->prepare($q_service);
$stmt->bindParam(":cid", $clientId);
$stmt->execute();
$service = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. Fetch Invoices (Last 6 months)
$q_invoices = "SELECT * FROM invoices 
               WHERE client_id = :cid 
               ORDER BY issue_date DESC 
               LIMIT 10";
$stmtInv = $db->prepare($q_invoices);
$stmtInv->bindParam(":cid", $clientId);
$stmtInv->execute();
$invoices = $stmtInv->fetchAll(PDO::FETCH_ASSOC);

// Format response
$response = [
    'service' => $service ? [
        'plan_name' => $service['plan_name'],
        'speed_mbps' => $service['speed_mbps'],
        'address' => $service['installation_address'],
        'ip_address' => $service['ip_address'],
        'status' => $service['service_status']
    ] : null,
    'invoices' => $invoices
];

echo json_encode($response);
?>
