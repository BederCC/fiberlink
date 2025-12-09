<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? 'summary';

if ($action === 'summary') {
    // 1. Financial: Income by Month (Last 6 months)
    $q_income = "SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount) as total 
                 FROM payments 
                 WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
                 GROUP BY DATE_FORMAT(payment_date, '%Y-%m') 
                 ORDER BY month ASC";
    $s_income = $db->prepare($q_income);
    $s_income->execute();
    $income_data = $s_income->fetchAll(PDO::FETCH_ASSOC);

    // 2. Operational: Service Status
    $q_status = "SELECT service_status, COUNT(*) as count FROM services GROUP BY service_status";
    $s_status = $db->prepare($q_status);
    $s_status->execute();
    $status_data = $s_status->fetchAll(PDO::FETCH_ASSOC);

    // 3. Metrics: Average Time to Payment (Last 30 days)
    $q_metric = "SELECT AVG(duration_seconds) as avg_seconds 
                 FROM payment_metrics 
                 WHERE payment_timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $s_metric = $db->prepare($q_metric);
    $s_metric->execute();
    $metric_data = $s_metric->fetch(PDO::FETCH_ASSOC);

    // 4. Top Debtors
    $q_debtors = "SELECT c.first_name, c.last_name, SUM(i.total_amount) as debt 
                  FROM invoices i 
                  JOIN clients c ON i.client_id = c.id 
                  WHERE i.status IN ('unpaid', 'overdue') 
                  GROUP BY c.id 
                  ORDER BY debt DESC 
                  LIMIT 5";
    $s_debtors = $db->prepare($q_debtors);
    $s_debtors->execute();
    $debtors_data = $s_debtors->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'income' => $income_data,
        'status' => $status_data,
        'avg_payment_time' => round($metric_data['avg_seconds'] ?? 0),
        'debtors' => $debtors_data
    ]);

} elseif ($action === 'export_debts') {
    // Export logic (simplified for now)
    // In a real app, this would generate a CSV/PDF
    // For now, just return the raw data for frontend to handle or download
    $q_all_debts = "SELECT c.first_name, c.last_name, c.dni_ruc, i.invoice_number, i.total_amount, i.due_date 
                    FROM invoices i 
                    JOIN clients c ON i.client_id = c.id 
                    WHERE i.status IN ('unpaid', 'overdue') 
                    ORDER BY i.due_date ASC";
    $s_all = $db->prepare($q_all_debts);
    $s_all->execute();
    echo json_encode($s_all->fetchAll(PDO::FETCH_ASSOC));
}
?>
