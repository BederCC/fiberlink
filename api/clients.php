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
        if(isset($_GET['id'])) {
            // Get single client
            $query = "SELECT * FROM clients WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $_GET['id']);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($row);
        } else {
            // Get all clients
            $query = "SELECT * FROM clients ORDER BY created_at DESC";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->first_name) && !empty($data->last_name) && !empty($data->dni_ruc)) {
            $query = "INSERT INTO clients SET first_name=:first_name, last_name=:last_name, dni_ruc=:dni_ruc, email=:email, phone=:phone, address=:address, coordinates=:coordinates";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(":first_name", $data->first_name);
            $stmt->bindParam(":last_name", $data->last_name);
            $stmt->bindParam(":dni_ruc", $data->dni_ruc);
            $stmt->bindParam(":email", $data->email);
            $stmt->bindParam(":phone", $data->phone);
            $stmt->bindParam(":address", $data->address);
            $stmt->bindParam(":coordinates", $data->coordinates);
            
            if($stmt->execute()) {
                http_response_code(201);
                echo json_encode(array("message" => "Cliente creado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear el cliente."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $query = "UPDATE clients SET first_name=:first_name, last_name=:last_name, dni_ruc=:dni_ruc, email=:email, phone=:phone, address=:address, coordinates=:coordinates, status=:status WHERE id=:id";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(":first_name", $data->first_name);
            $stmt->bindParam(":last_name", $data->last_name);
            $stmt->bindParam(":dni_ruc", $data->dni_ruc);
            $stmt->bindParam(":email", $data->email);
            $stmt->bindParam(":phone", $data->phone);
            $stmt->bindParam(":address", $data->address);
            $stmt->bindParam(":coordinates", $data->coordinates);
            $stmt->bindParam(":status", $data->status);
            $stmt->bindParam(":id", $data->id);
            
            if($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Cliente actualizado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo actualizar el cliente."));
            }
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $query = "DELETE FROM clients WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $data->id);
            
            if($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Cliente eliminado."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo eliminar el cliente."));
            }
        }
        break;
}
?>
