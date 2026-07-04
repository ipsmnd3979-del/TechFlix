<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content_id = intval($_POST['content_id']);
    $field = $_POST['field'];
    $value = $_POST['value'];
    
    // Validate field
    $allowed_fields = ['poster_image', 'thumbnail'];
    if (!in_array($field, $allowed_fields)) {
        echo json_encode(['success' => false, 'message' => 'Invalid field']);
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE content SET $field = ? WHERE id = ?");
    $stmt->bind_param("si", $value, $content_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Image updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update image']);
    }
    
    $stmt->close();
}
?>