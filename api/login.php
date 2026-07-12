<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->username) && !empty($data->password)) {
    $query = "SELECT id, username, password, full_name, role FROM users WHERE username = :username LIMIT 0,1";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(":username", $data->username);
    $stmt->execute();
    
    if($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(password_verify($data->password, $row['password'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user'] = array(
                "id" => $row['id'],
                "username" => $row['username'],
                "full_name" => $row['full_name'],
                "role" => $row['role']
            );
            
            // Generate a simple token (in production use JWT)
            $token = bin2hex(random_bytes(16));
            
            writeActivityLog("Logged in");
            
            // Start session or store token in DB if needed, for now just return success
            http_response_code(200);
            echo json_encode(array(
                "message" => "Login successful.",
                "token" => $token,
                "user" => array(
                    "id" => $row['id'],
                    "username" => $row['username'],
                    "full_name" => $row['full_name'],
                    "role" => $row['role']
                )
            ));
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Login failed. Incorrect password."));
        }
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Login failed. User not found."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data."));
}
?>
