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
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $offset = ($page - 1) * $limit;

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        $where = "";
        if (isset($_GET['dni'])) {
            $where = " WHERE c.dni_ruc = :dni AND i.status != 'paid'";
        } elseif (!empty($search)) {
            $where = " WHERE i.invoice_number LIKE :search 
                       OR c.fullname LIKE :search 
                       OR c.dni_ruc LIKE :search 
                       OR i.issue_date LIKE :search 
                       OR i.due_date LIKE :search 
                       OR i.total_amount LIKE :search 
                       OR i.status LIKE :search";
        }

        // 1. Get Totals and Count
        $countQuery = "SELECT 
                        COUNT(*) as total_records,
                        SUM(CASE WHEN i.status = 'paid' THEN i.total_amount ELSE 0 END) as total_paid,
                        SUM(CASE WHEN i.status = 'unpaid' THEN i.total_amount ELSE 0 END) as total_unpaid,
                        SUM(CASE WHEN i.status = 'overdue' THEN i.total_amount ELSE 0 END) as total_overdue
                       FROM invoices i 
                       JOIN clients c ON i.client_id = c.id
                       $where";
        
        $stmtCount = $db->prepare($countQuery);
        if (isset($_GET['dni'])) {
            $stmtCount->bindParam(":dni", $_GET['dni']);
        } elseif (!empty($search)) {
            $searchTerm = "%$search%";
            $stmtCount->bindParam(":search", $searchTerm);
        }
        $stmtCount->execute();
        $stats = $stmtCount->fetch(PDO::FETCH_ASSOC);
        
        // 2. Get Paginated Data
        $query = "SELECT i.*, c.fullname, c.dni_ruc 
                  FROM invoices i 
                  JOIN clients c ON i.client_id = c.id
                  $where";
        
        // Sort by Status (Overdue > Unpaid > Paid) then Date
        $query .= " ORDER BY 
                    CASE 
                        WHEN i.status = 'overdue' THEN 1 
                        WHEN i.status = 'unpaid' THEN 2 
                        WHEN i.status = 'paid' THEN 3 
                        ELSE 4 
                    END ASC, 
                    i.issue_date DESC, i.id DESC";
        
        $query .= " LIMIT :limit OFFSET :offset";
        
        $stmt = $db->prepare($query);
        
        if (isset($_GET['dni'])) {
            $stmt->bindParam(":dni", $_GET['dni']);
        } elseif (!empty($search)) {
            $searchTerm = "%$search%";
            $stmt->bindParam(":search", $searchTerm);
        }
        
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'data' => $invoices,
            'pagination' => [
                'current_page' => $page,
                'limit' => $limit,
                'total_records' => $stats['total_records'],
                'total_pages' => ceil($stats['total_records'] / $limit)
            ],
            'stats' => [
                'paid' => $stats['total_paid'] ?? 0,
                'unpaid' => $stats['total_unpaid'] ?? 0,
                'overdue' => $stats['total_overdue'] ?? 0
            ]
        ]);
        break;

    case 'POST':
        // Generate monthly invoices
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->month) && !empty($data->year)) {
            $month = $data->month; // 1-12
            $year = $data->year;
            
            // Find active services that don't have a MONTHLY invoice for this month/year
            $query = "SELECT s.id, s.client_id, s.plan_id, p.price, p.name as plan_name 
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
            
            // Filter by selected service IDs if provided
            if (isset($data->service_ids) && is_array($data->service_ids)) {
                $selected_ids = array_map('intval', $data->service_ids);
                $services_to_bill = array_filter($services_to_bill, function($service) use ($selected_ids) {
                    return in_array(intval($service['id']), $selected_ids);
                });
            }
            
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
