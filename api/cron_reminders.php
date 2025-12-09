<?php
// This script is intended to be run by a CRON JOB once a day
// Example: 0 9 * * * php /path/to/api/cron_reminders.php

include_once '../config/database.php';
include_once '../includes/Mailer.php';

$database = new Database();
$db = $database->getConnection();
$mailer = new Mailer();

echo "Starting Reminder Process...\n";

// 1. Find invoices due in 3 days
$days_before = 3;
$target_date = date('Y-m-d', strtotime("+$days_before days"));

$query = "SELECT i.*, c.first_name, c.last_name, c.email 
          FROM invoices i 
          JOIN clients c ON i.client_id = c.id 
          WHERE i.due_date = :target_date 
          AND i.status = 'unpaid' 
          AND c.email IS NOT NULL 
          AND c.email != ''";

$stmt = $db->prepare($query);
$stmt->bindParam(":target_date", $target_date);
$stmt->execute();
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count = 0;

foreach ($invoices as $inv) {
    echo "Sending reminder to {$inv['first_name']} {$inv['last_name']} ({$inv['email']}) for Invoice {$inv['invoice_number']}...\n";
    
    $invoiceData = [
        'invoice_number' => $inv['invoice_number'],
        'due_date' => date('d/m/Y', strtotime($inv['due_date'])),
        'amount' => number_format($inv['total_amount'], 2)
    ];
    
    if($mailer->sendReminder($inv['email'], $inv['first_name'] . ' ' . $inv['last_name'], $invoiceData)) {
        echo "Sent successfully.\n";
        $count++;
    } else {
        echo "Failed to send.\n";
    }
}

echo "Processed $count reminders.\n";
?>
