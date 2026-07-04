<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

// Check if input is valid
if (!$input || !isset($input['content_id']) || !isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$userId = $_SESSION['user_id'];
$contentId = $input['content_id'];
$action = $input['action'];

if ($action == 'toggle') {
    // Check if already in watchlist
    $checkStmt = $conn->prepare("SELECT id FROM watchlist WHERE user_id = ? AND content_id = ?");
    $checkStmt->bind_param("ii", $userId, $contentId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        // Remove from watchlist
        $deleteStmt = $conn->prepare("DELETE FROM watchlist WHERE user_id = ? AND content_id = ?");
        $deleteStmt->bind_param("ii", $userId, $contentId);
        if ($deleteStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Removed from watchlist', 'action' => 'removed']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error removing from watchlist']);
        }
    } else {
        // Add to watchlist
        $insertStmt = $conn->prepare("INSERT INTO watchlist (user_id, content_id) VALUES (?, ?)");
        $insertStmt->bind_param("ii", $userId, $contentId);
        if ($insertStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Added to watchlist', 'action' => 'added']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding to watchlist']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>