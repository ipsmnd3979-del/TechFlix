<?php
session_start();
echo "Debug Info:<br>";
echo "Session started: " . (isset($_SESSION) ? 'Yes' : 'No') . "<br>";
echo "Logged in: " . (isset($_SESSION['loggedin']) ? $_SESSION['loggedin'] : 'No') . "<br>";
echo "User role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'Not set') . "<br>";
echo "Session ID: " . session_id() . "<br>";

// Test database connection
require_once '../includes/config.php';
echo "Database connected: " . ($conn ? 'Yes' : 'No') . "<br>";

// Check if user should be redirected
$shouldRedirect = !isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin');
echo "Should redirect: " . ($shouldRedirect ? 'Yes' : 'No') . "<br>";
?>