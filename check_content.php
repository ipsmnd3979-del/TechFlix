<?php
require_once 'includes/header.php';

echo "<h2>Content Video Status Check</h2>";

$check_query = "SELECT id, title, video_url, content_url FROM content ORDER BY id";
$result = $conn->query($check_query);

echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #333; color: white;'>
        <th>ID</th>
        <th>Title</th>
        <th>Video URL</th>
        <th>Content URL</th>
        <th>Status</th>
        <th>Test</th>
      </tr>";

while($row = $result->fetch_assoc()) {
    $has_video = !empty($row['video_url']) || !empty($row['content_url']);
    $status = $has_video ? 
        "<span style='color: green; font-weight: bold;'>✓ READY</span>" : 
        "<span style='color: red; font-weight: bold;'>✗ NO VIDEO</span>";
    
    $test_link = $has_video ? 
        "<a href='player.php?id=" . intval($row['id']) . "&title=" . urlencode($row['title']) . "' style='color: blue;'>Test Play</a>" : 
        "<span style='color: gray;'>No Video</span>";
    
    echo "<tr style='background: " . ($has_video ? '#f0fff0' : '#fff0f0') . ";'>
            <td>" . intval($row['id']) . "</td>
            <td><strong>" . htmlspecialchars($row['title']) . "</strong></td>
            <td>" . htmlspecialchars($row['video_url']) . "</td>
            <td>" . htmlspecialchars($row['content_url']) . "</td>
            <td>{$status}</td>
            <td>{$test_link}</td>
          </tr>";
}
echo "</table>";

echo "<br><a href='fix_content_videos.php' class='btn btn-primary'>Fix All Video URLs</a>";
?>