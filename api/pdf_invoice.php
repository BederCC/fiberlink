<?php
require_once('../vendor/autoload.php');
include_once '../config/database.php';

if (!isset($_GET['id'])) {
    die('ID de factura no proporcionado');
}

$invoice_id = $_GET['id'];

$database = new Database();
$db = $database->getConnection();

// 1. Get Invoice & Client Details
$query = "SELECT i.*, c.first_name, c.last_name, c.dni_ruc, c.email, c.address, c.phone 
          FROM invoices i 
          JOIN clients c ON i.client_id = c.id 
          WHERE i.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(":id", $invoice_id);
$stmt->execute();
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice) {
    die('Factura no encontrada');
}

// 2. Get Invoice Items
$q_items = "SELECT * FROM invoice_items WHERE invoice_id = :id";
$s_items = $db->prepare($q_items);
$s_items->bindParam(":id", $invoice_id);
$s_items->execute();
$items = $s_items->fetchAll(PDO::FETCH_ASSOC);

// 3. Create PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Meta info
$pdf->SetCreator('FiberLink System');
$pdf->SetAuthor('FiberLink');
$pdf->SetTitle('Factura ' . $invoice['invoice_number']);

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Margins
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, 15);

$pdf->AddPage();

// --- CONTENT ---

// Company Logo/Header
$html = '
<table border="0" cellpadding="5">
    <tr>
        <td width="60%">
            <h1 style="color: #4f46e5; font-size: 24px;">FiberLink</h1>
            <p style="font-size: 10px; color: #666;">
                Av. Principal 123, Ciudad<br>
                RUC: 20123456789<br>
                Tel: (01) 123-4567<br>
                Email: contacto@fiberlink.com
            </p>
        </td>
        <td width="40%" style="text-align: right;">
            <h2 style="color: #333;">FACTURA</h2>
            <p style="font-size: 12px;"><strong>N°: ' . $invoice['invoice_number'] . '</strong></p>
            <p style="font-size: 10px;">
                Fecha Emisión: ' . date('d/m/Y', strtotime($invoice['issue_date'])) . '<br>
                Fecha Vencimiento: ' . date('d/m/Y', strtotime($invoice['due_date'])) . '
            </p>
            <p style="font-size: 12px; font-weight: bold; color: ' . ($invoice['status'] == 'paid' ? '#10b981' : '#ef4444') . ';">
                ESTADO: ' . strtoupper($invoice['status']) . '
            </p>
        </td>
    </tr>
</table>
<hr style="color: #ddd;">
';

// Client Info
$html .= '
<table border="0" cellpadding="5">
    <tr>
        <td width="100%">
            <h3 style="font-size: 12px; color: #333;">CLIENTE</h3>
            <p style="font-size: 10px; color: #555;">
                <strong>Nombre:</strong> ' . $invoice['first_name'] . ' ' . $invoice['last_name'] . '<br>
                <strong>DNI/RUC:</strong> ' . $invoice['dni_ruc'] . '<br>
                <strong>Dirección:</strong> ' . $invoice['address'] . '<br>
                <strong>Email:</strong> ' . $invoice['email'] . '
            </p>
        </td>
    </tr>
</table>
<br>
';

// Items Table
$html .= '
<table border="1" cellpadding="6" cellspacing="0" style="border-color: #eee;">
    <tr style="background-color: #f8fafc; color: #333; font-weight: bold;">
        <td width="70%">Descripción</td>
        <td width="30%" style="text-align: right;">Monto</td>
    </tr>
';

foreach ($items as $item) {
    $html .= '
    <tr>
        <td>' . $item['description'] . '</td>
        <td style="text-align: right;">S/ ' . number_format($item['amount'], 2) . '</td>
    </tr>
    ';
}

// Totals
$html .= '
    <tr style="background-color: #f8fafc; font-weight: bold;">
        <td style="text-align: right;">TOTAL</td>
        <td style="text-align: right; color: #4f46e5;">S/ ' . number_format($invoice['total_amount'], 2) . '</td>
    </tr>
</table>
';

// Footer
$html .= '
<br><br><br>
<p style="text-align: center; font-size: 9px; color: #999;">
    Gracias por su preferencia.<br>
    Este documento es un comprobante generado electrónicamente.
</p>
';

$pdf->writeHTML($html, true, false, true, false, '');

// Output
$pdf->Output('Factura_' . $invoice['invoice_number'] . '.pdf', 'I');
?>
