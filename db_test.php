<?php
// db_test.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Iniciando prueba de conexión de base de datos...</h2>";

// 1. Detectar entorno
$is_local = false;
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' || PHP_OS_FAMILY === 'Windows') {
    $is_local = true;
} elseif (isset($_SERVER['HTTP_HOST'])) {
    $http_host = $_SERVER['HTTP_HOST'];
    if (
        strpos($http_host, 'localhost') !== false || 
        strpos($http_host, '127.0.0.1') !== false || 
        strpos($http_host, '::1') !== false || 
        preg_match('/^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.)/', $http_host) ||
        preg_match('/\.local$/', $http_host) ||
        preg_match('/\.test$/', $http_host)
    ) {
        $is_local = true;
    }
}

echo "Entorno detectado: " . ($is_local ? "<b>LOCAL (Desarrollo)</b>" : "<b>PRODUCCIÓN (Hostinger)</b>") . "<br>";

// 2. Cargar credenciales según entorno
if ($is_local) {
    $host = "localhost";
    $db_name = "fiberlink";
    $username = "root";
    $password = "";
} else {
    $host = "localhost"; 
    $db_name = "u226387153_fiberlink"; 
    $username = "u226387153_fiberlink";     
    $password = "FiberLink123";   
}

echo "Intentando conectar a <b>$host</b> con la base de datos <b>$db_name</b> y usuario <b>$username</b>...<br><br>";

try {
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("set names utf8");
    echo "<span style='color: green; font-weight: bold;'>¡CONEXIÓN EXITOSA! El script pudo conectarse a la base de datos sin problemas.</span>";
} catch(PDOException $exception) {
    echo "<span style='color: red; font-weight: bold;'>ERROR DE CONEXIÓN:</span><br>";
    echo "<pre>" . $exception->getMessage() . "</pre>";
    echo "<br><b>Sugerencias de resolución:</b><br>";
    echo "1. Verifique si el nombre de la base de datos, usuario o contraseña son 100% correctos.<br>";
    echo "2. En Hostinger, asegúrese de que el usuario de base de datos tenga permisos asignados a esa base de datos específica.<br>";
    echo "3. Verifique si el host de la base de datos es 'localhost' o si Hostinger le proporciona una dirección IP o un host de base de datos diferente en el Panel de Control.<br>";
}
?>
