<?php
// Simulate the request that is failing
$url = 'http://localhost/fiberlink/api/client_data.php?client_id=1277'; // Using the ID from previous debug
$response = file_get_contents($url);
echo $response;
?>
