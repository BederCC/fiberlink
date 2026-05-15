<?php
// config.php - Centralized configuration
date_default_timezone_set('America/Lima');

// Determine the base URL of the application
// This script assumes it's included from somewhere within the project structure

// Get the protocol (http or https)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";

// Get the server name (e.g., localhost)
$host = $_SERVER['HTTP_HOST'];

// Get the script name (e.g., /fiberlink/public/dashboard.php)
$script_name = $_SERVER['SCRIPT_NAME'];

// Find the position of 'public' or 'api' in the path to determine the root
$path_parts = explode('/', trim($script_name, '/'));
$public_index = array_search('public', $path_parts);
$api_index = array_search('api', $path_parts);

if ($public_index !== false) {
    // Reconstruct path up to 'public'
    $base_path = '/' . implode('/', array_slice($path_parts, 0, $public_index));
} elseif ($api_index !== false) {
    // Reconstruct path up to 'api'
    $base_path = '/' . implode('/', array_slice($path_parts, 0, $api_index));
} else {
    // If neither is found, we might be at root or in another folder
    // For production (like Hostinger), if it's at the root of a domain/subdomain, base_path should be empty
    // unless it's in a subdirectory like /fiberlink/
    if (count($path_parts) > 1) {
         // If there's more than one part and no public/api, it might be /project/file.php
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
