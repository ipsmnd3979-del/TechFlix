<?php
require_once 'includes/header.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add favorites']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content_id = intval($_POST['content_id']);
    $user_id = $_SESSION['user_id'];
    
    // Check if already in favorites
    $check_query = "SELECT * FROM user_favorites WHERE user_id = $user_id AND content_id = $content_id";
    $check_result = $conn->query($check_query);
    
    if ($check_result->num_rows > 0) {
        // Remove from favorites
        $delete_query = "DELETE FROM user_favorites WHERE user_id = $user_id AND content_id = $content_id";
        if ($conn->query($delete_query)) {
            echo json_encode(['success' => true, 'is_favorite' => false, 'message' => 'Removed from favorites']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    } else {
        // Add to favorites
        $insert_query = "INSERT INTO user_favorites (user_id, content_id) VALUES ($user_id, $content_id)";
        if ($conn->query($insert_query)) {
            echo json_encode(['success' => true, 'is_favorite' => true, 'message' => 'Added to favorites']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    }
}
?>