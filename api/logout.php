<?php
require_once '../config.php';
require_once '../config/database.php';

writeActivityLog("Logged out");
session_destroy();

header("Location: " . BASE_URL . "/index.php");
exit;
?>
