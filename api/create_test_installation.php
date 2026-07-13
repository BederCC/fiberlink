<?php
// api/create_test_installation.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/database.php';
require_once '../includes/PdfGenerator.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode([
        "status" => "error",
        "message" => "Error: No se pudo conectar a la base de datos."
    ]);
    exit;
}

// 1. Obtener parámetros: client_id (opcional) y count (cantidad a generar)
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
$count = isset($_GET['count']) ? intval($_GET['count']) : 1;
if ($count <= 0) {
    $count = 1;
}
if ($count > 30) {
    $count = 30; // Límite para evitar sobrecarga
}

// 2. Obtener la lista de clientes a procesar
$clients = [];

if ($client_id > 0) {
    // Si se especifica un cliente, solo procesamos ese
    $q_client = "SELECT id, fullname FROM clients WHERE id = :id";
    $stmt = $db->prepare($q_client);
    $stmt->bindParam(":id", $client_id);
    $stmt->execute();
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($client) {
        $clients[] = $client;
    }
} else {
    // Buscar clientes que NO tengan ningún servicio registrado (para no repetir personas en la lista)
    // Usamos bindValue con PDO::PARAM_INT para el LIMIT
    $q_clients = "SELECT c.id, c.fullname FROM clients c LEFT JOIN services s ON c.id = s.client_id WHERE s.id IS NULL LIMIT :limit";
    $stmt = $db->prepare($q_clients);
    $stmt->bindValue(":limit", $count, PDO::PARAM_INT);
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Si se necesitan más clientes para completar la cantidad solicitada (count), seleccionamos aleatorios excluyendo los ya elegidos
    $needed = $count - count($clients);
    if ($needed > 0) {
        $exclude_ids = array_column($clients, 'id');
        $exclude_clause = "";
        if (!empty($exclude_ids)) {
            $exclude_clause = "WHERE id NOT IN (" . implode(',', array_map('intval', $exclude_ids)) . ") ";
        }
        
        $q_rand = "SELECT id, fullname FROM clients $exclude_clause ORDER BY RAND() LIMIT :limit";
        $stmt = $db->prepare($q_rand);
        $stmt->bindValue(":limit", $needed, PDO::PARAM_INT);
        $stmt->execute();
        $rand_clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $clients = array_merge($clients, $rand_clients);
    }
}

if (empty($clients)) {
    echo json_encode([
        "status" => "error",
        "message" => "Error: No hay clientes registrados en la base de datos o el cliente especificado no existe."
    ]);
    exit;
}

// 3. Obtener el primer plan disponible
$q_plan = "SELECT id, name, price FROM plans LIMIT 1";
$stmt = $db->query($q_plan);
$plan = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$plan) {
    echo json_encode([
        "status" => "error",
        "message" => "Error: No hay planes registrados. Cree un plan de internet primero."
    ]);
    exit;
}
$plan_id = $plan['id'];

// 4. Obtener el primer técnico disponible
$q_tech = "SELECT id FROM users LIMIT 1";
$stmt = $db->query($q_tech);
$tech = $stmt->fetch(PDO::FETCH_ASSOC);
$tech_id = $tech ? $tech['id'] : null;

// Iniciar transacción
$db->beginTransaction();
try {
    $generated = [];
    
    foreach ($clients as $client) {
        $curr_client_id = $client['id'];
        $curr_client_name = $client['fullname'];
        
        // A. Crear el servicio (sin "Prueba" en el router)
        $ip_address = "192.168.100." . rand(2, 254);
        $mac_address = sprintf('%02X:%02X:%02X:%02X:%02X:%02X', rand(0, 255), rand(0, 255), rand(0, 255), rand(0, 255), rand(0, 255), rand(0, 255));
        $router_model = "Router Huawei Dual Band"; // Sin la palabra "(Prueba)"
        
        $q_serv = "INSERT INTO services (client_id, plan_id, ip_address, router_model, mac_address, installation_date, service_status) 
                   VALUES (:client_id, :plan_id, :ip_address, :router_model, :mac_address, CURDATE(), 'active')";
        $stmt = $db->prepare($q_serv);
        $stmt->bindParam(":client_id", $curr_client_id);
        $stmt->bindParam(":plan_id", $plan_id);
        $stmt->bindParam(":ip_address", $ip_address);
        $stmt->bindParam(":router_model", $router_model);
        $stmt->bindParam(":mac_address", $mac_address);
        $stmt->execute();
        $service_id = $db->lastInsertId();

        // B. Crear el registro de instalación como completado
        $installation_cost = 50.00;
        $include_first_month = 1;
        $notes = "Instalación completada de forma automática por el sistema.";
        
        $q_inst = "INSERT INTO installations (client_id, service_id, status, created_at, completed_date, installation_cost, include_first_month, notes, technician_id) 
                   VALUES (:client_id, :service_id, 'completed', NOW(), NOW(), :cost, :include_first, :notes, :tech_id)";
        $stmt = $db->prepare($q_inst);
        $stmt->bindParam(":client_id", $curr_client_id);
        $stmt->bindParam(":service_id", $service_id);
        $stmt->bindParam(":cost", $installation_cost);
        $stmt->bindParam(":include_first", $include_first_month);
        $stmt->bindParam(":notes", $notes);
        $stmt->bindParam(":tech_id", $tech_id);
        $stmt->execute();
        $installation_id = $db->lastInsertId();

        // C. Generar factura de instalación (sin accesorios)
        $products_total = 0;
        $total_invoice = $installation_cost + $products_total + ($include_first_month ? $plan['price'] : 0);
        $invoice_number = 'INS-' . str_pad($service_id, 6, '0', STR_PAD_LEFT);
        $issue_date = date('Y-m-d');
        $due_date = date('Y-m-d', strtotime('+7 days'));

        $q_inv = "INSERT INTO invoices (client_id, invoice_number, issue_date, due_date, total_amount, status, type) 
                  VALUES (:client_id, :invoice_number, :issue_date, :due_date, :total_amount, 'unpaid', 'installation')";
        $stmt = $db->prepare($q_inv);
        $stmt->bindParam(":client_id", $curr_client_id);
        $stmt->bindParam(":invoice_number", $invoice_number);
        $stmt->bindParam(":issue_date", $issue_date);
        $stmt->bindParam(":due_date", $due_date);
        $stmt->bindParam(":total_amount", $total_invoice);
        $stmt->execute();
        $invoice_id = $db->lastInsertId();

        // Detalle de factura: costo de instalación
        $q_item = "INSERT INTO invoice_items (invoice_id, description, amount) VALUES (:inv_id, 'Costo de Instalación', :amount)";
        $stmt = $db->prepare($q_item);
        $stmt->bindParam(":inv_id", $invoice_id);
        $stmt->bindParam(":amount", $installation_cost);
        $stmt->execute();

        // Detalle de factura: primer mes
        if ($include_first_month) {
            $q_item = "INSERT INTO invoice_items (invoice_id, description, amount) VALUES (:inv_id, 'Primer Mes de Servicio', :amount)";
            $stmt = $db->prepare($q_item);
            $stmt->bindParam(":inv_id", $invoice_id);
            $stmt->bindParam(":amount", $plan['price']);
            $stmt->execute();
        }

        if (function_exists('writeActivityLog')) {
            writeActivityLog("Created test installation ID: $installation_id for client: $curr_client_name");
        }
        
        $generated[] = [
            "client_name" => $curr_client_name,
            "service_id" => $service_id,
            "installation_id" => $installation_id,
            "invoice_id" => $invoice_id
        ];
    }

    $db->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Se generaron exitosamente " . count($generated) . " registros de instalación sin accesorios.",
        "data" => $generated
    ]);

} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        "status" => "error",
        "message" => "Error al crear instalación de prueba: " . $e->getMessage()
    ]);
}
?>
