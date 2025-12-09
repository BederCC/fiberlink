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
            
            if($stmt->execute()) {
                http_response_code(201);
                echo json_encode(array("message" => "Servicio creado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear el servicio."));
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
