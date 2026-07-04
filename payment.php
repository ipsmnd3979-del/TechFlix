<?php
// payment.php

// Initialize all variables at the top
$success = false;
$error_message = '';
$success_message = '';
$payment_methods = [];

// Database connection
try {
    require_once 'includes/header.php'; // This likely already starts the session
    require_once 'includes/SubscriptionManager.php';
    
    $subscriptionManager = new SubscriptionManager($conn);
    
} catch (Exception $e) {
    // If header.php doesn't exist, create basic database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "techflix";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }
    
    // Only start session if header.php didn't do it
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $subscriptionManager = new SubscriptionManager($conn);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

// Get user's payment methods
try {
    $payment_query = "SELECT * FROM user_payment_methods WHERE user_id = ? ORDER BY is_default DESC, created_at DESC";
    $payment_stmt = $conn->prepare($payment_query);
    $payment_stmt->bind_param("i", $_SESSION['user_id']);
    $payment_stmt->execute();
    $payment_result = $payment_stmt->get_result();
    
    while ($row = $payment_result->fetch_assoc()) {
        $payment_methods[] = $row;
    }
    $payment_stmt->close();
} catch (Exception $e) {
    error_log("Payment methods error: " . $e->getMessage());
}

// Get plan_id from URL if provided
$selected_plan_id = isset($_GET['plan_id']) ? intval($_GET['plan_id']) : 0;

// Handle payment form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['process_payment'])) {
        // Get form data
        $card_number = sanitize_input($_POST['card_number'] ?? '');
        $expiry_date = sanitize_input($_POST['expiry_date'] ?? '');
        $cvv = sanitize_input($_POST['cvv'] ?? '');
        $card_holder = sanitize_input($_POST['card_holder'] ?? '');
        $plan_id = intval($_POST['plan_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        
        // Validate input
        if (empty($card_number) || empty($expiry_date) || empty($cvv) || empty($card_holder)) {
            $error_message = "Please fill in all required fields.";
        } elseif (!validateCardNumber($card_number)) {
            $error_message = "Invalid card number.";
        } elseif (!validateExpiryDate($expiry_date)) {
            $error_message = "Invalid expiry date. Please use MM/YY format and ensure it's not expired.";
        } elseif ($plan_id == 0) {
            $error_message = "Please select a subscription plan.";
        } else {
            // Process payment (simulate payment processing)
            try {
                // Start transaction
                $conn->begin_transaction();
                
                // Get plan details
                $plan_query = "SELECT * FROM subscription_plans WHERE id = ?";
                $plan_stmt = $conn->prepare($plan_query);
                $plan_stmt->bind_param("i", $plan_id);
                $plan_stmt->execute();
                $plan = $plan_stmt->get_result()->fetch_assoc();
                $plan_stmt->close();
                
                if (!$plan) {
                    throw new Exception("Invalid subscription plan selected.");
                }
                
                // Insert payment record
                $payment_query = "INSERT INTO payments (user_id, amount, currency, payment_method, status, payment_date) 
                                 VALUES (?, ?, 'USD', 'Credit Card', 'completed', NOW())";
                $payment_stmt = $conn->prepare($payment_query);
                $payment_stmt->bind_param("id", $_SESSION['user_id'], $plan['price']);
                
                if ($payment_stmt->execute()) {
                    $payment_id = $conn->insert_id;
                    
                    // Create subscription using SubscriptionManager
                    $subscription_id = $subscriptionManager->createSubscription($_SESSION['user_id'], $plan_id, $payment_id);
                    
                    if ($subscription_id) {
                        $conn->commit();
                        $success = true;
                        $success_message = "Payment processed successfully! Your " . $plan['name'] . " subscription has been activated.";
                        
                        // Update session with new subscription status
                        $_SESSION['subscription_active'] = true;
                        $_SESSION['plan_id'] = $plan_id;
                        $_SESSION['current_plan'] = $plan['name'];
                        
                    } else {
                        throw new Exception("Failed to create subscription.");
                    }
                    
                } else {
                    throw new Exception("Payment processing failed.");
                }
                
                $payment_stmt->close();
                
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = "Payment failed: " . $e->getMessage();
                $success = false;
            }
        }
    }
    
    // Handle adding new payment method
    if (isset($_POST['add_payment_method'])) {
        $payment_type = sanitize_input($_POST['payment_type'] ?? '');
        $card_number = sanitize_input($_POST['card_number'] ?? '');
        $expiry_month = intval($_POST['expiry_month'] ?? 0);
        $expiry_year = intval($_POST['expiry_year'] ?? 0);
        $paypal_email = sanitize_input($_POST['paypal_email'] ?? '');
        
        try {
            if ($payment_type === 'credit_card') {
                // Validate credit card
                if (!validateCardNumber($card_number)) {
                    $error_message = "Invalid card number.";
                } elseif ($expiry_month < 1 || $expiry_month > 12) {
                    $error_message = "Invalid expiry month.";
                } elseif ($expiry_year < date('Y')) {
                    $error_message = "Card has expired.";
                } else {
                    // Extract card info
                    $card_last_four = substr(str_replace(' ', '', $card_number), -4);
                    $card_brand = detectCardBrand($card_number);
                    
                    // Insert credit card
                    $insert_query = "INSERT INTO user_payment_methods (user_id, payment_type, card_last_four, card_brand, expiry_month, expiry_year, is_default) 
                                    VALUES (?, 'credit_card', ?, ?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_query);
                    
                    // Set as default if no other payment methods exist
                    $is_default = empty($payment_methods) ? 1 : 0;
                    
                    $insert_stmt->bind_param("issiii", $_SESSION['user_id'], $card_last_four, $card_brand, $expiry_month, $expiry_year, $is_default);
                    
                    if ($insert_stmt->execute()) {
                        $success_message = "Credit card added successfully!";
                        // Refresh payment methods
                        header("Location: payment.php");
                        exit();
                    } else {
                        $error_message = "Failed to add credit card.";
                    }
                    $insert_stmt->close();
                }
            } elseif ($payment_type === 'paypal') {
                // Validate PayPal email
                if (!filter_var($paypal_email, FILTER_VALIDATE_EMAIL)) {
                    $error_message = "Invalid PayPal email address.";
                } else {
                    // Insert PayPal
                    $insert_query = "INSERT INTO user_payment_methods (user_id, payment_type, paypal_email, is_default) 
                                    VALUES (?, 'paypal', ?, ?)";
                    $insert_stmt = $conn->prepare($insert_query);
                    
                    // Set as default if no other payment methods exist
                    $is_default = empty($payment_methods) ? 1 : 0;
                    
                    $insert_stmt->bind_param("isi", $_SESSION['user_id'], $paypal_email, $is_default);
                    
                    if ($insert_stmt->execute()) {
                        $success_message = "PayPal account added successfully!";
                        // Refresh payment methods
                        header("Location: payment.php");
                        exit();
                    } else {
                        $error_message = "Failed to add PayPal account.";
                    }
                    $insert_stmt->close();
                }
            }
        } catch (Exception $e) {
            $error_message = "Error adding payment method: " . $e->getMessage();
        }
    }
}

// Get subscription plans
$plans = $subscriptionManager->getSubscriptionPlans();
$current_subscription = $subscriptionManager->getUserSubscription($_SESSION['user_id']);

// Helper functions
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function validateCardNumber($number) {
    // Remove any non-digit characters
    $number = preg_replace('/\D/', '', $number);
    
    // Basic validation - check if it's 13-19 digits
    if (!preg_match('/^\d{13,19}$/', $number)) {
        return false;
    }
    
    // Luhn algorithm check
    return luhnCheck($number);
}

function luhnCheck($number) {
    $sum = 0;
    $reverse = strrev($number);
    
    for ($i = 0; $i < strlen($reverse); $i++) {
        $digit = intval($reverse[$i]);
        
        if ($i % 2 == 1) {
            $digit *= 2;
            if ($digit > 9) {
                $digit -= 9;
            }
        }
        
        $sum += $digit;
    }
    
    return $sum % 10 == 0;
}

function validateExpiryDate($date) {
    if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $date, $matches)) {
        return false;
    }
    
    $month = intval($matches[1]);
    $year = intval($matches[2]) + 2000; // Assuming 20XX format
    
    // Check if date is in the future
    $currentYear = intval(date('Y'));
    $currentMonth = intval(date('m'));
    
    if ($year < $currentYear || ($year == $currentYear && $month < $currentMonth)) {
        return false;
    }
    
    return true;
}

function detectCardBrand($number) {
    $number = preg_replace('/\D/', '', $number);
    
    // Visa
    if (preg_match('/^4/', $number)) {
        return 'visa';
    }
    // Mastercard
    elseif (preg_match('/^5[1-5]/', $number)) {
        return 'mastercard';
    }
    // American Express
    elseif (preg_match('/^3[47]/', $number)) {
        return 'amex';
    }
    // Discover
    elseif (preg_match('/^6(?:011|5)/', $number)) {
        return 'discover';
    }
    
    return 'unknown';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFlix - Payment</title>
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
            0%, 100% { opacity: 0.1; }
            50% { opacity: 1; }
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

        /* Payment Container */
        .payment-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }

        @media (min-width: 992px) {
            .payment-container {
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
            font-size: 1rem;
        }

        .payment-details p {
            color: #bbb;
            font-size: 0.9rem;
        }

        .payment-menu {
            position: absolute;
            right: 15px;
            top: 50px;
            background: rgba(0, 0, 0, 0.9);
            border-radius: 8px;
            padding: 10px;
            display: none;
            flex-direction: column;
            gap: 5px;
            z-index: 10;
        }

        .payment-menu button {
            background: none;
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            text-align: left;
            white-space: nowrap;
        }

        .payment-menu button:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Payment Form */
        .payment-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            color: #ddd;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            padding: 12px 15px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 16px;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #6a5af9;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .pay-btn, .upgrade-btn {
            background: linear-gradient(45deg, #6a5af9, #d66efd);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .pay-btn:hover, .upgrade-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(106, 90, 249, 0.3);
        }

        .pay-btn:disabled {
            background: #666;
            cursor: not-allowed;
            transform: none;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-secondary {
            background: #6c757d;
        }

        /* Messages */
        .success-message {
            background: rgba(46, 213, 115, 0.2);
            border: 1px solid #2ed573;
            color: #2ed573;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .error-message {
            background: rgba(255, 71, 87, 0.2);
            border: 1px solid #ff4757;
            color: #ff4757;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        /* Subscription Plans */
        .plan-selection {
            margin-bottom: 20px;
        }

        .plan-option {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: 0.3s;
        }

        .plan-option.selected {
            border-color: #6a5af9;
            background: rgba(106, 90, 249, 0.1);
        }

        .current-plan-badge {
            background: #2ed573;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="galaxy" id="galaxy"></div>

    <div class="payment-container">
        <!-- Payment Methods Card -->
        <div class="card">
            <h2><i class="fas fa-credit-card"></i> Payment Methods</h2>
            <div class="payment-methods">
                <?php if (!empty($payment_methods)): ?>
                    <?php foreach ($payment_methods as $payment): ?>
                        <div class="payment-card">
                            <div class="payment-icon">
                                <?php if ($payment['payment_type'] == 'credit_card'): ?>
                                    <i class="fab fa-cc-<?php echo strtolower($payment['card_brand'] ?? 'visa'); ?>"></i>
                                <?php elseif ($payment['payment_type'] == 'paypal'): ?>
                                    <i class="fab fa-paypal"></i>
                                <?php else: ?>
                                    <i class="fas fa-credit-card"></i>
                                <?php endif; ?>
                            </div>
                            <div class="payment-details">
                                <h3>
                                    <?php if ($payment['payment_type'] == 'credit_card'): ?>
                                        <?php echo ucfirst($payment['card_brand'] ?? 'Card'); ?> ending in <?php echo $payment['card_last_four']; ?>
                                    <?php elseif ($payment['payment_type'] == 'paypal'): ?>
                                        PayPal - <?php echo $payment['paypal_email']; ?>
                                    <?php endif; ?>
                                    <?php if ($payment['is_default']): ?>
                                        <span style="background: #2ed573; color: white; padding: 2px 8px; border-radius: 20px; font-size: 0.7rem; margin-left: 10px;">Default</span>
                                    <?php endif; ?>
                                </h3>
                                <p>
                                    <?php if ($payment['payment_type'] == 'credit_card'): ?>
                                        Expires <?php echo str_pad($payment['expiry_month'], 2, '0', STR_PAD_LEFT); ?>/<?php echo $payment['expiry_year']; ?>
                                    <?php else: ?>
                                        Connected
                                    <?php endif; ?>
                                </p>
                            </div>
                            <i class="fas fa-ellipsis-v" onclick="togglePaymentMenu(this)" style="cursor: pointer;"></i>
                            <div class="payment-menu">
                                <button onclick="setDefaultPayment(<?php echo $payment['id']; ?>)">Set as Default</button>
                                <button onclick="removePayment(<?php echo $payment['id']; ?>)" style="color: #ff4757;">Remove</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #bbb;">
                        <i class="fas fa-credit-card" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                        <h3>No Payment Methods</h3>
                        <p>Add a payment method to get started</p>
                    </div>
                <?php endif; ?>
                
                <button class="upgrade-btn" onclick="showAddPaymentModal()" style="margin-top: 15px;">
                    <i class="fas fa-plus"></i> Add Payment Method
                </button>
            </div>
        </div>

        <!-- Subscription Payment Card -->
        <div class="card">
            <h2><i class="fas fa-crown"></i> Subscription Payment</h2>
            
            <?php if (isset($success_message) && !empty($success_message)): ?>
                <div class="success-message">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message) && !empty($error_message)): ?>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-check-circle" style="font-size: 4rem; color: #2ed573; margin-bottom: 20px;"></i>
                    <h3>Payment Successful!</h3>
                    <p>Your subscription has been activated. You can now enjoy premium features.</p>
                    <a href="index.php" class="upgrade-btn" style="margin-top: 20px;">Go to Home</a>
                </div>
            <?php else: ?>
                <div class="plan-selection">
                    <h3>Select Plan</h3>
                    <?php foreach ($plans as $plan): ?>
                        <div class="plan-option <?php echo $current_subscription && $current_subscription['plan_id'] == $plan['id'] ? 'selected' : ''; ?>" 
                             onclick="selectPlan(<?php echo $plan['id']; ?>, <?php echo $plan['price']; ?>, '<?php echo $plan['name']; ?>')">
                            <input type="radio" name="plan_id" value="<?php echo $plan['id']; ?>" 
                                   <?php echo ($selected_plan_id == $plan['id'] || ($current_subscription && $current_subscription['plan_id'] == $plan['id'])) ? 'checked' : ''; ?> 
                                   style="display: none;">
                            <h4>
                                <?php echo $plan['name']; ?>
                                <?php if ($current_subscription && $current_subscription['plan_id'] == $plan['id']): ?>
                                    <span class="current-plan-badge">Current</span>
                                <?php endif; ?>
                            </h4>
                            <p>$<?php echo $plan['price']; ?>/month</p>
                            <small><?php echo $plan['description']; ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>

                
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Payment Method Modal -->
    <div id="addPaymentModal" class="modal">
        <div class="modal-content">
            <h2>Add Payment Method</h2>
            <form id="addPaymentForm" method="POST" action="">
                <div class="form-group">
                    <label>Payment Type</label>
                    <select name="payment_type" id="paymentTypeSelect" required>
                        <option value="credit_card">Credit Card</option>
                        <option value="paypal">PayPal</option>
                    </select>
                </div>
                
                <div id="creditCardFields">
                    <div class="form-group">
                        <label>Card Number</label>
                        <input type="text" name="card_number" placeholder="1234 5678 9012 3456">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Expiry Month</label>
                            <input type="number" name="expiry_month" placeholder="MM" min="1" max="12">
                        </div>
                        <div class="form-group">
                            <label>Expiry Year</label>
                            <input type="number" name="expiry_year" placeholder="YYYY" min="<?php echo date('Y'); ?>">
                        </div>
                    </div>
                </div>
                
                <div id="paypalFields" style="display: none;">
                    <div class="form-group">
                        <label>PayPal Email</label>
                        <input type="email" name="paypal_email" placeholder="your@email.com">
                    </div>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" class="upgrade-btn btn-secondary" onclick="hideAddPaymentModal()">Cancel</button>
                    <button type="submit" name="add_payment_method" class="upgrade-btn">Add Payment Method</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Galaxy background animation
        function createGalaxy() {
            const galaxy = document.getElementById('galaxy');
            if (!galaxy) return;
            
            const starsCount = 200;

            for (let i = 0; i < starsCount; i++) {
                const star = document.createElement('div');
                star.classList.add('star');

                const posX = Math.random() * 100;
                const posY = Math.random() * 100;
                const size = Math.random() * 2.5 + 0.5;
                const duration = Math.random() * 5 + 3;

                star.style.left = `${posX}%`;
                star.style.top = `${posY}%`;
                star.style.width = `${size}px`;
                star.style.height = `${size}px`;
                star.style.setProperty('--duration', `${duration}s`);

                galaxy.appendChild(star);
            }
        }

        let selectedPlanPrice = 0;

        function selectPlan(planId, price, planName) {
            // Remove selected class from all plans
            document.querySelectorAll('.plan-option').forEach(plan => {
                plan.classList.remove('selected');
            });
            
            // Add selected class to clicked plan
            event.currentTarget.classList.add('selected');
            
            // Check the radio button
            const radio = event.currentTarget.querySelector('input[type="radio"]');
            radio.checked = true;
            
            // Update form values
            document.getElementById('plan_id').value = planId;
            selectedPlanPrice = price;
            document.getElementById('amount').value = price;
            document.getElementById('displayAmount').textContent = price.toFixed(2);
            
            // Enable pay button
            document.getElementById('payButton').disabled = false;
        }
        
        // Format card number
        const cardNumberInput = document.getElementById('card_number');
        if (cardNumberInput) {
            cardNumberInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
                let matches = value.match(/\d{4,16}/g);
                let match = matches ? matches[0] : '';
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
        }
        
        // Format expiry date
        const expiryDateInput = document.getElementById('expiry_date');
        if (expiryDateInput) {
            expiryDateInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length >= 2) {
                    e.target.value = value.substring(0, 2) + '/' + value.substring(2, 4);
                }
            });
        }
        
        // Format CVV
        const cvvInput = document.getElementById('cvv');
        if (cvvInput) {
            cvvInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
            });
        }
        
        // Auto-select plan if coming from subscription page
        <?php if ($selected_plan_id): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const planOption = document.querySelector(`.plan-option input[value="<?php echo $selected_plan_id; ?>"]`);
            if (planOption) {
                planOption.closest('.plan-option').click();
            }
        });
        <?php endif; ?>

        // Payment methods functionality
        function togglePaymentMenu(element) {
            const menu = element.nextElementSibling;
            const allMenus = document.querySelectorAll('.payment-menu');
            
            // Close all other menus
            allMenus.forEach(m => {
                if (m !== menu) m.style.display = 'none';
            });
            
            // Toggle current menu
            menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
        }

        function showAddPaymentModal() {
            document.getElementById('addPaymentModal').style.display = 'flex';
        }

        function hideAddPaymentModal() {
            document.getElementById('addPaymentModal').style.display = 'none';
        }

        function setDefaultPayment(paymentId) {
            fetch('set_default_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `payment_id=${paymentId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Default payment method updated!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Error updating payment method', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error updating payment method', 'error');
            });
        }

        function removePayment(paymentId) {
            if (confirm('Are you sure you want to remove this payment method?')) {
                fetch('remove_payment_method.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `payment_id=${paymentId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Payment method removed!', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification(data.message || 'Error removing payment method', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error removing payment method', 'error');
                });
            }
        }

        // Toggle payment type fields in modal
        document.addEventListener('DOMContentLoaded', function() {
            const paymentTypeSelect = document.getElementById('paymentTypeSelect');
            if (paymentTypeSelect) {
                paymentTypeSelect.addEventListener('change', function() {
                    const creditCardFields = document.getElementById('creditCardFields');
                    const paypalFields = document.getElementById('paypalFields');
                    
                    if (this.value === 'credit_card') {
                        creditCardFields.style.display = 'block';
                        paypalFields.style.display = 'none';
                    } else if (this.value === 'paypal') {
                        creditCardFields.style.display = 'none';
                        paypalFields.style.display = 'block';
                    }
                });
            }
            
            // Close payment menus when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.payment-menu') && !e.target.matches('.fa-ellipsis-v')) {
                    document.querySelectorAll('.payment-menu').forEach(menu => {
                        menu.style.display = 'none';
                    });
                }
                
                // Close modal when clicking outside
                if (e.target.id === 'addPaymentModal') {
                    hideAddPaymentModal();
                }
            });
        });

        // Notification function
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 8px;
                color: white;
                z-index: 10000;
                font-weight: 600;
                transition: all 0.3s ease;
                background: ${type === 'success' ? '#2ed573' : '#ff4757'};
            `;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }
        
        // Initialize the page
        document.addEventListener('DOMContentLoaded', function () {
            createGalaxy();
        });
    </script>
</body>
</html>