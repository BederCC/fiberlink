<?php
// Mock POST data
$_GET['action'] = 'send_activation';
// We need to simulate php://input
// We can't easily do that without making a real HTTP request or modifying the script to accept an array.
// Instead, let's just use curl to hit the actual endpoint.

$url = 'http://localhost/fiberlink/api/client_auth.php?action=send_activation';
$data = array('dni' => '70116118', 'email' => 'test_user@example.com');

$options = array(
    'http' => array(
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data)
    )
);
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) { 
    echo "Error making request\n";
    print_r(error_get_last());
} else {
    echo "Response: " . $result . "\n";
}
?>
