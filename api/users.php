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
            $query = "SELECT id, username, full_name, role, status, created_at FROM users WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $_GET['id']);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($user);
        } else {
            $query = "SELECT id, username, full_name, role, status, created_at FROM users ORDER BY id DESC";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($users);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->username) && !empty($data->password) && !empty($data->full_name)) {
            $query = "INSERT INTO users (username, password, full_name, role, status) VALUES (:username, :password, :full_name, :role, :status)";
            $stmt = $db->prepare($query);
            
            $password_hash = password_hash($data->password, PASSWORD_BCRYPT);
            $role = $data->role ?? 'technician';
            $status = $data->status ?? 1;
            
            $stmt->bindParam(":username", $data->username);
            $stmt->bindParam(":password", $password_hash);
            $stmt->bindParam(":full_name", $data->full_name);
            $stmt->bindParam(":role", $role);
            $stmt->bindParam(":status", $status);
            
            if($stmt->execute()) {
                http_response_code(201);
                echo json_encode(array("message" => "Usuario creado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear el usuario."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $query = "UPDATE users SET username = :username, full_name = :full_name, role = :role, status = :status";
            
            // Only update password if provided
            if (!empty($data->password)) {
                $query .= ", password = :password";
            }
            
            $query .= " WHERE id = :id";
            
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(":username", $data->username);
            $stmt->bindParam(":full_name", $data->full_name);
            $stmt->bindParam(":role", $data->role);
            $stmt->bindParam(":status", $data->status);
            $stmt->bindParam(":id", $data->id);
            
            if (!empty($data->password)) {
                $password_hash = password_hash($data->password, PASSWORD_BCRYPT);
                $stmt->bindParam(":password", $password_hash);
            }
            
            if($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Usuario actualizado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo actualizar el usuario."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            // Prevent deleting the last admin or self (optional safety check, skipping for now but good to keep in mind)
            
            $query = "DELETE FROM users WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $data->id);
            
            if($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Usuario eliminado."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo eliminar el usuario."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
        }
        break;
}
?>
