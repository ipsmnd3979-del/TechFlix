<?php
// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "ott_platform";
$port = 3306;

// Create connection
$conn = new mysqli($host, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8mb4");

// Helper function to check for missing tables
if (!function_exists('checkMissingTables')) {
    function checkMissingTables() {
        global $conn;
        
        $required_tables = ['users', 'content', 'categories', 'media_files', 'banners', 'seasons'];
        $missing_tables = [];
        
        foreach ($required_tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows == 0) {
                $missing_tables[] = $table;
                error_log("Missing table: $table");
            }
        }
        
        return $missing_tables;
    }
}

// Main database schema check function
if (!function_exists('checkDatabaseSchema')) {
    function checkDatabaseSchema() {
        global $conn;
        
        // Check if status column exists in users table
        $result = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
        if ($result->num_rows == 0) {
            // Add the missing column
            $conn->query("ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'");
            $conn->query("UPDATE users SET status = 'active' WHERE status IS NULL");
            error_log("Added status column to users table");
        }
        
        // Check for other missing tables
        $missing_tables = checkMissingTables();
        
        if (!empty($missing_tables)) {
            error_log("Missing tables detected: " . implode(', ', $missing_tables));
        }
    }
}

// Call the schema check function
checkDatabaseSchema();
?>