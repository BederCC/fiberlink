<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../includes/MikrotikSimulator.php';

$database = new Database();
$db = $database->getConnection();
$router = new MikrotikSimulator();

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->service_id) && !empty($data->action)) {
    
    // Get Service Details
    $query = "SELECT * FROM services WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $data->service_id);
    $stmt->execute();
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($service) {
        $ip = $service['ip_address'];
        
        if($data->action === 'cut') {
            // Block IP
            $router->connect('192.168.88.1', 'admin', 'password');
            $result = $router->blockIP($ip);
            
            // Update DB
            $q_update = "UPDATE services SET service_status = 'suspended' WHERE id = :id";
            $s_update = $db->prepare($q_update);
            $s_update->bindParam(":id", $data->service_id);
            $s_update->execute();
            
            echo json_encode(['message' => 'Servicio cortado exitosamente.', 'router_response' => $result]);
            
        } elseif($data->action === 'restore') {
            // Unblock IP
            $router->connect('192.168.88.1', 'admin', 'password');
            $result = $router->unblockIP($ip);
            
            // Update DB
            $q_update = "UPDATE services SET service_status = 'active' WHERE id = :id";
            $s_update = $db->prepare($q_update);
            $s_update->bindParam(":id", $data->service_id);
            $s_update->execute();
            
            echo json_encode(['message' => 'Servicio repuesto exitosamente.', 'router_response' => $result]);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Acción no válida.']);
        }
        
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Servicio no encontrado.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['message' => 'Datos incompletos.']);
}
?>
