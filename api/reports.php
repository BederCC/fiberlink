<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? 'summary';

if ($action === 'summary' || $action === 'export_metrics_pdf' || $action === 'export_metrics_excel' || $action === 'export_payment_logs_pdf') {
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
    $avg_time = ($metric_data && isset($metric_data['avg_seconds'])) ? $metric_data['avg_seconds'] : 0;

    // 4. Top Debtors
    $q_debtors = "SELECT c.fullname, SUM(i.total_amount) as debt 
                  FROM invoices i 
                  JOIN clients c ON i.client_id = c.id 
                  WHERE i.status IN ('unpaid', 'overdue') 
                  GROUP BY c.id 
                  ORDER BY debt DESC 
                  LIMIT 5";
    $s_debtors = $db->prepare($q_debtors);
    $s_debtors->execute();
    $debtors_data = $s_debtors->fetchAll(PDO::FETCH_ASSOC);

    $report_data = [
        'income' => $income_data,
        'status' => $status_data,
        'avg_payment_time' => round($avg_time),
        'debtors' => $debtors_data
    ];

    if ($action === 'summary') {
        echo json_encode($report_data);
    } elseif ($action === 'export_metrics_pdf') {
        include_once '../includes/PdfGenerator.php';
        $pdfGen = new PdfGenerator($db);
        $pdfGen->generateMetricsPdf($report_data, 'I'); // I for Inline
    } elseif ($action === 'export_metrics_excel') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="Reporte_Metricas.xls"');
        
        echo "REPORTE DE METRICAS Y DESEMPENO\n";
        echo "Generado: " . date('Y-m-d H:i:s') . "\n\n";
        
        echo "TIEMPO PROMEDIO DE PAGO\n";
        echo $report_data['avg_payment_time'] . " segundos\n\n";
        
        echo "ESTADO DE SERVICIOS\n";
        echo "Estado\tCantidad\n";
        foreach ($status_data as $s) {
            echo ucfirst($s['service_status']) . "\t" . $s['count'] . "\n";
        }
        echo "\n";
        
        echo "INGRESOS MENSUALES (Ultimos 6 meses)\n";
        echo "Mes\tTotal (S/)\n";
        foreach ($income_data as $inc) {
            echo $inc['month'] . "\t" . number_format($inc['total'], 2) . "\n";
        }
        echo "\n";
        
        echo "TOP DEUDORES\n";
        echo "Cliente\tDeuda Total (S/)\n";
        foreach ($debtors_data as $d) {
            echo $d['fullname'] . "\t" . number_format($d['debt'], 2) . "\n";
        }
    } elseif ($action === 'export_payment_logs_pdf') {
        // Fetch detailed logs
        $q_logs = "SELECT pm.payment_timestamp, pm.duration_seconds, i.invoice_number, c.fullname 
                   FROM payment_metrics pm
                   JOIN invoices i ON pm.invoice_id = i.id
                   JOIN clients c ON i.client_id = c.id
                   ORDER BY pm.payment_timestamp DESC";
        $s_logs = $db->prepare($q_logs);
        $s_logs->execute();
        $logs_data = $s_logs->fetchAll(PDO::FETCH_ASSOC);

        if (empty($logs_data)) {
            // Fallback for empty data to show SOMETHING in the PDF
            $logs_data = [[
                'payment_timestamp' => date('Y-m-d H:i:s'),
                'duration_seconds' => 0,
                'invoice_number' => 'NO-DATA',
                'fullname' => 'System'
            ]];
        }

        include_once '../includes/PdfGenerator.php';
        $pdfGen = new PdfGenerator($db);
        $pdfGen->generatePaymentLogPdf($logs_data, 'I');
    }

} elseif ($action === 'export_debts') {
    // Keep existing logic for raw debts export if needed, or remove if replaced
    // For now, keeping it as it might be used elsewhere
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Reporte_Deudas.xls"');
    
    $q_all_debts = "SELECT c.fullname, c.dni_ruc, i.invoice_number, i.total_amount, i.due_date 
                    FROM invoices i 
                    JOIN clients c ON i.client_id = c.id 
                    WHERE i.status IN ('unpaid', 'overdue') 
                    ORDER BY i.due_date ASC";
    $s_all = $db->prepare($q_all_debts);
    $s_all->execute();
    $debts = $s_all->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Cliente\tDNI/RUC\tNro Factura\tMonto\tVencimiento\n";
    foreach ($debts as $row) {
        echo $row['fullname'] . "\t" . $row['dni_ruc'] . "\t" . $row['invoice_number'] . "\t" . $row['total_amount'] . "\t" . $row['due_date'] . "\n";
    }
}
?>
