<?php
require_once '../includes/config.php';

echo "<h2>Checking Database Structure</h2>";

// Check if content table exists and show its structure
$result = $conn->query("SHOW TABLES LIKE 'content'");
if ($result->num_rows > 0) {
    echo "<p>✅ Content table exists</p>";
    
    // Show table structure
    $structure = $conn->query("DESCRIBE content");
    echo "<h3>Current Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Fix the table structure
    echo "<h3>Fixing Table Structure...</h3>";
    
    // Add missing columns
    $alter_queries = [
        "ALTER TABLE content ADD COLUMN IF NOT EXISTS category VARCHAR(100) AFTER type",
        "ALTER TABLE content ADD COLUMN IF NOT EXISTS release_year INT AFTER category",
        "ALTER TABLE content ADD COLUMN IF NOT EXISTS duration INT AFTER release_year", 
        "ALTER TABLE content ADD COLUMN IF NOT EXISTS rating DECIMAL(3,1) AFTER duration",
        "ALTER TABLE content ADD COLUMN IF NOT EXISTS thumbnail VARCHAR(500) AFTER rating",
        "ALTER TABLE content ADD COLUMN IF NOT EXISTS video_path VARCHAR(500) AFTER thumbnail"
    ];
    
    foreach ($alter_queries as $query) {
        if ($conn->query($query)) {
            echo "<p>✅ " . $query . "</p>";
        } else {
            echo "<p>❌ " . $query . " - Error: " . $conn->error . "</p>";
        }
    }
    
} else {
    echo "<p>❌ Content table doesn't exist. Creating it...</p>";
    
    $create_table = "CREATE TABLE content (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        type ENUM('movie', 'tv_show', 'kids') NOT NULL,
        category VARCHAR(100),
        release_year INT,
        duration INT,
        rating DECIMAL(3,1),
        thumbnail VARCHAR(500),
        video_path VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table)) {
        echo "<p>✅ Content table created successfully</p>";
    } else {
        echo "<p>❌ Failed to create table: " . $conn->error . "</p>";
    }
}

echo "<hr>";
echo "<a href='dashboard.php'>Go to Dashboard</a>";
?>