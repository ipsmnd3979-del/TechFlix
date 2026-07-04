<?php
require_once 'includes/config.php';

echo "<h1>Image Path Test</h1>";
echo "<p>Checking content table for images...</p>";

$result = $conn->query("SELECT id, title, poster_image, thumbnail FROM content LIMIT 10");

if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Poster Image</th><th>Thumbnail</th><th>Test Image</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['title']}</td>";
        echo "<td>{$row['poster_image']}</td>";
        echo "<td>{$row['thumbnail']}</td>";
        
        // Test image display
        $testImage = 'https://via.placeholder.com/100x150/369/fff?text=Test';
        if (!empty($row['poster_image'])) {
            $testImage = $row['poster_image'];
        } elseif (!empty($row['thumbnail'])) {
            $testImage = $row['thumbnail'];
        }
        
        echo "<td><img src='{$testImage}' style='width: 100px; height: 150px; border: 1px solid red;' onerror=\"this.src='https://via.placeholder.com/100x150/f00/fff?text=Error'\"></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No content found in database!";
}

// Check if default image exists
echo "<h2>File Check</h2>";
$defaultImage = 'assets/img/default-poster.jpg';
if (file_exists($defaultImage)) {
    echo "<p style='color: green;'>✅ Default image exists: {$defaultImage}</p>";
} else {
    echo "<p style='color: red;'>❌ Default image missing: {$defaultImage}</p>";
}

// Check uploads directory
$uploadsDir = 'assets/uploads/thumbnails/';
if (is_dir($uploadsDir)) {
    echo "<p style='color: green;'>✅ Uploads directory exists</p>";
    $files = scandir($uploadsDir);
    echo "<p>Files in uploads: " . implode(', ', array_slice($files, 2)) . "</p>";
} else {
    echo "<p style='color: red;'>❌ Uploads directory missing: {$uploadsDir}</p>";
}
?>