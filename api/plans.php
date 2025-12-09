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
            $query = "SELECT * FROM plans WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $_GET['id']);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($row);
        } else {
            $query = "SELECT * FROM plans ORDER BY price ASC";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->name) && !empty($data->speed_mbps) && !empty($data->price)) {
            $query = "INSERT INTO plans SET name=:name, speed_mbps=:speed_mbps, price=:price, description=:description";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(":name", $data->name);
            $stmt->bindParam(":speed_mbps", $data->speed_mbps);
            $stmt->bindParam(":price", $data->price);
            $stmt->bindParam(":description", $data->description);
            
            if($stmt->execute()) {
                http_response_code(201);
                echo json_encode(array("message" => "Plan creado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear el plan."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $query = "UPDATE plans SET name=:name, speed_mbps=:speed_mbps, price=:price, description=:description WHERE id=:id";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(":name", $data->name);
            $stmt->bindParam(":speed_mbps", $data->speed_mbps);
            $stmt->bindParam(":price", $data->price);
            $stmt->bindParam(":description", $data->description);
            $stmt->bindParam(":id", $data->id);
            
            if($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Plan actualizado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo actualizar el plan."));
            }
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $query = "DELETE FROM plans WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $data->id);
            
            if($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Plan eliminado."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo eliminar el plan."));
            }
        }
        break;
}
?>
