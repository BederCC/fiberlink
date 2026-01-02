<?php
// config.php - Centralized configuration

// Determine the base URL of the application
// This script assumes it's included from somewhere within the project structure

// Get the protocol (http or https)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";

// Get the server name (e.g., localhost)
$host = $_SERVER['HTTP_HOST'];

// Get the script name (e.g., /fiberlink/public/dashboard.php)
$script_name = $_SERVER['SCRIPT_NAME'];

// Find the position of 'public' in the path to determine the root
$path_parts = explode('/', trim($script_name, '/'));
$public_index = array_search('public', $path_parts);

if ($public_index !== false) {
    // Reconstruct path up to 'public'
    $base_path = '/' . implode('/', array_slice($path_parts, 0, $public_index));
} else {
    // Fallback: if 'public' is not found, maybe we are at root?
    // Or maybe the folder is named differently?
    // Let's assume standard xampp structure /project_name/
    if (count($path_parts) > 0) {
         $base_path = '/' . $path_parts[0];
    } else {
         $base_path = '';
    }
}

// Ensure no trailing slash
$base_path = rtrim($base_path, '/');

// Full Base URL (e.g., http://localhost/fiberlink)
$base_url = $protocol . "://" . $host . $base_path;

// Define commonly used paths
define('BASE_URL', $base_url);
define('API_URL', $base_url . '/api');
define('PUBLIC_URL', $base_url . '/public');
define('ASSETS_URL', $base_url . '/src');

?>
