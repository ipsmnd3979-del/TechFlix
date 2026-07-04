<?php
// This is an AJAX endpoint - must not include header.php (which outputs HTML)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/config.php';

header('Content-Type: application/json');

// Prevent HTML output from errors leaking into JSON response
set_error_handler(function($errno, $errstr) {
    // Silently log, never echo
    error_log("add_to_watchlist.php error [$errno]: $errstr");
    return true;
});

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add to watchlist']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$content_id = intval($_POST['content_id'] ?? 0);
$user_id    = intval($_SESSION['user_id']);

if ($content_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid content ID']);
    exit();
}

// Check if already in watchlist
$check_stmt = $conn->prepare("SELECT id FROM watchlist WHERE user_id = ? AND content_id = ?");
if (!$check_stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}
$check_stmt->bind_param("ii", $user_id, $content_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $check_stmt->close();
    echo json_encode(['success' => false, 'message' => 'Already in your watchlist']);
    exit();
}
$check_stmt->close();

// Insert into watchlist
$insert_stmt = $conn->prepare("INSERT INTO watchlist (user_id, content_id, added_at) VALUES (?, ?, NOW())");
if (!$insert_stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}
$insert_stmt->bind_param("ii", $user_id, $content_id);

if ($insert_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Added to watchlist!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add to watchlist']);
}
$insert_stmt->close();
