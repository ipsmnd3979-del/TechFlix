<?php
session_start();
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['payment_id'])) {
    $payment_id = intval($_POST['payment_id']);
    $user_id = $_SESSION['user_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM user_payment_methods WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $payment_id, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete payment method']);
        }
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>