<?php
// api/get_reminder_logs.php
header('Content-Type: text/plain; charset=utf-8');
$log_file = __DIR__ . '/../logs/reminders.log';

if (file_exists($log_file)) {
    // Read the last 200 lines of the file for performance
    $lines = file($log_file);
    $last_lines = array_slice($lines, -200);
    echo implode("", $last_lines);
} else {
    echo "No hay logs registrados aún.";
}
?>
