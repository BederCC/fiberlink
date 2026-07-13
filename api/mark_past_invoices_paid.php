<?php
// api/mark_past_invoices_paid.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode([
        "status" => "error",
        "message" => "Error: No se pudo conectar a la base de datos."
    ]);
    exit;
}

try {
    // 1. Obtener todas las facturas no pagadas previas o iguales al 16 de Junio de 2026
    $cutoff_date = '2026-06-16';
    
    $q_inv = "SELECT id, client_id, total_amount, invoice_number 
              FROM invoices 
              WHERE issue_date <= :cutoff AND status != 'paid'";
    $stmt = $db->prepare($q_inv);
    $stmt->bindParam(":cutoff", $cutoff_date);
    $stmt->execute();
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($invoices)) {
        echo json_encode([
            "status" => "success",
            "message" => "No se encontraron facturas pendientes anteriores o iguales al " . date('d/m/Y', strtotime($cutoff_date)) . "."
        ]);
        exit;
    }

    $db->beginTransaction();
    
    $processed = 0;
    $payments_recorded = 0;
    $reactivated_services = 0;

    foreach ($invoices as $invoice) {
        $inv_id = $invoice['id'];
        $client_id = $invoice['client_id'];
        $total_amount = floatval($invoice['total_amount']);

        // A. Obtener el total pagado hasta ahora para esta factura (por si tenía pagos parciales)
        $q_paid = "SELECT SUM(amount) as total_paid FROM payments WHERE invoice_id = :id";
        $stmt_paid = $db->prepare($q_paid);
        $stmt_paid->bindParam(":id", $inv_id);
        $stmt_paid->execute();
        $paid_row = $stmt_paid->fetch(PDO::FETCH_ASSOC);
        $total_paid = $paid_row ? floatval($paid_row['total_paid']) : 0;

        $remaining_balance = $total_amount - $total_paid;

        if ($remaining_balance > 0) {
            // B. Registrar el pago por el saldo restante en la tabla de pagos
            $q_insert_payment = "INSERT INTO payments (invoice_id, amount, payment_method, transaction_id, notes, created_by) 
                                 VALUES (:invoice_id, :amount, 'cash', 'BULK_AUTO_PAY', 'Pago masivo automático anterior a Junio', 1)";
            $stmt_ins = $db->prepare($q_insert_payment);
            $stmt_ins->bindParam(":invoice_id", $inv_id);
            $stmt_ins->bindParam(":amount", $remaining_balance);
            $stmt_ins->execute();
            $payments_recorded++;
        }

        // C. Marcar la factura como pagada en la tabla de facturas
        $q_upd_status = "UPDATE invoices SET status = 'paid' WHERE id = :id";
        $stmt_upd = $db->prepare($q_upd_status);
        $stmt_upd->bindParam(":id", $inv_id);
        $stmt_upd->execute();
        $processed++;

        // D. Reactivar los servicios suspendidos del cliente
        $q_reactivate = "UPDATE services SET service_status = 'active' WHERE client_id = :cid AND service_status = 'suspended'";
        $stmt_react = $db->prepare($q_reactivate);
        $stmt_react->bindParam(":cid", $client_id);
        $stmt_react->execute();
        $reactivated_services += $stmt_react->rowCount();
    }

    if (function_exists('writeActivityLog')) {
        writeActivityLog("Bulk marked $processed past invoices as paid (prior to $cutoff_date).");
    }

    $db->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Procesamiento completado con éxito.",
        "details" => [
            "facturas_marcadas_como_pagadas" => $processed,
            "pagos_registrados_en_historial" => $payments_recorded,
            "servicios_reactivados" => $reactivated_services
        ]
    ]);

} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        "status" => "error",
        "message" => "Error al ejecutar el script de pago masivo: " . $e->getMessage()
    ]);
}
?>
