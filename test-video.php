<?php
$test_path = '../assets/uploads/videos/video_1761951980_690540eca1d3b.mp4';
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$project_path = dirname($_SERVER['PHP_SELF']);

$absolute_url = $base_url . $project_path . '/' . ltrim($test_path, '../');

echo "Original path: $test_path<br>";
echo "Absolute URL: $absolute_url<br>";
echo "Local path: " . $_SERVER['DOCUMENT_ROOT'] . parse_url($absolute_url, PHP_URL_PATH) . "<br>";

// Check if file exists
$local_path = $_SERVER['DOCUMENT_ROOT'] . parse_url($absolute_url, PHP_URL_PATH);
echo "File exists: " . (file_exists($local_path) ? 'Yes' : 'No');
?>