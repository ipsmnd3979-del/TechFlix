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
        $conn->begin_transaction();
        
        // Reset all payment methods to not default
        $reset_stmt = $conn->prepare("UPDATE user_payment_methods SET is_default = 0 WHERE user_id = ?");
        $reset_stmt->bind_param("i", $user_id);
        $reset_stmt->execute();
        $reset_stmt->close();
        
        // Set the selected payment method as default
        $set_stmt = $conn->prepare("UPDATE user_payment_methods SET is_default = 1 WHERE id = ? AND user_id = ?");
        $set_stmt->bind_param("ii", $payment_id, $user_id);
        $set_stmt->execute();
        $set_stmt->close();
        
        $conn->commit();
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>