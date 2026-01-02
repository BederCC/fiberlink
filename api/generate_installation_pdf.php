<?php
require_once '../config.php';
require_once '../config/database.php';
require_once '../includes/PdfGenerator.php';

$database = new Database();
$conn = $database->getConnection();

if (!isset($_GET['id'])) {
    die('ID de instalación no especificado.');
}

$installation_id = intval($_GET['id']);

$pdfGen = new PdfGenerator($conn);
$pdfGen->generateInstallationPdf($installation_id, 'I');
?>
