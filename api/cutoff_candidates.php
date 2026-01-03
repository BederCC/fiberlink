<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Fetch unpaid invoices ordered by due date
// We assume 'unpaid' and 'overdue' are the relevant statuses
$query = "
    SELECT 
        i.id, i.invoice_number, i.total_amount, i.due_date,
        c.fullname, c.address, c.phone
    FROM invoices i
    JOIN clients c ON i.client_id = c.id
    WHERE i.status IN ('unpaid', 'overdue')
    ORDER BY i.due_date ASC
";

$stmt = $db->prepare($query);
$stmt->execute();
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

$candidates = [];
$now = new DateTime(); // Current time

foreach ($invoices as $inv) {
    $dueDate = new DateTime($inv['due_date']);
    
    // We want the difference relative to now.
    // If due_date is Jan 16 and now is Jan 2, diff is 14 days (future).
    // If due_date was Jan 1 and now is Jan 2, diff is 1 day (past).
    
    $interval = $now->diff($dueDate);
    $days = $interval->days;
    $isPast = $interval->invert; // 1 if $now > $dueDate (overdue)

    // Calculate signed days remaining
    // If future: +days
    // If past: -days
    $daysRemaining = $isPast ? -$days : $days;

    // Logic: Show if it's overdue OR if it's coming up soon (e.g. within next 30 days)
    // User mentioned "today is Jan 2, due Jan 16", that's 14 days.
    // So we'll include anything <= 20 days just to be safe.
    if ($daysRemaining <= 20) {
        $candidates[] = [
            'id' => $inv['id'],
            'client' => $inv['fullname'],
            'address' => $inv['address'],
            'amount' => $inv['total_amount'],
            'due_date' => $inv['due_date'],
            'days_remaining' => $daysRemaining
        ];
    }
}

echo json_encode($candidates);
?>
