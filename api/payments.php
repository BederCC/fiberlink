<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->invoice_id) && !empty($data->amount)) {
    
    // 1. Register Payment
    $query = "INSERT INTO payments (invoice_id, amount, payment_method, transaction_id, notes, created_by) 
              VALUES (:invoice_id, :amount, :payment_method, :transaction_id, :notes, :created_by)";
    
    $stmt = $db->prepare($query);
    
    // Defaults
    $method = $data->payment_method ?? 'cash';
    $txn_id = $data->transaction_id ?? '';
    $notes = $data->notes ?? '';
    $user_id = 1; // TODO: Get from token/session
    
    $stmt->bindParam(":invoice_id", $data->invoice_id);
    $stmt->bindParam(":amount", $data->amount);
    $stmt->bindParam(":payment_method", $method);
    $stmt->bindParam(":transaction_id", $txn_id);
    $stmt->bindParam(":notes", $notes);
    $stmt->bindParam(":created_by", $user_id);
    
    if($stmt->execute()) {
        
        // 2. Check if invoice is fully paid
        // Get total invoice amount
        $q_inv = "SELECT total_amount FROM invoices WHERE id = :id";
        $s_inv = $db->prepare($q_inv);
        $s_inv->bindParam(":id", $data->invoice_id);
        $s_inv->execute();
        $invoice = $s_inv->fetch(PDO::FETCH_ASSOC);
        
        // Get total paid so far
        $q_paid = "SELECT SUM(amount) as total_paid FROM payments WHERE invoice_id = :id";
        $s_paid = $db->prepare($q_paid);
        $s_paid->bindParam(":id", $data->invoice_id);
        $s_paid->execute();
        $paid = $s_paid->fetch(PDO::FETCH_ASSOC);
        
        if($paid['total_paid'] >= $invoice['total_amount']) {
            // Update status to paid
            $q_update = "UPDATE invoices SET status = 'paid' WHERE id = :id";
            $s_update = $db->prepare($q_update);
            $s_update->bindParam(":id", $data->invoice_id);
            $s_update->execute();
        }
        
        // 3. Send Email Receipt
        include_once '../includes/Mailer.php';
        include_once '../includes/PdfGenerator.php';
        
        // Get Client & Invoice Details
        $q_details = "SELECT c.email, c.fullname, i.invoice_number 
                      FROM invoices i 
                      JOIN clients c ON i.client_id = c.id 
                      WHERE i.id = :id";
        $s_details = $db->prepare($q_details);
        $s_details->bindParam(":id", $data->invoice_id);
        $s_details->execute();
        $details = $s_details->fetch(PDO::FETCH_ASSOC);
        
        if($details && !empty($details['email'])) {
            // Generate PDF
            $pdfGen = new PdfGenerator($db);
            $pdfContent = $pdfGen->generateInvoicePdf($data->invoice_id, 'S'); // 'S' returns string
            $filename = 'Recibo_' . $details['invoice_number'] . '.pdf';

            $mailer = new Mailer();
            $paymentData = [
                'invoice_number' => $details['invoice_number'],
                'amount' => number_format($data->amount, 2),
                'date' => date('d/m/Y H:i'),
                'method' => ucfirst($method),
                'transaction_id' => $txn_id
            ];
            
            $mailer->sendPaymentReceipt(
                $details['email'], 
                $details['fullname'], 
                $paymentData,
                $pdfContent,
                $filename
            );
        }
        
        // 4. Record Payment Metric (Time to Payment)
        if (!empty($data->search_timestamp)) {
            $searchTime = $data->search_timestamp / 1000; // Convert JS ms to PHP seconds
            $payTime = time();
            $duration = $payTime - $searchTime;
            
            if ($duration > 0) {
                $q_metric = "INSERT INTO payment_metrics (invoice_id, search_timestamp, payment_timestamp, duration_seconds) 
                             VALUES (:invoice_id, FROM_UNIXTIME(:search_ts), FROM_UNIXTIME(:pay_ts), :duration)";
                $s_metric = $db->prepare($q_metric);
                $s_metric->bindParam(":invoice_id", $data->invoice_id);
                $s_metric->bindParam(":search_ts", $searchTime);
                $s_metric->bindParam(":pay_ts", $payTime);
                $s_metric->bindParam(":duration", $duration);
                $s_metric->execute();
            }
        }
        
        http_response_code(201);
        echo json_encode(array("message" => "Pago registrado y recibo enviado."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "No se pudo registrar el pago."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Datos incompletos."));
}
?>
