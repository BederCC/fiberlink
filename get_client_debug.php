<?php
include_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();
$stmt = $db->query("SELECT dni_ruc, email FROM clients LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($row);
?>
