<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $query = "SELECT * FROM services WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $_GET['id']);
            $stmt->execute();
            $service = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($service);
        } else {
            $query = "SELECT s.*, c.first_name, c.last_name, p.name as plan_name, p.speed_mbps 
                      FROM services s 
                      JOIN clients c ON s.client_id = c.id 
                      JOIN plans p ON s.plan_id = p.id 
                      ORDER BY s.id DESC";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($services);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->client_id) && !empty($data->plan_id)) {
            $db->beginTransaction();
            
            try {
                // 1. Create Service
                $query = "INSERT INTO services (client_id, plan_id, ip_address, router_model, mac_address, installation_date, service_status) 
                          VALUES (:client_id, :plan_id, :ip_address, :router_model, :mac_address, :installation_date, 'active')";
                
                $stmt = $db->prepare($query);
                
                // Defaults
                $ip = $data->ip_address ?? '';
                $router = $data->router_model ?? '';
                $mac = $data->mac_address ?? '';
                $date = date('Y-m-d');
                
                $stmt->bindParam(":client_id", $data->client_id);
                $stmt->bindParam(":plan_id", $data->plan_id);
                $stmt->bindParam(":ip_address", $ip);
                $stmt->bindParam(":router_model", $router);
                $stmt->bindParam(":mac_address", $mac);
                $stmt->bindParam(":installation_date", $date);
                
                $stmt->execute();
                $service_id = $db->lastInsertId();
                
                // 2. Process Products (if any)
                $products_total = 0;
                if (!empty($data->products) && is_array($data->products)) {
                    foreach ($data->products as $prod) {
                        if (!empty($prod->id) && !empty($prod->quantity)) {
                            // Get product price
                            $q_price = "SELECT price, stock FROM products WHERE id = :id";
                            $s_price = $db->prepare($q_price);
                            $s_price->bindParam(":id", $prod->id);
                            $s_price->execute();
                            $product_info = $s_price->fetch(PDO::FETCH_ASSOC);
                            
                            if ($product_info) {
                                $price = $product_info['price'];
                                $subtotal = $price * $prod->quantity;
                                $products_total += $subtotal;
                                
                                // Insert installation detail
                                $q_detail = "INSERT INTO installation_details (service_id, product_id, quantity, price_at_moment) 
                                             VALUES (:service_id, :product_id, :quantity, :price)";
                                $s_detail = $db->prepare($q_detail);
                                $s_detail->bindParam(":service_id", $service_id);
                                $s_detail->bindParam(":product_id", $prod->id);
                                $s_detail->bindParam(":quantity", $prod->quantity);
                                $s_detail->bindParam(":price", $price);
                                $s_detail->execute();
                                
                                // Update stock
                                $q_stock = "UPDATE products SET stock = stock - :qty WHERE id = :id";
                                $s_stock = $db->prepare($q_stock);
                                $s_stock->bindParam(":qty", $prod->quantity);
                                $s_stock->bindParam(":id", $prod->id);
                                $s_stock->execute();
                            }
                        }
                    }
                }
                
                // 3. Generate Installation Invoice
                // Get Plan Price
                $q_plan = "SELECT price FROM plans WHERE id = :id";
                $s_plan = $db->prepare($q_plan);
                $s_plan->bindParam(":id", $data->plan_id);
                $s_plan->execute();
                $plan = $s_plan->fetch(PDO::FETCH_ASSOC);
                
                $installation_cost = isset($data->installation_cost) ? $data->installation_cost : 0;
                $total_invoice = $installation_cost + $products_total;
                
                // If user wants to include first month, add plan price
                if (!empty($data->include_first_month)) {
                    $total_invoice += $plan['price'];
                }
                
                if ($total_invoice > 0) {
                    $invoice_number = 'INS-' . str_pad($service_id, 6, '0', STR_PAD_LEFT);
                    $due_date = date('Y-m-d', strtotime('+7 days')); // 7 days to pay installation
                    
                    $q_inv = "INSERT INTO invoices (client_id, invoice_number, issue_date, due_date, total_amount, status, type) 
                              VALUES (:client_id, :invoice_number, :issue_date, :due_date, :total_amount, 'unpaid', 'installation')";
                    $s_inv = $db->prepare($q_inv);
                    $s_inv->bindParam(":client_id", $data->client_id);
                    $s_inv->bindParam(":invoice_number", $invoice_number);
                    $s_inv->bindParam(":issue_date", $date);
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
                    if (!empty($data->products) && is_array($data->products)) {
                         // We already have the details in installation_details, but let's add a summary item or individual items to invoice
                         // For simplicity, let's add one item per product type
                         foreach ($data->products as $prod) {
                            if (!empty($prod->id) && !empty($prod->quantity)) {
                                $q_pname = "SELECT name, price FROM products WHERE id = :id";
                                $s_pname = $db->prepare($q_pname);
                                $s_pname->bindParam(":id", $prod->id);
                                $s_pname->execute();
                                $p_info = $s_pname->fetch(PDO::FETCH_ASSOC);
                                
                                if ($p_info) {
                                    $desc = $p_info['name'] . " (x" . $prod->quantity . ")";
                                    $amt = $p_info['price'] * $prod->quantity;
                                    
                                    $q_item = "INSERT INTO invoice_items (invoice_id, description, amount) VALUES (:inv_id, :desc, :amount)";
                                    $s_item = $db->prepare($q_item);
                                    $s_item->bindParam(":inv_id", $invoice_id);
                                    $s_item->bindParam(":desc", $desc);
                                    $s_item->bindParam(":amount", $amt);
                                    $s_item->execute();
                                }
                            }
                         }
                    }
                    
                    // 3. First Month
                    if (!empty($data->include_first_month)) {
                        $desc = "Primer Mes de Servicio";
                        $q_item = "INSERT INTO invoice_items (invoice_id, description, amount) VALUES (:inv_id, :desc, :amount)";
                        $s_item = $db->prepare($q_item);
                        $s_item->bindParam(":inv_id", $invoice_id);
                        $s_item->bindParam(":desc", $desc);
                        $s_item->bindParam(":amount", $plan['price']);
                        $s_item->execute();
                    }
                }
                
                $db->commit();
                http_response_code(201);
                echo json_encode(array("message" => "Servicio creado e instalación facturada exitosamente."));
                
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

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $query = "UPDATE services SET 
                        client_id = :client_id, 
                        plan_id = :plan_id, 
                        ip_address = :ip_address, 
                        router_model = :router_model, 
                        mac_address = :mac_address 
                      WHERE id = :id";
            
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(":client_id", $data->client_id);
            $stmt->bindParam(":plan_id", $data->plan_id);
            $stmt->bindParam(":ip_address", $data->ip_address);
            $stmt->bindParam(":router_model", $data->router_model);
            $stmt->bindParam(":mac_address", $data->mac_address);
            $stmt->bindParam(":id", $data->id);
            
            if($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Servicio actualizado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo actualizar el servicio."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
        }
        break;
}
?>
