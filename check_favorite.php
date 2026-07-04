<?php
require_once 'includes/header.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['is_favorite' => false]);
    exit();
}

$content_id = isset($_GET['content_id']) ? intval($_GET['content_id']) : 0;
$user_id = intval($_SESSION['user_id']);

// Fixed raw query structural vulnerability
$stmt = $conn->prepare("SELECT id FROM user_favorites WHERE user_id = ? AND content_id = ?");
$stmt->bind_param("ii", $user_id, $content_id);
$stmt->execute();
$check_result = $stmt->get_result();

echo json_encode(['is_favorite' => $check_result->num_rows > 0]);
$stmt->close();
?>