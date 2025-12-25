<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// 1. Stats Counters
$stats = [];

// Active Installations
$query = "SELECT COUNT(*) as count FROM services WHERE service_status = 'active'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['active_services'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Pending Payments (Unpaid + Overdue)
$query = "SELECT COUNT(*) as count FROM invoices WHERE status IN ('unpaid', 'overdue')";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['pending_payments'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Monthly Income (Paid invoices in current month)
$currentMonth = date('m');
$currentYear = date('Y');
$query = "SELECT SUM(amount) as total FROM payments 
          WHERE MONTH(payment_date) = :month 
          AND YEAR(payment_date) = :year";
$stmt = $db->prepare($query);
$stmt->bindParam(":month", $currentMonth);
$stmt->bindParam(":year", $currentYear);
$stmt->execute();
$stats['monthly_income'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// 2. Recent Activity (Last 5 services)
$query = "SELECT s.id, c.fullname, s.service_status, s.installation_date 
          FROM services s 
          JOIN clients c ON s.client_id = c.id 
          ORDER BY s.installation_date DESC, s.id DESC 
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. System Alerts
$alerts = [];

// Overdue Invoices
$query = "SELECT i.invoice_number, c.fullname, i.total_amount, i.due_date 
          FROM invoices i 
          JOIN clients c ON i.client_id = c.id 
          WHERE i.status = 'overdue' 
          ORDER BY i.due_date ASC 
          LIMIT 3";
$stmt = $db->prepare($query);
$stmt->execute();
$overdue_invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($overdue_invoices as $inv) {
    $alerts[] = [
        'type' => 'danger',
        'message' => "Pago vencido: {$inv['fullname']} (S/ {$inv['total_amount']})"
    ];
}

// Low Stock Products (Assuming 'stock' column exists, checking < 10)
try {
    $query = "SELECT name, stock FROM products WHERE stock < 10 LIMIT 3";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $low_stock = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($low_stock as $prod) {
        $alerts[] = [
            'type' => 'warning',
            'message' => "Stock bajo: {$prod['name']} ({$prod['stock']} unid.)"
        ];
    }
} catch (Exception $e) {
    // Ignore if table/column doesn't exist
}

// New Pending Installations
$query = "SELECT c.fullname FROM services s 
          JOIN clients c ON s.client_id = c.id 
          WHERE s.service_status = 'pending' 
          LIMIT 3";
$stmt = $db->prepare($query);
$stmt->execute();
$pending_installs = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($pending_installs as $inst) {
    $alerts[] = [
        'type' => 'info',
        'message' => "Nueva solicitud: {$inst['fullname']}"
    ];
}

// 4. Chart Data: Income History (Last 6 Months)
$income_history = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $monthName = date('M', strtotime("-$i months"));
    
    $query = "SELECT SUM(amount) as total FROM payments 
              WHERE DATE_FORMAT(payment_date, '%Y-%m') = :month";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":month", $month);
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    $income_history[] = [
        'month' => $monthName,
        'amount' => floatval($total)
    ];
}

// 5. Widget A: Plan Distribution
$query = "SELECT p.name, COUNT(s.id) as count 
          FROM services s 
          JOIN plans p ON s.plan_id = p.id 
          WHERE s.service_status = 'active' 
          GROUP BY p.id 
          ORDER BY count DESC 
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$plan_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 6. Widget B: Service Status Distribution
$query = "SELECT service_status, COUNT(*) as count FROM services GROUP BY service_status";
$stmt = $db->prepare($query);
$stmt->execute();
$status_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'stats' => $stats,
    'recent_activity' => $recent_activity,
    'alerts' => $alerts,
    'income_history' => $income_history,
    'plan_distribution' => $plan_distribution,
    'status_distribution' => $status_distribution
]);
?>
