<?php
require_once 'includes/header.php';

// Check if admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die('Admin access required.');
}

echo "<h2>Fixing Content Video URLs</h2>";

// Sample videos to assign
$sample_videos = [
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4'
];

// Get all content that needs video URLs
$content_query = "SELECT id, title FROM content WHERE video_url IS NULL OR video_url = ''";
$content_result = $conn->query($content_query);

if ($content_result->num_rows > 0) {
    echo "<p>Found {$content_result->num_rows} content items without video URLs:</p>";
    echo "<ul>";
    
    $video_index = 0;
    while($content = $content_result->fetch_assoc()) {
        $video_url = $sample_videos[$video_index % count($sample_videos)];
        
        // Update the content
        $update_query = "UPDATE content SET 
                        video_url = '$video_url',
                        content_url = '$video_url',
                        poster_image = './assets/img/default-poster.jpg'
                        WHERE id = {$content['id']}";
        
        if ($conn->query($update_query)) {
            echo "<li style='color: green;'>✓ Updated: {$content['title']} (ID: {$content['id']}) - {$video_url}</li>";
        } else {
            echo "<li style='color: red;'>✗ Error updating {$content['title']}: " . $conn->error . "</li>";
        }
        
        $video_index++;
    }
    echo "</ul>";
    
    echo "<h3 style='color: green;'>All content has been updated with video URLs!</h3>";
} else {
    echo "<p>All content already has video URLs.</p>";
}

// Verify the updates
echo "<h3>Verification:</h3>";
$verify_query = "SELECT id, title, video_url FROM content ORDER BY id";
$verify_result = $conn->query($verify_query);

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Title</th><th>Video URL</th><th>Status</th></tr>";
while($row = $verify_result->fetch_assoc()) {
    $status = !empty($row['video_url']) ? 
        "<span style='color: green;'>✓ Has URL</span>" : 
        "<span style='color: red;'>✗ No URL</span>";
    
    echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['title']}</td>
            <td>{$row['video_url']}</td>
            <td>{$status}</td>
          </tr>";
}
echo "</table>";

echo "<br><a href='player.php?id=12&title=Rana%20Naidu' class='btn btn-primary'>Test Rana Naidu Player Now</a>";
?>