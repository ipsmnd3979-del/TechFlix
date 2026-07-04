<?php
require_once 'includes/header.php';

// Check if admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die('Admin access required.');
}

$content_id = 12;

// Update the video URL
$update_query = "UPDATE content SET 
    video_url = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
    poster_image = './assets/img/default-poster.jpg',
    description = 'An action-packed drama series featuring intense performances and gripping storyline.',
    rating = 8.2,
    duration = 45,
    release_year = 2023,
    updated_at = NOW()
WHERE id = $content_id";

if ($conn->query($update_query)) {
    echo "<div style='padding: 20px; background: green; color: white;'>";
    echo "SUCCESS: Rana Naidu video URL updated!";
    echo "</div>";
    
    // Verify the update
    $check_query = "SELECT id, title, video_url FROM content WHERE id = $content_id";
    $result = $conn->query($check_query);
    $content = $result->fetch_assoc();
    
    echo "<div style='padding: 20px;'>";
    echo "<h3>Updated Content:</h3>";
    echo "<pre>";
    print_r($content);
    echo "</pre>";
    echo "<a href='player.php?id=$content_id&title=Rana%20Naidu' class='btn btn-primary'>Test Player Now</a>";
    echo "</div>";
} else {
    echo "<div style='padding: 20px; background: red; color: white;'>";
    echo "ERROR: " . $conn->error;
    echo "</div>";
}
?>