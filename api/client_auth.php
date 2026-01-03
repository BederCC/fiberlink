<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';
include_once '../includes/Mailer.php';

$database = new Database();
$db = $database->getConnection();

$action = isset($_GET['action']) ? $_GET['action'] : '';
$data = json_decode(file_get_contents("php://input"));

if ($action === 'login') {
    if (empty($data->dni) || empty($data->password)) {
        http_response_code(400);
        echo json_encode(["message" => "Datos incompletos"]);
        exit;
    }

    $query = "SELECT id, fullname, password FROM clients WHERE dni_ruc = :dni";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":dni", $data->dni);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['password'] && password_verify($data->password, $user['password'])) {
        session_start();
        $_SESSION['client_id'] = $user['id'];
        $_SESSION['client_name'] = $user['fullname'];
        
        echo json_encode(["message" => "Login exitoso"]);
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Credenciales incorrectas o cuenta no activada"]);
    }

} elseif ($action === 'check_dni') {
    if (empty($data->dni)) {
        http_response_code(400);
        echo json_encode(["message" => "DNI requerido"]);
        exit;
    }

    $query = "SELECT id, email FROM clients WHERE dni_ruc = :dni";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":dni", $data->dni);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $hasEmail = !empty($user['email']);
        $maskedEmail = '';
        if ($hasEmail) {
            $parts = explode('@', $user['email']);
            $maskedEmail = substr($parts[0], 0, 3) . '***@' . $parts[1];
        }
        echo json_encode(["exists" => true, "has_email" => $hasEmail, "masked_email" => $maskedEmail]);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Cliente no encontrado"]);
    }

} elseif ($action === 'send_activation') {
    if (empty($data->dni)) {
        http_response_code(400);
        echo json_encode(["message" => "DNI requerido"]);
        exit;
    }

    // 1. Verify User
    $query = "SELECT id, fullname, email FROM clients WHERE dni_ruc = :dni";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":dni", $data->dni);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(["message" => "Cliente no encontrado"]);
        exit;
    }

    // 2. Update Email if provided and currently empty
    $emailToSend = $user['email'];
    if (empty($user['email']) && !empty($data->email)) {
        $updateQ = "UPDATE clients SET email = :email WHERE id = :id";
        $updateS = $db->prepare($updateQ);
        $updateS->bindParam(":email", $data->email);
        $updateS->bindParam(":id", $user['id']);
        $updateS->execute();
        $emailToSend = $data->email;
    }

    if (empty($emailToSend)) {
        http_response_code(400);
        echo json_encode(["message" => "Correo electrónico requerido"]);
        exit;
    }

    // 3. Generate Token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $updateToken = "UPDATE clients SET reset_token = :token, reset_expires = :expires WHERE id = :id";
    $stmtToken = $db->prepare($updateToken);
    $stmtToken->bindParam(":token", $token);
    $stmtToken->bindParam(":expires", $expires);
    $stmtToken->bindParam(":id", $user['id']);
    
    if ($stmtToken->execute()) {
        // 4. Send Email
        $mailer = new Mailer();
        $link = "http://" . $_SERVER['HTTP_HOST'] . "/fiberlink/set_password.php?token=" . $token;
        
        if ($mailer->sendActivationEmail($user['email'] ?? $data->email, $user['fullname'], $link)) {
            echo json_encode(["message" => "Enlace enviado"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error al enviar correo: " . $mailer->getLastError()]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error al generar token"]);
    }

} elseif ($action === 'set_password') {
    if (empty($data->token) || empty($data->password)) {
        http_response_code(400);
        echo json_encode(["message" => "Datos incompletos"]);
        exit;
    }

    $query = "SELECT id FROM clients WHERE reset_token = :token AND reset_expires > NOW()";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":token", $data->token);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $hash = password_hash($data->password, PASSWORD_DEFAULT);
        $update = "UPDATE clients SET password = :pass, reset_token = NULL, reset_expires = NULL WHERE id = :id";
        $stmtUpdate = $db->prepare($update);
        $stmtUpdate->bindParam(":pass", $hash);
        $stmtUpdate->bindParam(":id", $user['id']);
        
        if ($stmtUpdate->execute()) {
            echo json_encode(["message" => "Contraseña actualizada"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error al actualizar contraseña"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Token inválido o expirado"]);
    }
}
?>
