<?php
require_once '../config.php';
require_once '../config/database.php';

// Check if user is logged in (optional but good practice)
session_start();

$database = new Database();
$db = $database->getConnection();

$format = $_GET['format'] ?? 'csv';

// Fetch all invoices (filtered by search query if provided)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where = "";
if (!empty($search)) {
    $where = " WHERE i.invoice_number LIKE :search 
               OR c.fullname LIKE :search 
               OR c.dni_ruc LIKE :search 
               OR i.issue_date LIKE :search 
               OR i.due_date LIKE :search 
               OR i.total_amount LIKE :search 
               OR i.status LIKE :search";
}

$query = "SELECT i.invoice_number, c.fullname, c.dni_ruc, i.issue_date, i.due_date, i.total_amount, i.status, i.type 
          FROM invoices i 
          JOIN clients c ON i.client_id = c.id 
          $where
          ORDER BY i.issue_date DESC, i.id DESC";

$stmt = $db->prepare($query);

if (!empty($search)) {
    $searchTerm = "%$search%";
    $stmt->bindParam(":search", $searchTerm);
}

$stmt->execute();
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="reporte_facturas_' . date('Ymd_His') . '.csv"');
    
    $output = fopen('php://output', 'w');
    // UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, ['Nro Factura', 'Cliente', 'DNI/RUC', 'Fecha Emisión', 'Fecha Vencimiento', 'Monto (S/)', 'Estado', 'Tipo']);
    
    foreach ($invoices as $inv) {
        $status_text = '';
        switch ($inv['status']) {
            case 'paid': $status_text = 'Pagado'; break;
            case 'unpaid': $status_text = 'Pendiente'; break;
            case 'overdue': $status_text = 'Vencido'; break;
            case 'cancelled': $status_text = 'Cancelado'; break;
            default: $status_text = ucfirst($inv['status']);
        }
        
        $type_text = '';
        switch ($inv['type']) {
            case 'monthly': $type_text = 'Mensual'; break;
            case 'installation': $type_text = 'Instalación'; break;
            case 'product_sale': $type_text = 'Venta Producto'; break;
            default: $type_text = ucfirst($inv['type']);
        }
        
        fputcsv($output, [
            $inv['invoice_number'],
            $inv['fullname'],
            $inv['dni_ruc'],
            $inv['issue_date'],
            $inv['due_date'],
            number_format($inv['total_amount'], 2, '.', ''),
            $status_text,
            $type_text
        ]);
    }
    fclose($output);
    exit;

} elseif ($format === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="reporte_facturas_' . date('Ymd_His') . '.xls"');
    
    // Generate styled HTML that Excel parses perfectly
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
    echo '<style>
        table { border-collapse: collapse; font-family: Calibri, sans-serif; width: 100%; }
        th { background-color: #4f46e5; color: #ffffff; font-weight: bold; padding: 12px 10px; border: 1px solid #cbd5e1; font-size: 11pt; }
        td { padding: 10px 8px; border: 1px solid #e2e8f0; font-size: 10pt; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .status-paid { color: #16a34a; font-weight: bold; }
        .status-unpaid { color: #d97706; font-weight: bold; }
        .status-overdue { color: #dc2626; font-weight: bold; }
        .status-cancelled { color: #64748b; }
        .header-title { font-size: 18pt; font-weight: bold; color: #1e1b4b; margin-bottom: 5px; }
        .header-subtitle { font-size: 10pt; color: #475569; margin-bottom: 20px; }
        .summary-table { border: none; margin-bottom: 30px; width: 300px; }
        .summary-table td { border: none; padding: 5px; }
    </style></head>';
    echo '<body>';
    
    echo '<div class="header-title">Reporte de Facturación y Pagos - FiberLink</div>';
    echo '<div class="header-subtitle">Generado el: ' . date('d/m/Y H:i:s') . '</div><br>';
    
    // Calculate summaries
    $total_count = count($invoices);
    $total_amount = 0;
    $total_paid = 0;
    $total_unpaid = 0;
    foreach ($invoices as $inv) {
        $amount = floatval($inv['total_amount']);
        $total_amount += $amount;
        if ($inv['status'] === 'paid') {
            $total_paid += $amount;
        } else {
            $total_unpaid += $amount;
        }
    }
    
    echo '<table class="summary-table">';
    echo '<tr><td class="font-bold">Total Facturas:</td><td>' . $total_count . '</td></tr>';
    echo '<tr><td class="font-bold">Total Facturado:</td><td class="font-bold">S/ ' . number_format($total_amount, 2) . '</td></tr>';
    echo '<tr><td class="font-bold">Total Cobrado:</td><td class="font-bold" style="color: #16a34a;">S/ ' . number_format($total_paid, 2) . '</td></tr>';
    echo '<tr><td class="font-bold">Total Pendiente/Vencido:</td><td class="font-bold" style="color: #dc2626;">S/ ' . number_format($total_unpaid, 2) . '</td></tr>';
    echo '</table><br>';
    
    echo '<table>';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Nro Factura</th>';
    echo '<th>Cliente</th>';
    echo '<th>DNI/RUC</th>';
    echo '<th>Fecha Emisión</th>';
    echo '<th>Fecha Vencimiento</th>';
    echo '<th>Monto (S/)</th>';
    echo '<th>Estado</th>';
    echo '<th>Tipo</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($invoices as $inv) {
        $status_class = '';
        $status_text = '';
        switch ($inv['status']) {
            case 'paid': $status_class = 'status-paid'; $status_text = 'Pagado'; break;
            case 'unpaid': $status_class = 'status-unpaid'; $status_text = 'Pendiente'; break;
            case 'overdue': $status_class = 'status-overdue'; $status_text = 'Vencido'; break;
            case 'cancelled': $status_class = 'status-cancelled'; $status_text = 'Cancelado'; break;
            default: $status_text = ucfirst($inv['status']);
        }
        
        $type_text = '';
        switch ($inv['type']) {
            case 'monthly': $type_text = 'Mensual'; break;
            case 'installation': $type_text = 'Instalación'; break;
            case 'product_sale': $type_text = 'Venta Producto'; break;
            default: $type_text = ucfirst($inv['type']);
        }
        
        echo '<tr>';
        echo '<td class="font-bold">' . $inv['invoice_number'] . '</td>';
        echo '<td>' . htmlspecialchars($inv['fullname']) . '</td>';
        echo '<td>' . $inv['dni_ruc'] . '</td>';
        echo '<td class="text-center">' . date('d/m/Y', strtotime($inv['issue_date'])) . '</td>';
        echo '<td class="text-center">' . date('d/m/Y', strtotime($inv['due_date'])) . '</td>';
        echo '<td class="text-right font-bold">S/ ' . number_format($inv['total_amount'], 2) . '</td>';
        echo '<td class="text-center"><span class="' . $status_class . '">' . $status_text . '</span></td>';
        echo '<td class="text-center">' . $type_text . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</body></html>';
    exit;

} elseif ($format === 'pdf') {
    include_once '../includes/PdfGenerator.php';
    $pdfGen = new PdfGenerator($db);
    $pdfGen->generateInvoicesReportPdf($invoices, 'I');
    exit;
}
