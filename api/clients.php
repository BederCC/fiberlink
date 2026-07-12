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
            // Get all clients with pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $search = isset($_GET['search']) ? $_GET['search'] : '';

            $where = "";
            if (!empty($search)) {
                $where = "WHERE fullname LIKE :search OR dni_ruc LIKE :search OR phone LIKE :search";
            }

            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM clients $where";
            $stmtCount = $db->prepare($countQuery);
            if (!empty($search)) {
                $searchTerm = "%$search%";
                $stmtCount->bindParam(":search", $searchTerm);
            }
            $stmtCount->execute();
            $totalRows = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
            $totalPages = ceil($totalRows / $limit);

            // Get paginated data
            $query = "SELECT * FROM clients $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($query);
            
            if (!empty($search)) {
                $searchTerm = "%$search%";
                $stmt->bindParam(":search", $searchTerm);
            }
            
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                "data" => $result,
                "pagination" => [
                    "current_page" => $page,
                    "total_pages" => $totalPages,
                    "total_records" => $totalRows,
                    "limit" => $limit
                ]
            ]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->fullname) && !empty($data->dni_ruc)) {
            $query = "INSERT INTO clients SET fullname=:fullname, dni_ruc=:dni_ruc, email=:email, phone=:phone, address=:address, coordinates=:coordinates";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(":fullname", $data->fullname);
            $stmt->bindParam(":dni_ruc", $data->dni_ruc);
            $stmt->bindParam(":email", $data->email);
            $stmt->bindParam(":phone", $data->phone);
            $stmt->bindParam(":address", $data->address);
            $stmt->bindParam(":coordinates", $data->coordinates);
            
            if($stmt->execute()) {
                writeActivityLog("Created client: " . $data->fullname . " (DNI/RUC: " . $data->dni_ruc . ")");
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
            $query = "UPDATE clients SET fullname=:fullname, dni_ruc=:dni_ruc, email=:email, phone=:phone, address=:address, coordinates=:coordinates, status=:status WHERE id=:id";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(":fullname", $data->fullname);
            $stmt->bindParam(":dni_ruc", $data->dni_ruc);
            $stmt->bindParam(":email", $data->email);
            $stmt->bindParam(":phone", $data->phone);
            $stmt->bindParam(":address", $data->address);
            $stmt->bindParam(":coordinates", $data->coordinates);
            $stmt->bindParam(":status", $data->status);
            $stmt->bindParam(":id", $data->id);
            
            if($stmt->execute()) {
                writeActivityLog("Updated client ID: " . $data->id . " - Name: " . $data->fullname . " (Status: " . $data->status . ")");
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
                writeActivityLog("Deleted client ID: " . $data->id);
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
