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
            $query = "SELECT s.*, c.fullname, p.name as plan_name, 
                             i.installation_cost, i.include_first_month
                      FROM services s 
                      JOIN clients c ON s.client_id = c.id 
                      JOIN plans p ON s.plan_id = p.id 
                      LEFT JOIN installations i ON i.service_id = s.id
                      WHERE s.id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $_GET['id']);
            $stmt->execute();
            $service = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($service) {
                // Fetch Products
                $q_prods = "SELECT p.id, p.name, p.price, id.quantity 
                            FROM installation_details id
                            JOIN products p ON id.product_id = p.id
                            WHERE id.service_id = :sid";
                $s_prods = $db->prepare($q_prods);
                $s_prods->bindParam(":sid", $_GET['id']);
                $s_prods->execute();
                $service['products'] = $s_prods->fetchAll(PDO::FETCH_ASSOC);
            }

            echo json_encode($service);
        } else {
            $query = "SELECT s.*, c.fullname, p.name as plan_name, p.speed_mbps 
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
                // 1. Create Service (Pending)
                $query = "INSERT INTO services (client_id, plan_id, ip_address, router_model, mac_address, installation_date, service_status) 
                          VALUES (:client_id, :plan_id, :ip_address, :router_model, :mac_address, NULL, 'pending')";
                
                $stmt = $db->prepare($query);
                
                // Defaults
                $ip = $data->ip_address ?? '';
                $router = $data->router_model ?? '';
                $mac = $data->mac_address ?? '';
                // Installation date is NULL until installed
                
                $stmt->bindParam(":client_id", $data->client_id);
                $stmt->bindParam(":plan_id", $data->plan_id);
                $stmt->bindParam(":ip_address", $ip);
                $stmt->bindParam(":router_model", $router);
                $stmt->bindParam(":mac_address", $mac);
                
                $stmt->execute();
                $service_id = $db->lastInsertId();

                // 1.1 Create Installation Record
                $q_inst = "INSERT INTO installations (client_id, service_id, status, created_at) VALUES (:cid, :sid, 'pending', NOW())";
                $s_inst = $db->prepare($q_inst);
                $s_inst->bindParam(":cid", $data->client_id);
                $s_inst->bindParam(":sid", $service_id);
                $s_inst->execute();
                
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
                
                // 3. Update Installation Record with Billing Info
                $installation_cost = isset($data->installation_cost) ? (float)$data->installation_cost : 0;
                $include_first_month = !empty($data->include_first_month) ? 1 : 0;

                $q_upd_inst = "UPDATE installations SET installation_cost = :cost, include_first_month = :first_month WHERE service_id = :sid";
                $s_upd_inst = $db->prepare($q_upd_inst);
                $s_upd_inst->bindParam(":cost", $installation_cost);
                $s_upd_inst->bindParam(":first_month", $include_first_month);
                $s_upd_inst->bindParam(":sid", $service_id);
                $s_upd_inst->execute();

                // Invoice will be generated upon completion in api/installations.php
                
                writeActivityLog("Created service for client ID: " . $data->client_id . " (Service ID: $service_id)");
                $db->commit();
                http_response_code(201);
                echo json_encode(array("message" => "Servicio registrado pendiente de instalación."));
                
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
            $db->beginTransaction();
            try {
                // 1. Update Service Basic Info
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
                $stmt->execute();

                // 2. Update Installation Info
                $installation_cost = isset($data->installation_cost) ? (float)$data->installation_cost : 0;
                $include_first_month = !empty($data->include_first_month) ? 1 : 0;

                $q_upd_inst = "UPDATE installations SET installation_cost = :cost, include_first_month = :first_month WHERE service_id = :sid";
                $s_upd_inst = $db->prepare($q_upd_inst);
                $s_upd_inst->bindParam(":cost", $installation_cost);
                $s_upd_inst->bindParam(":first_month", $include_first_month);
                $s_upd_inst->bindParam(":sid", $data->id);
                $s_upd_inst->execute();

                // 3. Update Products (Full Replace Strategy)
                // First, return stock of existing items
                $q_old = "SELECT product_id, quantity FROM installation_details WHERE service_id = :sid";
                $s_old = $db->prepare($q_old);
                $s_old->bindParam(":sid", $data->id);
                $s_old->execute();
                $old_items = $s_old->fetchAll(PDO::FETCH_ASSOC);

                foreach($old_items as $item) {
                    $q_restock = "UPDATE products SET stock = stock + :qty WHERE id = :id";
                    $s_restock = $db->prepare($q_restock);
                    $s_restock->bindParam(":qty", $item['quantity']);
                    $s_restock->bindParam(":id", $item['product_id']);
                    $s_restock->execute();
                }

                // Delete old details
                $q_del = "DELETE FROM installation_details WHERE service_id = :sid";
                $s_del = $db->prepare($q_del);
                $s_del->bindParam(":sid", $data->id);
                $s_del->execute();

                // Insert new products
                if (!empty($data->products) && is_array($data->products)) {
                    foreach ($data->products as $prod) {
                        if (!empty($prod->id) && !empty($prod->quantity)) {
                            // Get price
                            $q_price = "SELECT price FROM products WHERE id = :id";
                            $s_price = $db->prepare($q_price);
                            $s_price->bindParam(":id", $prod->id);
                            $s_price->execute();
                            $p_info = $s_price->fetch(PDO::FETCH_ASSOC);
                            
                            if($p_info) {
                                $price = $p_info['price'];
                                
                                // Insert detail
                                $q_detail = "INSERT INTO installation_details (service_id, product_id, quantity, price_at_moment) 
                                             VALUES (:service_id, :product_id, :quantity, :price)";
                                $s_detail = $db->prepare($q_detail);
                                $s_detail->bindParam(":service_id", $data->id);
                                $s_detail->bindParam(":product_id", $prod->id);
                                $s_detail->bindParam(":quantity", $prod->quantity);
                                $s_detail->bindParam(":price", $price);
                                $s_detail->execute();

                                // Deduct stock
                                $q_stock = "UPDATE products SET stock = stock - :qty WHERE id = :id";
                                $s_stock = $db->prepare($q_stock);
                                $s_stock->bindParam(":qty", $prod->quantity);
                                $s_stock->bindParam(":id", $prod->id);
                                $s_stock->execute();
                            }
                        }
                    }
                }

                writeActivityLog("Updated service ID: " . $data->id . " for client ID: " . $data->client_id);
                $db->commit();
                http_response_code(200);
                echo json_encode(array("message" => "Servicio actualizado exitosamente."));

            } catch (Exception $e) {
                $db->rollBack();
                http_response_code(503);
                echo json_encode(array("message" => "Error al actualizar: " . $e->getMessage()));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
        }
        break;
}
?>
