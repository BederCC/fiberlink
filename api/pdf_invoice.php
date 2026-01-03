<?php
require_once '../config.php';
require_once '../config/database.php';
require_once '../includes/PdfGenerator.php';

$database = new Database();
$conn = $database->getConnection();

if (!isset($_GET['id'])) {
    die('ID de factura no proporcionado');
}

$invoice_id = intval($_GET['id']);

$pdfGen = new PdfGenerator($conn);
$pdfGen->generateInvoicePdf($invoice_id, 'I');
?>
