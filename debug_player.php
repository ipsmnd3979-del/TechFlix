<?php
require_once 'includes/header.php';

$content_id = 12;
echo "<h2>Debugging Content ID: $content_id (Rana Naidu)</h2>";

// Check if content exists
$content_query = "SELECT * FROM content WHERE id = $content_id";
$content_result = $conn->query($content_query);
$content = $content_result->fetch_assoc();

if (!$content) {
    echo "<p style='color: red;'>ERROR: Content with ID $content_id not found in database!</p>";
    exit();
}

echo "<h3>Content Details:</h3>";
echo "<pre>";
print_r($content);
echo "</pre>";

// Check video URL
$video_url = $content['video_url'];
echo "<h3>Video URL Analysis:</h3>";
echo "<p>Video URL: " . ($video_url ?: 'NULL/EMPTY') . "</p>";

if (empty($video_url)) {
    echo "<p style='color: red;'>ERROR: No video URL set for this content!</p>";
} else {
    // Test if video URL is accessible
    echo "<p>Testing video URL accessibility...</p>";
    $headers = @get_headers($video_url);
    if ($headers && strpos($headers[0], '200')) {
        echo "<p style='color: green;'>SUCCESS: Video URL is accessible</p>";
    } else {
        echo "<p style='color: red;'>ERROR: Video URL is NOT accessible</p>";
        echo "<p>Response: " . ($headers ? $headers[0] : 'No response') . "</p>";
    }
}

// Check if video file exists locally
if ($video_url && strpos($video_url, './') === 0) {
    $local_path = str_replace('./', '', $video_url);
    if (file_exists($local_path)) {
        echo "<p style='color: green;'>Local file exists: $local_path</p>";
    } else {
        echo "<p style='color: red;'>Local file NOT found: $local_path</p>";
    }
}
?>