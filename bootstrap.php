<?php
// backend/bootstrap.php
$config_path = __DIR__ . '/config.php';
if (file_exists($config_path)) {
    require_once($config_path);
} else {
    die('Configuration file not found! Please check the installation.');
}

// Fixed to avoid double invocation issues across the scripts
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>