<?php
echo "<h1>Setup Test</h1>";

// Test 1: Basic PHP
echo "<p>✅ PHP is working</p>";

// Test 2: Session
session_start();
echo "<p>✅ Sessions working. ID: " . session_id() . "</p>";

// Test 3: File includes
$config_path = '../includes/config.php';
if (file_exists($config_path)) {
    echo "<p>✅ Config file exists</p>";
    
    require_once $config_path;
    if (isset($conn)) {
        echo "<p>✅ Database connected</p>";
    } else {
        echo "<p>❌ Database connection failed</p>";
    }
} else {
    echo "<p>❌ Config file not found at: $config_path</p>";
}

// Test 4: Current session status
echo "<p>Session loggedin: " . (isset($_SESSION['loggedin']) ? 'true' : 'false') . "</p>";
echo "<p>Session role: " . ($_SESSION['role'] ?? 'not set') . "</p>";

// Test 5: What would redirect logic do?
$should_redirect = !isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin');
echo "<p>Would redirect to login: " . ($should_redirect ? 'YES' : 'NO') . "</p>";

echo "<hr><h3>Quick Fix:</h3>";
echo "<p><a href='simple.php'>Go to Simple Admin</a> (no authentication)</p>";
echo "<p><a href='manual-login.php'>Set admin session manually</a></p>";
?>