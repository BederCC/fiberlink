<?php
// api/import_clients.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$jsonFile = '../relacion_clientes.json';

if (!file_exists($jsonFile)) {
    http_response_code(404);
    echo json_encode(["message" => "File not found: $jsonFile"]);
    exit;
}

$jsonData = file_get_contents($jsonFile);
$clients = json_decode($jsonData, true);

if (!$clients) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid JSON"]);
    exit;
}

$count = 0;
$errors = 0;
$skipped = 0;

// Prepare statement
// Using INSERT IGNORE to skip duplicates based on dni_ruc (which is UNIQUE in schema)
$query = "INSERT IGNORE INTO clients (fullname, dni_ruc, address, status) VALUES (:fullname, :dni_ruc, :address, 'active')";
$stmt = $db->prepare($query);

foreach ($clients as $index => $client) {
    // Skip header row or invalid rows
    if ($index === 0 && $client['cliente'] === 'Nombre') {
        continue;
    }
    
    if (empty($client['cliente']) || empty($client['dni'])) {
        $skipped++;
        continue;
    }

    $fullname = trim($client['cliente']);
    $dni = trim($client['dni']);
    $address = trim($client['direccion'] ?? '');

    // Bind parameters
    $stmt->bindParam(":fullname", $fullname);
    $stmt->bindParam(":dni_ruc", $dni);
    $stmt->bindParam(":address", $address);

    try {
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $count++;
            } else {
                $skipped++; // Duplicate found and ignored
            }
        } else {
            $errors++;
        }
    } catch (PDOException $e) {
        $errors++;
        // echo "Error: " . $e->getMessage() . "\n";
    }
}

echo json_encode([
    "message" => "Import process completed",
    "inserted" => $count,
    "skipped_or_duplicate" => $skipped,
    "errors" => $errors
]);
?>
