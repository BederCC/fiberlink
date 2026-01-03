<?php
require_once '../includes/Mailer.php';

echo "Testing Mailer...\n";

$mailer = new Mailer();
$result = $mailer->sendActivationEmail('test@example.com', 'Test User', 'http://example.com');

if ($result) {
    echo "Email sent successfully!\n";
} else {
    echo "Email failed.\n";
    echo "Error: " . $mailer->getLastError() . "\n";
}
?>
