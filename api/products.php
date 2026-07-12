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
            $query = "SELECT * FROM products WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $_GET['id']);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($product);
        } else {
            $query = "SELECT * FROM products ORDER BY id DESC";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($products);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->name) && isset($data->price)) {
            $query = "INSERT INTO products (name, description, price, stock) VALUES (:name, :description, :price, :stock)";
            $stmt = $db->prepare($query);
            
            $desc = $data->description ?? '';
            $stock = $data->stock ?? 0;
            
            $stmt->bindParam(":name", $data->name);
            $stmt->bindParam(":description", $desc);
            $stmt->bindParam(":price", $data->price);
            $stmt->bindParam(":stock", $stock);
            
            if($stmt->execute()) {
                writeActivityLog("Created product: " . $data->name . " (Price: S/ " . number_format($data->price, 2) . ", Stock: " . $stock . ")");
                http_response_code(201);
                echo json_encode(array("message" => "Producto creado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear el producto."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id) && !empty($data->name)) {
            $query = "UPDATE products SET name = :name, description = :description, price = :price, stock = :stock WHERE id = :id";
            $stmt = $db->prepare($query);
            
            $desc = $data->description ?? '';
            $stock = $data->stock ?? 0;
            
            $stmt->bindParam(":name", $data->name);
            $stmt->bindParam(":description", $desc);
            $stmt->bindParam(":price", $data->price);
            $stmt->bindParam(":stock", $stock);
            $stmt->bindParam(":id", $data->id);
            
            if($stmt->execute()) {
                writeActivityLog("Updated product ID: " . $data->id . " - Name: " . $data->name . " (Price: S/ " . number_format($data->price, 2) . ", Stock: " . $stock . ")");
                http_response_code(200);
                echo json_encode(array("message" => "Producto actualizado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo actualizar el producto."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $query = "DELETE FROM products WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $data->id);
            
            if($stmt->execute()) {
                writeActivityLog("Deleted product ID: " . $data->id);
                http_response_code(200);
                echo json_encode(array("message" => "Producto eliminado."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo eliminar el producto."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
        }
        break;
}
?>
