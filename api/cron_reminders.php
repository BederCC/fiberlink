<?php
// This script is intended to be run by a CRON JOB once a day
// Example: 0 9 * * * php /path/to/api/cron_reminders.php

include_once '../config/database.php';
include_once '../includes/Mailer.php';

$database = new Database();
$db = $database->getConnection();
$mailer = new Mailer();

echo "Starting Reminder Process...\n";

// 1. Determine target invoices
if (isset($_GET['type']) && $_GET['type'] === 'all') {
    // Select all unpaid invoices due in the next 3 days OR overdue
    $target_date = date('Y-m-d', strtotime("+3 days"));
    $query = "SELECT i.*, c.fullname, c.email 
              FROM invoices i 
              JOIN clients c ON i.client_id = c.id 
              WHERE i.due_date <= :target_date 
              AND i.status = 'unpaid' 
              AND c.email IS NOT NULL 
              AND c.email != ''";
} else {
    // Default behavior: specific day (default 3)
    $days_before = isset($_GET['days']) ? intval($_GET['days']) : 3;
    $target_date = date('Y-m-d', strtotime(($days_before >= 0 ? "+" : "") . $days_before . " days"));
    
    $query = "SELECT i.*, c.fullname, c.email 
              FROM invoices i 
              JOIN clients c ON i.client_id = c.id 
              WHERE i.due_date = :target_date 
              AND i.status = 'unpaid' 
              AND c.email IS NOT NULL 
              AND c.email != ''";
}

$stmt = $db->prepare($query);
$stmt->bindParam(":target_date", $target_date);
$stmt->execute();
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count = 0;

foreach ($invoices as $inv) {
    echo "Sending reminder to {$inv['fullname']} ({$inv['email']}) for Invoice {$inv['invoice_number']}...\n";
    
    $daysRemaining = round((strtotime($inv['due_date']) - strtotime(date('Y-m-d'))) / 86400);

    $invoiceData = [
        'invoice_number' => $inv['invoice_number'],
        'due_date' => date('d/m/Y', strtotime($inv['due_date'])),
        'amount' => number_format($inv['total_amount'], 2),
        'days_remaining' => $daysRemaining
    ];
    
    if($mailer->sendReminder($inv['email'], $inv['fullname'], $invoiceData)) {
        echo "Sent successfully.\n";
        $count++;
    } else {
        echo "Failed to send.\n";
    }
}

echo "Processed $count reminders.\n";
?>
