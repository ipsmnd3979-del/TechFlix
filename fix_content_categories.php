<?php
require_once 'includes/header.php';

// Check if admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die('Admin access required.');
}

echo "<h2>Fixing Content Categories</h2>";

// First, show available categories
$categories_query = "SELECT id, name FROM categories";
$categories_result = $conn->query($categories_query);

echo "<h3>Available Categories:</h3>";
echo "<ul>";
while($cat = $categories_result->fetch_assoc()) {
    echo "<li><strong>{$cat['id']}</strong> - {$cat['name']}</li>";
}
echo "</ul>";

// Update content with proper category IDs
$updates = [
    ['id' => 2, 'title' => 'Mirai', 'category_id' => 1],      // Action
    ['id' => 4, 'title' => 'Anupamaa', 'category_id' => 5],   // Drama
    ['id' => 10, 'title' => 'Kota Factory', 'category_id' => 5], // Drama
    ['id' => 11, 'title' => 'Kota Factory', 'category_id' => 10], // Animation
    ['id' => 12, 'title' => 'Rana Naidu', 'category_id' => 5] // Drama
];

echo "<h3>Updating Content Categories:</h3>";
foreach($updates as $update) {
    $update_query = "UPDATE content SET category_id = {$update['category_id']} WHERE id = {$update['id']}";
    
    if ($conn->query($update_query)) {
        echo "<p style='color: green;'>✓ Updated: {$update['title']} (ID: {$update['id']}) with category ID: {$update['category_id']}</p>";
    } else {
        echo "<p style='color: red;'>✗ Error updating {$update['title']}: " . $conn->error . "</p>";
    }
}

// Verify the updates
echo "<h3>Verification - Current Content Status:</h3>";
$verify_query = "SELECT c.id, c.title, c.category_id, cat.name as category_name 
                 FROM content c 
                 LEFT JOIN categories cat ON c.category_id = cat.id 
                 ORDER BY c.id";
$verify_result = $conn->query($verify_query);

echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #333; color: white;'>
        <th>ID</th>
        <th>Title</th>
        <th>Category ID</th>
        <th>Category Name</th>
        <th>Status</th>
      </tr>";

while($row = $verify_result->fetch_assoc()) {
    $status = $row['category_id'] ? 
        "<span style='color: green;'>✓ Assigned</span>" : 
        "<span style='color: red;'>✗ No Category</span>";
    
    echo "<tr>
            <td>{$row['id']}</td>
            <td><strong>{$row['title']}</strong></td>
            <td>{$row['category_id']}</td>
            <td>{$row['category_name']}</td>
            <td>{$status}</td>
          </tr>";
}
echo "</table>";

echo "<br><a href='player.php?id=12&title=Rana%20Naidu' class='btn btn-primary'>Test Rana Naidu Player</a>";
?>