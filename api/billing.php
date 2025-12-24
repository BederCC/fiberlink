<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Check for preview action
        if (isset($_GET['action']) && $_GET['action'] === 'preview') {
            $month = $_GET['month'];
            $year = $_GET['year'];
            
            $query = "SELECT s.id, c.fullname, c.dni_ruc, p.name as plan_name, p.price 
                      FROM services s 
                      JOIN clients c ON s.client_id = c.id 
                      JOIN plans p ON s.plan_id = p.id 
                      WHERE s.service_status = 'active' 
                      AND s.client_id NOT IN (
                          SELECT client_id FROM invoices 
                          WHERE MONTH(issue_date) = :month 
                          AND YEAR(issue_date) = :year 
                          AND type = 'monthly'
                      )";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(":month", $month);
            $stmt->bindParam(":year", $year);
            $stmt->execute();
            $preview = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($preview);
            break;
        }

        // Get single invoice details with items
        if (isset($_GET['invoice_id'])) {
            $invoice_id = $_GET['invoice_id'];
            
            $q_inv = "SELECT i.*, c.fullname, c.dni_ruc, c.address 
                      FROM invoices i 
                      JOIN clients c ON i.client_id = c.id 
                      WHERE i.id = :id";
            $s_inv = $db->prepare($q_inv);
            $s_inv->bindParam(":id", $invoice_id);
            $s_inv->execute();
            $invoice = $s_inv->fetch(PDO::FETCH_ASSOC);
            
            if($invoice) {
                // Get Items
                $q_items = "SELECT * FROM invoice_items WHERE invoice_id = :id";
                $s_items = $db->prepare($q_items);
                $s_items->bindParam(":id", $invoice_id);
                $s_items->execute();
                $invoice['items'] = $s_items->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode($invoice);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Factura no encontrada."));
            }
            break;
        }

        // List invoices
        $query = "SELECT i.*, c.fullname, c.dni_ruc 
                  FROM invoices i 
                  JOIN clients c ON i.client_id = c.id";
        
        if(isset($_GET['dni'])) {
            $query .= " WHERE c.dni_ruc = :dni AND i.status != 'paid'";
        }
        
        $query .= " ORDER BY i.issue_date DESC, i.id DESC";
        
        $stmt = $db->prepare($query);
        
        if(isset($_GET['dni'])) {
            $stmt->bindParam(":dni", $_GET['dni']);
        }
        
        $stmt->execute();
        $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($invoices);
        break;

    case 'POST':
        // Generate monthly invoices
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->month) && !empty($data->year)) {
            $month = $data->month; // 1-12
            $year = $data->year;
            
            // Find active services that don't have a MONTHLY invoice for this month/year
            $query = "SELECT s.client_id, s.plan_id, p.price, p.name as plan_name 
                      FROM services s 
                      JOIN plans p ON s.plan_id = p.id 
                      WHERE s.service_status = 'active' 
                      AND s.client_id NOT IN (
                          SELECT client_id FROM invoices 
                          WHERE MONTH(issue_date) = :month 
                          AND YEAR(issue_date) = :year
                          AND type = 'monthly'
                      )";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(":month", $month);
            $stmt->bindParam(":year", $year);
            $stmt->execute();
            
            $services_to_bill = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $count = 0;
            
            foreach($services_to_bill as $service) {
                // Create Invoice
                $invoice_number = "INV-" . $year . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
                $issue_date = "$year-$month-01";
                // If generating for current month, issue date could be today, but usually 1st of month is standard for recurring.
                // Let's keep it 1st of month or today if we prefer. 
                // If we run this on 24th Dec for Dec, issue date 2025-12-01 is fine.
                
                $due_date = date('Y-m-d', strtotime("$issue_date + 15 days")); 
                
                $insert_invoice = "INSERT INTO invoices (client_id, invoice_number, issue_date, due_date, total_amount, status, type) 
                                   VALUES (:client_id, :invoice_number, :issue_date, :due_date, :total_amount, 'unpaid', 'monthly')";
                $stmt_inv = $db->prepare($insert_invoice);
                $stmt_inv->bindParam(":client_id", $service['client_id']);
                $stmt_inv->bindParam(":invoice_number", $invoice_number);
                $stmt_inv->bindParam(":issue_date", $issue_date);
                $stmt_inv->bindParam(":due_date", $due_date);
                $stmt_inv->bindParam(":total_amount", $service['price']);
                
                if($stmt_inv->execute()) {
                    $invoice_id = $db->lastInsertId();
                    
                    // Create Invoice Item
                    $description = "Servicio Internet - " . $service['plan_name'] . " - " . date('F Y', strtotime($issue_date));
                    $insert_item = "INSERT INTO invoice_items (invoice_id, description, amount) VALUES (:invoice_id, :description, :amount)";
                    $stmt_item = $db->prepare($insert_item);
                    $stmt_item->bindParam(":invoice_id", $invoice_id);
                    $stmt_item->bindParam(":description", $description);
                    $stmt_item->bindParam(":amount", $service['price']);
                    $stmt_item->execute();
                    
                    $count++;
                }
            }
            
            http_response_code(201);
            echo json_encode(array("message" => "Proceso completado.", "generated_count" => $count));
            
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
        }
        break;
}
?>
