<?php
require_once 'includes/header.php';

// Sanitize input function
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Redirect if not logged in
if (!$isLoggedIn) {
    header("Location: auth/login.php");
    exit();
}

// Ensure user data is available
if (!isset($userData) || empty($userData)) {
    // Fallback: get user data directly
    $user_id = $_SESSION['user_id'];
    $user_query = "SELECT * FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $userData = $user_result->fetch_assoc();
    $user_stmt->close();
}

// Get user's watchlist count
$watchlist_count = 0;
try {
    $watchlist_query = "SELECT COUNT(*) as count FROM watchlist WHERE user_id = ?";
    $watchlist_stmt = $conn->prepare($watchlist_query);
    $watchlist_stmt->bind_param("i", $_SESSION['user_id']);
    $watchlist_stmt->execute();
    $watchlist_result = $watchlist_stmt->get_result();
    $watchlist_data = $watchlist_result->fetch_assoc();
    $watchlist_count = $watchlist_data['count'];
    $watchlist_stmt->close();
} catch (Exception $e) {
    error_log("Watchlist count error: " . $e->getMessage());
}

// Get user's viewing history count
$history_count = 0;
try {
    $table_check = $conn->query("SHOW TABLES LIKE 'viewing_history'");
    if ($table_check->num_rows > 0) {
        $history_query = "SELECT COUNT(*) as count FROM viewing_history WHERE user_id = ?";
        $history_stmt = $conn->prepare($history_query);
        $history_stmt->bind_param("i", $_SESSION['user_id']);
        $history_stmt->execute();
        $history_result = $history_stmt->get_result();
        $history_data = $history_result->fetch_assoc();
        $history_count = $history_data['count'];
        $history_stmt->close();
    }
} catch (Exception $e) {
    error_log("History count error: " . $e->getMessage());
}

// Get continue watching items
$continue_watching = [];
try {
    $continue_query = "SELECT c.* FROM viewing_history h 
                      JOIN content c ON h.content_id = c.id 
                      WHERE h.user_id = ? 
                      ORDER BY h.watched_at DESC 
                      LIMIT 4";
    $continue_stmt = $conn->prepare($continue_query);
    $continue_stmt->bind_param("i", $_SESSION['user_id']);
    $continue_stmt->execute();
    $continue_result = $continue_stmt->get_result();
    
    while ($row = $continue_result->fetch_assoc()) {
        $continue_watching[] = $row;
    }
    $continue_stmt->close();
} catch (Exception $e) {
    error_log("Continue watching error: " . $e->getMessage());
}

// Get user's payment methods
$payment_methods = [];
try {
    // First, check if the payment_methods table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'payment_methods'");
    if ($table_check->num_rows > 0) {
        $payment_query = "SELECT * FROM payment_methods WHERE user_id = ? ORDER BY is_default DESC, created_at DESC";
        $payment_stmt = $conn->prepare($payment_query);
        $payment_stmt->bind_param("i", $_SESSION['user_id']);
        $payment_stmt->execute();
        $payment_result = $payment_stmt->get_result();
        
        while ($row = $payment_result->fetch_assoc()) {
            $payment_methods[] = $row;
        }
        $payment_stmt->close();
    }
} catch (Exception $e) {
    error_log("Payment methods error: " . $e->getMessage());
}

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $first_name = !empty($_POST['first_name']) ? sanitize_input($_POST['first_name']) : null;
        $last_name = !empty($_POST['last_name']) ? sanitize_input($_POST['last_name']) : null;
        $email = sanitize_input($_POST['email']);
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Invalid email format!";
        } else {
            $update_stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
            $update_stmt->bind_param("sssi", $first_name, $last_name, $email, $_SESSION['user_id']);
            
            if ($update_stmt->execute()) {
                $success_message = "Profile updated successfully!";
                // Update session data
                $_SESSION['email'] = $email;
                // Refresh user data
                $userData['first_name'] = $first_name;
                $userData['last_name'] = $last_name;
                $userData['email'] = $email;
            } else {
                $error_message = "Failed to update profile. Please try again.";
            }
            $update_stmt->close();
        }
    }
    
    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_picture']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = 'assets/uploads/profiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $new_filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                $update_pic_stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $update_pic_stmt->bind_param("si", $upload_path, $_SESSION['user_id']);
                if ($update_pic_stmt->execute()) {
                    $success_message = "Profile picture updated successfully!";
                    $userData['profile_picture'] = $upload_path;
                }
                $update_pic_stmt->close();
            } else {
                $error_message = "Failed to upload profile picture.";
            }
        } else {
            $error_message = "Invalid file type. Please upload JPEG, PNG, or GIF images.";
        }
    }
    
    // Handle payment method actions
    if (isset($_POST['payment_action'])) {
        $action = $_POST['payment_action'];
        
        if ($action === 'add') {
            // Add new payment method
            $card_number = sanitize_input($_POST['card_number']);
            $expiry_month = sanitize_input($_POST['expiry_month']);
            $expiry_year = sanitize_input($_POST['expiry_year']);
            $card_holder = sanitize_input($_POST['card_holder']);
            $cvv = sanitize_input($_POST['cvv']);
            
            // Basic validation
            if (empty($card_number) || empty($expiry_month) || empty($expiry_year) || empty($card_holder) || empty($cvv)) {
                $error_message = "All payment fields are required!";
            } else {
                // Mask card number for display
                $last_four = substr($card_number, -4);
                $masked_number = "**** **** **** " . $last_four;
                
                // Check if this is the first card to set as default
                $is_default = empty($payment_methods) ? 1 : 0;
                
                // Create payment_methods table if it doesn't exist
                $create_table_sql = "CREATE TABLE IF NOT EXISTS payment_methods (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    card_number VARCHAR(255) NOT NULL,
                    expiry_month VARCHAR(2) NOT NULL,
                    expiry_year VARCHAR(4) NOT NULL,
                    card_holder VARCHAR(255) NOT NULL,
                    is_default TINYINT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )";
                
                if ($conn->query($create_table_sql)) {
                    $insert_stmt = $conn->prepare("INSERT INTO payment_methods (user_id, card_number, expiry_month, expiry_year, card_holder, is_default) VALUES (?, ?, ?, ?, ?, ?)");
                    $insert_stmt->bind_param("issssi", $_SESSION['user_id'], $masked_number, $expiry_month, $expiry_year, $card_holder, $is_default);
                    
                    if ($insert_stmt->execute()) {
                        $success_message = "Payment method added successfully!";
                        // Refresh payment methods
                        $payment_stmt = $conn->prepare("SELECT * FROM payment_methods WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
                        $payment_stmt->bind_param("i", $_SESSION['user_id']);
                        $payment_stmt->execute();
                        $payment_result = $payment_stmt->get_result();
                        $payment_methods = [];
                        while ($row = $payment_result->fetch_assoc()) {
                            $payment_methods[] = $row;
                        }
                        $payment_stmt->close();
                    } else {
                        $error_message = "Failed to add payment method. Please try again.";
                    }
                    $insert_stmt->close();
                } else {
                    $error_message = "Database error. Please try again.";
                }
            }
        } elseif ($action === 'delete' && isset($_POST['payment_id'])) {
            // Delete payment method
            $payment_id = (int)$_POST['payment_id'];
            
            $delete_stmt = $conn->prepare("DELETE FROM payment_methods WHERE id = ? AND user_id = ?");
            $delete_stmt->bind_param("ii", $payment_id, $_SESSION['user_id']);
            
            if ($delete_stmt->execute()) {
                $success_message = "Payment method removed successfully!";
                // Refresh payment methods
                $payment_stmt = $conn->prepare("SELECT * FROM payment_methods WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
                $payment_stmt->bind_param("i", $_SESSION['user_id']);
                $payment_stmt->execute();
                $payment_result = $payment_stmt->get_result();
                $payment_methods = [];
                while ($row = $payment_result->fetch_assoc()) {
                    $payment_methods[] = $row;
                }
                $payment_stmt->close();
            } else {
                $error_message = "Failed to remove payment method. Please try again.";
            }
            $delete_stmt->close();
        } elseif ($action === 'set_default' && isset($_POST['payment_id'])) {
            // Set payment method as default
            $payment_id = (int)$_POST['payment_id'];
            
            // First, unset all default payment methods
            $unset_stmt = $conn->prepare("UPDATE payment_methods SET is_default = 0 WHERE user_id = ?");
            $unset_stmt->bind_param("i", $_SESSION['user_id']);
            $unset_stmt->execute();
            $unset_stmt->close();
            
            // Then set the selected one as default
            $set_stmt = $conn->prepare("UPDATE payment_methods SET is_default = 1 WHERE id = ? AND user_id = ?");
            $set_stmt->bind_param("ii", $payment_id, $_SESSION['user_id']);
            
            if ($set_stmt->execute()) {
                $success_message = "Default payment method updated successfully!";
                // Refresh payment methods
                $payment_stmt = $conn->prepare("SELECT * FROM payment_methods WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
                $payment_stmt->bind_param("i", $_SESSION['user_id']);
                $payment_stmt->execute();
                $payment_result = $payment_stmt->get_result();
                $payment_methods = [];
                while ($row = $payment_result->fetch_assoc()) {
                    $payment_methods[] = $row;
                }
                $payment_stmt->close();
            } else {
                $error_message = "Failed to update default payment method. Please try again.";
            }
            $set_stmt->close();
        }
    }
}

// Ensure user data is properly set and has required fields
if (!isset($userData) || !is_array($userData)) {
    $userData = [
        'username' => 'User',
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'profile_picture' => '',
        'created_at' => date('Y-m-d H:i:s'),
        'last_login' => null
    ];
}

// Set default values for missing user data fields
$userData['username'] = $userData['username'] ?? 'User';
$userData['first_name'] = $userData['first_name'] ?? '';
$userData['last_name'] = $userData['last_name'] ?? '';
$userData['email'] = $userData['email'] ?? '';
$userData['profile_picture'] = $userData['profile_picture'] ?? '';
$userData['created_at'] = $userData['created_at'] ?? date('Y-m-d H:i:s');
$userData['last_login'] = $userData['last_login'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFlix - Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            color: #fff;
            overflow-x: hidden;
            position: relative;
        }

        /* Galaxy Animation */
        .galaxy {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .star {
            position: absolute;
            background: #fff;
            border-radius: 50%;
            animation: twinkle var(--duration) infinite ease-in-out;
        }

        @keyframes twinkle {
            0%, 100% {
                opacity: 0.1;
            }
            50% {
                opacity: 1;
            }
        }

        @keyframes moveStar {
            from {
                transform: translateY(0);
            }
            to {
                transform: translateY(-100vh);
            }
        }

        /* Header Styles */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 5%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-icon {
            font-size: 32px;
            background: linear-gradient(45deg, #ff00cc, #3333ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo-text {
            font-family: 'Montserrat', sans-serif;
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(45deg, #ff00cc, #3333ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        nav ul {
            display: flex;
            gap: 25px;
            list-style: none;
        }

        nav a {
            color: #ddd;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
            padding: 8px 12px;
            border-radius: 20px;
        }

        nav a:hover,
        nav a.active {
            background: rgba(106, 90, 249, 0.3);
            color: #fff;
        }

        .user-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .search-bar {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 8px 15px;
            display: flex;
            align-items: center;
        }

        .search-bar input {
            background: transparent;
            border: none;
            color: white;
            outline: none;
            width: 150px;
        }

        .search-bar input::placeholder {
            color: #aaa;
        }

        .notification,
        .user-icon {
            background: rgba(255, 255, 255, 0.1);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
        }

        .notification::after {
            content: "3";
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Profile Section */
        .profile-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .profile-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            margin-bottom: 40px;
            text-align: center;
        }

        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid rgba(106, 90, 249, 0.5);
            background: linear-gradient(45deg, #6a5af9, #d66efd);
            overflow: hidden;
            position: relative;
        }

        .profile-pic img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-pic .edit-icon {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .profile-info h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .profile-info p {
            color: #bbb;
            margin-bottom: 20px;
        }

        .stats {
            display: flex;
            gap: 30px;
            margin-top: 20px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-item .number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #6a5af9;
        }

        .stat-item .label {
            color: #aaa;
            font-size: 0.9rem;
        }

        /* Profile Content */
        .profile-content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }

        @media (min-width: 768px) {
            .profile-content {
                grid-template-columns: 1fr 1fr;
            }
        }

        .card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.08);
        }

        .card h2 {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #6a5af9;
        }

        /* Subscription Card */
        .subscription-info {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .plan {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(106, 90, 249, 0.1);
            padding: 15px;
            border-radius: 15px;
        }

        .plan-details h3 {
            margin-bottom: 5px;
        }

        .plan-details p {
            color: #bbb;
            font-size: 0.9rem;
        }

        .upgrade-btn {
            background: linear-gradient(45deg, #6a5af9, #d66efd);
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        .upgrade-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(106, 90, 249, 0.5);
        }

        /* Payment Methods */
        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .payment-card {
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
        }

        .payment-card.default {
            border-color: #6a5af9;
            background: rgba(106, 90, 249, 0.1);
        }

        .payment-icon {
            width: 50px;
            height: 35px;
            background: rgba(106, 90, 249, 0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .payment-details {
            flex: 1;
        }

        .payment-details h3 {
            margin-bottom: 5px;
        }

        .payment-details p {
            color: #bbb;
            font-size: 0.9rem;
        }

        .payment-actions {
            display: flex;
            gap: 10px;
        }

        .payment-action-btn {
            background: transparent;
            border: none;
            color: #aaa;
            cursor: pointer;
            padding: 5px;
            border-radius: 5px;
            transition: 0.3s;
        }

        .payment-action-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
        }

        .default-tag {
            background: rgba(46, 213, 115, 0.2);
            color: #2ed573;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-top: 5px;
            display: inline-block;
        }

        /* Add Payment Method Form */
        .add-payment-form {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 15px;
            margin-top: 20px;
            display: none;
        }

        .add-payment-form.active {
            display: block;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #bbb;
        }

        .form-group input {
            width: 100%;
            padding: 10px 15px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            outline: none;
        }

        .form-group input:focus {
            border-color: #6a5af9;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-primary {
            background: #6a5af9;
            color: white;
        }

        .btn-primary:hover {
            background: #5a4ae9;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Preferences */
        .preferences {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }

        .pref-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 15px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
        }

        .pref-item:hover {
            background: rgba(106, 90, 249, 0.1);
            transform: translateY(-3px);
        }

        .pref-item i {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #6a5af9;
        }

        /* Devices */
        .device-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .device-item {
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 15px;
        }

        .device-icon {
            width: 50px;
            height: 50px;
            background: rgba(106, 90, 249, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .device-info h3 {
            margin-bottom: 5px;
        }

        .device-info p {
            color: #bbb;
            font-size: 0.9rem;
        }

        .active-tag {
            background: rgba(46, 213, 115, 0.2);
            color: #2ed573;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-top: 5px;
            display: inline-block;
        }

        /* Watchlist */
        .watchlist {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 20px;
        }

        .watch-item {
            border-radius: 15px;
            overflow: hidden;
            position: relative;
            height: 220px;
            cursor: pointer;
            transition: 0.3s;
        }

        .watch-item:hover {
            transform: scale(1.05);
            z-index: 10;
        }

        .watch-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .watch-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent);
            padding: 15px;
        }

        .watch-overlay h4 {
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .watch-overlay p {
            color: #bbb;
            font-size: 0.8rem;
        }

        .progress-bar {
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
            margin-top: 10px;
            overflow: hidden;
        }

        .progress {
            height: 100%;
            background: #6a5af9;
            width: 60%;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 30px;
            margin-top: 50px;
            color: #aaa;
            font-size: 0.9rem;
            background: rgba(0, 0, 0, 0.3);
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 20px 0;
        }

        .social-links a {
            color: #ddd;
            font-size: 1.2rem;
            transition: 0.3s;
        }

        .social-links a:hover {
            color: #6a5af9;
        }

        /* Responsive */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 15px;
            }

            nav ul {
                flex-wrap: wrap;
                justify-content: center;
            }

            .profile-header {
                margin-top: 20px;
            }

            .stats {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .payment-card {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .payment-actions {
                align-self: flex-end;
            }
        }
    </style>
</head>

<body>
    <div class="galaxy-bg" id="galaxy"></div>

    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-pic">
                <img src="<?php echo !empty($userData['profile_picture']) ? $userData['profile_picture'] : './assets/img/default-profile.jpg'; ?>" alt="Profile Picture">
                <div class="edit-icon" onclick="document.getElementById('profile_picture').click()">
                    <i class="fas fa-camera"></i>
                </div>
                <form id="profilePicForm" method="POST" enctype="multipart/form-data" style="display: none;">
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" onchange="document.getElementById('profilePicForm').submit();">
                </form>
            </div>
            <div class="profile-info">
                <h1><?php echo !empty($userData['first_name']) && !empty($userData['last_name']) ? $userData['first_name'] . ' ' . $userData['last_name'] : $userData['username']; ?></h1>
                <p>Member since <?php echo date('Y', strtotime($userData['created_at'])); ?></p>
                <div class="stats">
                    <div class="stat-item">
                        <div class="number"><?php echo $watchlist_count; ?></div>
                        <div class="label">In Watchlist</div>
                    </div>
                    <div class="stat-item">
                        <div class="number"><?php echo $history_count; ?></div>
                        <div class="label">Watched</div>
                    </div>
                    <div class="stat-item">
                        <div class="number"><?php echo !empty($userData['last_login']) ? date('M j, Y', strtotime($userData['last_login'])) : 'Never'; ?></div>
                        <div class="label">Last Login</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="profile-content">
            <div class="card">
                <h2><i class="fas fa-crown"></i> Subscription</h2>
                <div class="subscription-info">
                    <?php
                    // Get user's current subscription
                    $current_subscription = null;
                    try {
                        $table_check = $conn->query("SHOW TABLES LIKE 'subscriptions'");
                        if ($table_check->num_rows > 0) {
                            $sub_query = "SELECT * FROM subscriptions WHERE user_id = ? AND status = 'active' LIMIT 1";
                            $sub_stmt = $conn->prepare($sub_query);
                            $sub_stmt->bind_param("i", $_SESSION['user_id']);
                            $sub_stmt->execute();
                            $sub_result = $sub_stmt->get_result();
                            $current_subscription = $sub_result->fetch_assoc();
                            $sub_stmt->close();
                        }
                    } catch (Exception $e) {
                        error_log("Subscription error: " . $e->getMessage());
                    }
                    
                    if ($current_subscription): 
                    ?>
                        <div class="plan">
                            <div class="plan-details">
                                <h3><?php echo htmlspecialchars($current_subscription['plan_name'] ?? 'Premium'); ?>
                                    <span style="background: #2ed573; color: white; padding: 3px 10px; border-radius: 20px; font-size: 0.8rem; margin-left: 10px;">Active</span>
                                </h3>
                                <p>HD Quality • Multiple Screens • Ad-Free</p>
                            </div>
                            <button class="upgrade-btn" onclick="window.location.href='subscription_plans.php'">
                                Manage Plan
                            </button>
                        </div>
                        <div class="billing-info">
                            <p><i class="fas fa-calendar-alt"></i> Renews: <?php echo date('M j, Y', strtotime($current_subscription['end_date'] ?? '+1 month')); ?></p>
                            <p><i class="fas fa-credit-card"></i> $<?php echo $current_subscription['price'] ?? '9.99'; ?>/month</p>
                        </div>
                    <?php else: ?>
                        <div class="plan">
                            <div class="plan-details">
                                <h3>Free Plan</h3>
                                <p>Basic streaming • With Ads • Limited Access</p>
                            </div>
                            <button class="upgrade-btn" onclick="window.location.href='subscription_plans.php'">
                                Upgrade Now
                            </button>
                        </div>
                        <div class="billing-info">
                            <p><i class="fas fa-info-circle"></i> Upgrade to unlock premium features</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <h2><i class="fas fa-credit-card"></i> Payment Methods</h2>
                <div class="payment-methods">
                    <?php if (!empty($payment_methods)): ?>
                        <?php foreach ($payment_methods as $payment): ?>
                        <div class="payment-card <?php echo $payment['is_default'] ? 'default' : ''; ?>">
                            <div class="payment-icon">
                                <i class="fab fa-cc-visa"></i>
                            </div>
                            <div class="payment-details">
                                <h3><?php echo htmlspecialchars($payment['card_number']); ?></h3>
                                <p>Expires <?php echo $payment['expiry_month']; ?>/<?php echo $payment['expiry_year']; ?></p>
                                <?php if ($payment['is_default']): ?>
                                    <span class="default-tag">Default</span>
                                <?php endif; ?>
                            </div>
                            <div class="payment-actions">
                                <?php if (!$payment['is_default']): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="payment_action" value="set_default">
                                        <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                        <button type="submit" class="payment-action-btn" title="Set as default">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to remove this payment method?');">
                                    <input type="hidden" name="payment_action" value="delete">
                                    <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                    <button type="submit" class="payment-action-btn" title="Remove">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No payment methods added yet.</p>
                    <?php endif; ?>
                    
                    <button class="btn btn-secondary" id="addPaymentBtn" style="margin-top: 10px;">
                        <i class="fas fa-plus"></i> Add Payment Method
                    </button>
                    
                    <div class="add-payment-form" id="addPaymentForm">
                        <form method="POST">
                            <input type="hidden" name="payment_action" value="add">
                            <div class="form-group">
                                <label for="card_number">Card Number</label>
                                <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" required maxlength="19">
                            </div>
                            <div class="form-group">
                                <label for="card_holder">Card Holder Name</label>
                                <input type="text" id="card_holder" name="card_holder" placeholder="John Doe" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiry_month">Expiry Month</label>
                                    <input type="number" id="expiry_month" name="expiry_month" min="1" max="12" placeholder="MM" required>
                                </div>
                                <div class="form-group">
                                    <label for="expiry_year">Expiry Year</label>
                                    <input type="number" id="expiry_year" name="expiry_year" min="<?php echo date('Y'); ?>" max="<?php echo date('Y') + 10; ?>" placeholder="YYYY" required>
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV</label>
                                    <input type="text" id="cvv" name="cvv" placeholder="123" required maxlength="4">
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" id="cancelPaymentBtn">Cancel</button>
                                <button type="submit" class="btn btn-primary">Add Payment Method</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2><i class="fas fa-user-edit"></i> Profile Information</h2>
                <form method="POST">
                    <input type="hidden" name="update_profile" value="1">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($userData['first_name']); ?>" placeholder="First Name">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($userData['last_name']); ?>" placeholder="Last Name">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <h2><i class="fas fa-cog"></i> Preferences</h2>
                <div class="preferences">
                    <div class="pref-item">
                        <i class="fas fa-language"></i>
                        <p>Language</p>
                    </div>
                    <div class="pref-item">
                        <i class="fas fa-closed-captioning"></i>
                        <p>Subtitles</p>
                    </div>
                    <div class="pref-item">
                        <i class="fas fa-video"></i>
                        <p>Video Quality</p>
                    </div>
                    <div class="pref-item">
                        <i class="fas fa-bell"></i>
                        <p>Notifications</p>
                    </div>
                    <div class="pref-item">
                        <i class="fas fa-shield-alt"></i>
                        <p>Privacy</p>
                    </div>
                    <div class="pref-item">
                        <i class="fas fa-moon"></i>
                        <p>Dark Mode</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2><i class="fas fa-desktop"></i> Devices</h2>
                <div class="device-list">
                    <div class="device-item">
                        <div class="device-icon">
                            <i class="fas fa-laptop"></i>
                        </div>
                        <div class="device-info">
                            <h3>Current Device</h3>
                            <p>Last used: Today</p>
                            <span class="active-tag">Active Now</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2><i class="fas fa-history"></i> Continue Watching</h2>
                <div class="watchlist">
                    <?php if (!empty($continue_watching)): ?>
                        <?php foreach ($continue_watching as $content): ?>
                        <div class="watch-item">
                            <img src="<?php echo htmlspecialchars($content['thumbnail'] ?? $content['poster_image'] ?? './assets/img/default-thumbnail.jpg'); ?>" alt="<?php echo htmlspecialchars($content['title']); ?>">
                            <div class="watch-overlay">
                                <h4><?php echo htmlspecialchars($content['title']); ?></h4>
                                <p><?php echo $content['type'] ?? 'Movie'; ?></p>
                                <div class="progress-bar">
                                    <div class="progress"></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No recently watched content.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    

    <script>
        // Galaxy background animation
        function createGalaxy() {
            const galaxy = document.getElementById('galaxy');
            const starsCount = 150;

            for (let i = 0; i < starsCount; i++) {
                const star = document.createElement('div');
                star.classList.add('star');

                // Random position
                const x = Math.random() * 100;
                const y = Math.random() * 100;

                // Random size
                const size = Math.random() * 3;

                // Random animation duration
                const duration = 3 + Math.random() * 5;

                star.style.left = `${x}%`;
                star.style.top = `${y}%`;
                star.style.width = `${size}px`;
                star.style.height = `${size}px`;
                star.style.setProperty('--duration', `${duration}s`);

                galaxy.appendChild(star);
            }
        }

        // Payment methods form toggle
        document.getElementById('addPaymentBtn').addEventListener('click', function() {
            document.getElementById('addPaymentForm').classList.toggle('active');
        });

        document.getElementById('cancelPaymentBtn').addEventListener('click', function() {
            document.getElementById('addPaymentForm').classList.remove('active');
        });

        // Format card number input
        document.getElementById('card_number')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let matches = value.match(/\d{4,16}/g);
            let match = matches && matches[0] || '';
            let parts = [];
            
            for (let i = 0; i < match.length; i += 4) {
                parts.push(match.substring(i, i + 4));
            }
            
            if (parts.length) {
                e.target.value = parts.join(' ');
            } else {
                e.target.value = value;
            }
        });

        // Initialize
        window.addEventListener('DOMContentLoaded', function() {
            createGalaxy();
            
            // Show success/error messages
            <?php if (isset($success_message)): ?>
                setTimeout(function() {
                    alert("<?php echo $success_message; ?>");
                }, 100);
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                setTimeout(function() {
                    alert("<?php echo $error_message; ?>");
                }, 100);
            <?php endif; ?>
        });
    </script>
</body>

</html>



<?php require_once 'includes/footer.php'; ?>