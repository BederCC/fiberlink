<?php
// api/installations.php

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
        // List pending and in_progress installations
        $status_filter = isset($_GET['status']) ? $_GET['status'] : 'active';

        $query = "SELECT i.*, c.fullname, c.address, c.phone, p.name as plan_name, s.ip_address, s.router_model, s.mac_address 
                  FROM installations i 
                  JOIN services s ON i.service_id = s.id 
                  JOIN clients c ON i.client_id = c.id 
                  JOIN plans p ON s.plan_id = p.id";
        
        if ($status_filter === 'all') {
            // No WHERE clause for status
             $query .= " ORDER BY i.created_at DESC"; // Newest first for history
        } elseif ($status_filter === 'completed') {
            $query .= " WHERE i.status = 'completed' ORDER BY i.completed_date DESC";
        } else {
            // Default behavior (pending/in_progress)
            $query .= " WHERE i.status IN ('pending', 'in_progress') ORDER BY i.created_at ASC";
        }
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        $installations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($installations);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $db->beginTransaction();
            try {
                $action = $data->action ?? 'complete'; // Default to complete for backward compatibility if needed

                if ($action === 'start') {
                    // Start Installation
                    $q_inst = "UPDATE installations SET status = 'in_progress' WHERE id = :id";
                    $s_inst = $db->prepare($q_inst);
                    $s_inst->bindParam(":id", $data->id);
                    $s_inst->execute();
                    
                    // Update Service Status to in_progress
                    $q_serv = "UPDATE services s JOIN installations i ON i.service_id = s.id SET s.service_status = 'in_progress' WHERE i.id = :id";
                    $s_serv = $db->prepare($q_serv);
                    $s_serv->bindParam(":id", $data->id);
                    $s_serv->execute();
                    
                    writeActivityLog("Started installation ID: " . $data->id);
                    $db->commit();
                    http_response_code(200);
                    echo json_encode(array("message" => "Instalación iniciada."));

                } elseif ($action === 'complete') {
                    // Complete Installation (Existing Logic)
                    // 1. Update Installation
                    $q_inst = "UPDATE installations SET status = 'completed', completed_date = NOW(), notes = :notes WHERE id = :id";
                    $s_inst = $db->prepare($q_inst);
                    $notes = $data->notes ?? 'Instalación completada exitosamente.';
                    $s_inst->bindParam(":notes", $notes);
                    $s_inst->bindParam(":id", $data->id);
                    $s_inst->execute();
                    
                    // 2. Get Service ID
                    $q_get = "SELECT service_id FROM installations WHERE id = :id";
                    $s_get = $db->prepare($q_get);
                    $s_get->bindParam(":id", $data->id);
                    $s_get->execute();
                    $row = $s_get->fetch(PDO::FETCH_ASSOC);
                    
                    if($row) {
                        // 3. Activate Service
                        $q_serv = "UPDATE services SET service_status = 'active', installation_date = CURDATE() WHERE id = :sid";
                        $s_serv = $db->prepare($q_serv);
                        $s_serv->bindParam(":sid", $row['service_id']);
                        $s_serv->execute();

                        // 4. Generate Installation Invoice
                        // Get Installation Details (Cost, First Month, Products)
                        $q_details = "SELECT * FROM installations WHERE id = :id";
                        $s_details = $db->prepare($q_details);
                        $s_details->bindParam(":id", $data->id);
                        $s_details->execute();
                        $inst_details = $s_details->fetch(PDO::FETCH_ASSOC);

                        $service_id = $inst_details['service_id'];
                        $client_id = $inst_details['client_id'];
                        $installation_cost = $inst_details['installation_cost'];
                        $include_first_month = $inst_details['include_first_month'];

                        // Get Plan Price
                        $q_plan = "SELECT p.price FROM services s JOIN plans p ON s.plan_id = p.id WHERE s.id = :sid";
                        $s_plan = $db->prepare($q_plan);
                        $s_plan->bindParam(":sid", $service_id);
                        $s_plan->execute();
                        $plan = $s_plan->fetch(PDO::FETCH_ASSOC);

                        // Calculate Products Total
                        $q_prods = "SELECT SUM(price_at_moment * quantity) as total FROM installation_details WHERE service_id = :sid";
                        $s_prods = $db->prepare($q_prods);
                        $s_prods->bindParam(":sid", $service_id);
                        $s_prods->execute();
                        $products_total = $s_prods->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

                        $total_invoice = $installation_cost + $products_total;
                        if ($include_first_month) {
                            $total_invoice += $plan['price'];
                        }

                        if ($total_invoice > 0) {
                            $invoice_number = 'INS-' . str_pad($service_id, 6, '0', STR_PAD_LEFT);
                            $issue_date = date('Y-m-d');
                            $due_date = date('Y-m-d', strtotime('+7 days'));

                            $q_inv = "INSERT INTO invoices (client_id, invoice_number, issue_date, due_date, total_amount, status, type) 
                                      VALUES (:client_id, :invoice_number, :issue_date, :due_date, :total_amount, 'unpaid', 'installation')";
                            $s_inv = $db->prepare($q_inv);
                            $s_inv->bindParam(":client_id", $client_id);
                            $s_inv->bindParam(":invoice_number", $invoice_number);
                            $s_inv->bindParam(":issue_date", $issue_date);
                            $s_inv->bindParam(":due_date", $due_date);
                            $s_inv->bindParam(":total_amount", $total_invoice);
                            $s_inv->execute();
                            $invoice_id = $db->lastInsertId();

                            // Add Invoice Items
                            // 1. Installation Cost
                            if ($installation_cost > 0) {
                                $q_item = "INSERT INTO invoice_items (invoice_id, description, amount) VALUES (:inv_id, 'Costo de Instalación', :amount)";
                                $s_item = $db->prepare($q_item);
                                $s_item->bindParam(":inv_id", $invoice_id);
                                $s_item->bindParam(":amount", $installation_cost);
                                $s_item->execute();
                            }

                            // 2. Products
                            $q_prod_items = "SELECT p.name, d.quantity, d.price_at_moment FROM installation_details d JOIN products p ON d.product_id = p.id WHERE d.service_id = :sid";
                            $s_prod_items = $db->prepare($q_prod_items);
                            $s_prod_items->bindParam(":sid", $service_id);
                            $s_prod_items->execute();
                            $prod_items = $s_prod_items->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($prod_items as $item) {
                                $desc = $item['name'] . " (x" . $item['quantity'] . ")";
                                $amt = $item['price_at_moment'] * $item['quantity'];
                                $q_i = "INSERT INTO invoice_items (invoice_id, description, amount) VALUES (:inv_id, :desc, :amount)";
                                $s_i = $db->prepare($q_i);
                                $s_i->bindParam(":inv_id", $invoice_id);
                                $s_i->bindParam(":desc", $desc);
                                $s_i->bindParam(":amount", $amt);
                                $s_i->execute();
                            }

                            // 3. First Month
                            if ($include_first_month) {
                                $desc = "Primer Mes de Servicio";
                                $q_item = "INSERT INTO invoice_items (invoice_id, description, amount) VALUES (:inv_id, :desc, :amount)";
                                $s_item = $db->prepare($q_item);
                                $s_item->bindParam(":inv_id", $invoice_id);
                                $s_item->bindParam(":desc", $desc);
                                $s_item->bindParam(":amount", $plan['price']);
                                $s_item->execute();
                            }
                        }
                    }
                    
                    writeActivityLog("Completed installation ID: " . $data->id);
                    $db->commit();

                    // --- Post-Commit Actions (Email) ---
                    $emailSent = false;
                    try {
                        // 1. Fetch Client Email and Name
                        $q_client = "SELECT c.email, c.fullname FROM installations i JOIN clients c ON i.client_id = c.id WHERE i.id = :id";
                        $s_client = $db->prepare($q_client);
                        $s_client->bindParam(":id", $data->id);
                        $s_client->execute();
                        $clientData = $s_client->fetch(PDO::FETCH_ASSOC);

                        if ($clientData && !empty($clientData['email'])) {
                            // 2. Generate PDF
                            include_once '../includes/PdfGenerator.php';
                            $pdfGen = new PdfGenerator($db);
                            $pdfContent = $pdfGen->generateInstallationPdf($data->id, 'S'); // 'S' returns string

                            // 3. Send Email
                            include_once '../includes/Mailer.php';
                            $mailer = new Mailer();
                            $filename = 'Hoja_Instalacion_' . $data->id . '.pdf';
                            
                            if ($mailer->sendInstallationSheet($clientData['email'], $clientData['fullname'], $pdfContent, $filename)) {
                                $emailSent = true;
                            }
                        }
                    } catch (Exception $e) {
                        error_log("Error sending installation email: " . $e->getMessage());
                        // Don't fail the request if email fails, just log it
                    }

                    http_response_code(200);
                    $msg = "Instalación completada y servicio activado.";
                    if ($emailSent) {
                        $msg .= " Se envió la hoja de instalación al correo del cliente.";
                    }
                    echo json_encode(array("message" => $msg));
                }
                
            } catch (Exception $e) {
                $db->rollBack();
                http_response_code(503);
                echo json_encode(array("message" => "Error: " . $e->getMessage()));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
        }
        break;
}
?>
