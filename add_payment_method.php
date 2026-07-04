<?php
// add_payment_method.php
session_start();
require_once 'includes/header.php';

// Global connection check validation patch
if (!isset($conn)) {
    require_once 'includes/config.php';
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$success = false;
$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_type = sanitize_input($_POST['payment_type'] ?? '');
    $card_number = sanitize_input($_POST['card_number'] ?? '');
    $expiry_month = intval($_POST['expiry_month'] ?? 0);
    $expiry_year = intval($_POST['expiry_year'] ?? 0);
    $cvv = sanitize_input($_POST['cvv'] ?? '');
    $card_holder = sanitize_input($_POST['card_holder'] ?? '');
    $paypal_email = sanitize_input($_POST['paypal_email'] ?? '');

    try {
        if ($payment_type === 'credit_card') {
            // Validate credit card inputs
            if (empty($card_number) || empty($card_holder) || $expiry_month == 0 || $expiry_year == 0 || empty($cvv)) {
                $error_message = "Please fill in all credit card details.";
            } elseif (!validateCardNumber($card_number)) {
                $error_message = "Invalid card number.";
            } elseif ($expiry_month < 1 || $expiry_month > 12) {
                $error_message = "Invalid expiry month.";
            } elseif ($expiry_year < intval(date('Y'))) {
                $error_message = "Card has expired.";
            } elseif (strlen($cvv) < 3 || strlen($cvv) > 4) {
                $error_message = "Invalid CVV code.";
            } else {
                // Extract card information
                $card_last_four = substr(str_replace(' ', '', $card_number), -4);
                $card_brand = detectCardBrand($card_number);
                
                // Check if this card already exists for the user
                $check_stmt = $conn->prepare("SELECT id FROM user_payment_methods WHERE user_id = ? AND payment_type = 'credit_card' AND card_last_four = ? AND card_brand = ?");
                $check_stmt->bind_param("iss", $_SESSION['user_id'], $card_last_four, $card_brand);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    $error_message = "This credit card is already added to your account.";
                } else {
                    // Set as default if no other payment methods exist
                    $is_default = 0;
                    $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_payment_methods WHERE user_id = ?");
                    $count_stmt->bind_param("i", $_SESSION['user_id']);
                    $count_stmt->execute();
                    $count_result = $count_stmt->get_result()->fetch_assoc();
                    $count_stmt->close();
                    
                    if ($count_result['count'] == 0) {
                        $is_default = 1;
                    }
                    
                    // Insert the credit card
                    $insert_stmt = $conn->prepare("INSERT INTO user_payment_methods (user_id, payment_type, card_last_four, card_brand, expiry_month, expiry_year, is_default) VALUES (?, 'credit_card', ?, ?, ?, ?, ?)");
                    $insert_stmt->bind_param("issiii", $_SESSION['user_id'], $card_last_four, $card_brand, $expiry_month, $expiry_year, $is_default);
                    
                    if ($insert_stmt->execute()) {
                        $success = true;
                        $success_message = "Credit card added successfully!";
                    } else {
                        $error_message = "Failed to add credit card. Please try again.";
                    }
                    $insert_stmt->close();
                }
                $check_stmt->close();
            }
        } 
        elseif ($payment_type === 'paypal') {
            // Validate PayPal inputs
            if (empty($paypal_email)) {
                $error_message = "Please enter your PayPal email address.";
            } elseif (!filter_var($paypal_email, FILTER_VALIDATE_EMAIL)) {
                $error_message = "Please enter a valid email address.";
            } else {
                // Check if this PayPal account already exists for the user
                $check_stmt = $conn->prepare("SELECT id FROM user_payment_methods WHERE user_id = ? AND payment_type = 'paypal' AND paypal_email = ?");
                $check_stmt->bind_param("is", $_SESSION['user_id'], $paypal_email);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    $error_message = "This PayPal account is already added to your account.";
                } else {
                    // Set as default if no other payment methods exist
                    $is_default = 0;
                    $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_payment_methods WHERE user_id = ?");
                    $count_stmt->bind_param("i", $_SESSION['user_id']);
                    $count_stmt->execute();
                    $count_result = $count_stmt->get_result()->fetch_assoc();
                    $count_stmt->close();
                    
                    if ($count_result['count'] == 0) {
                        $is_default = 1;
                    }
                    
                    // Insert the PayPal account
                    $insert_stmt = $conn->prepare("INSERT INTO user_payment_methods (user_id, payment_type, paypal_email, is_default) VALUES (?, 'paypal', ?, ?)");
                    $insert_stmt->bind_param("isi", $_SESSION['user_id'], $paypal_email, $is_default);
                    
                    if ($insert_stmt->execute()) {
                        $success = true;
                        $success_message = "PayPal account added successfully!";
                    } else {
                        $error_message = "Failed to add PayPal account. Please try again.";
                    }
                    $insert_stmt->close();
                }
                $check_stmt->close();
            }
        } 
        else {
            $error_message = "Invalid payment type selected.";
        }
    } catch (Exception $e) {
        $error_message = "An error occurred: " . $e->getMessage();
    }
}

// Helper functions
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function validateCardNumber($number) {
    $number = preg_replace('/\D/', '', $number);
    if (!preg_match('/^\d{13,19}$/', $number)) {
        return false;
    }
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

function detectCardBrand($number) {
    $number = preg_replace('/\D/', '', $number);
    if (preg_match('/^4/', $number)) return 'visa';
    elseif (preg_match('/^5[1-5]/', $number)) return 'mastercard';
    elseif (preg_match('/^3[47]/', $number)) return 'amex';
    elseif (preg_match('/^6(?:011|5)/', $number)) return 'discover';
    elseif (preg_match('/^3(?:0[0-5]|[68])/', $number)) return 'diners';
    elseif (preg_match('/^(?:2131|1800|35)/', $number)) return 'jcb';
    return 'unknown';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Payment Method - TechFlix</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { min-height: 100vh; background: linear-gradient(135deg, #0f0c29, #302b63, #24243e); color: #fff; overflow-x: hidden; position: relative; }
        .galaxy { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; }
        .star { position: absolute; background: #fff; border-radius: 50%; animation: twinkle var(--duration) infinite ease-in-out; }
        @keyframes twinkle { 0%, 100% { opacity: 0.1; } 50% { opacity: 1; } }
        header { display: flex; justify-content: space-between; align-items: center; padding: 20px 5%; background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(10px); position: sticky; top: 0; z-index: 100; }
        .logo { display: flex; align-items: center; gap: 10px; }
        .logo-icon { font-size: 32px; background: linear-gradient(45deg, #ff00cc, #3333ff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .logo-text { font-family: 'Montserrat', sans-serif; font-size: 28px; font-weight: 800; background: linear-gradient(45deg, #ff00cc, #3333ff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        nav ul { display: flex; gap: 25px; list-style: none; }
        nav a { color: #ddd; text-decoration: none; font-weight: 500; transition: 0.3s; padding: 8px 12px; border-radius: 20px; }
        nav a:hover, nav a.active { background: rgba(106, 90, 249, 0.3); color: #fff; }
        .user-actions { display: flex; gap: 15px; align-items: center; }
        .container { max-width: 600px; margin: 40px auto; padding: 0 20px; }
        .card { background: rgba(255, 255, 255, 0.05); border-radius: 20px; padding: 30px; backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .card h1 { margin-bottom: 10px; display: flex; align-items: center; gap: 10px; color: #6a5af9; }
        .card p { color: #bbb; margin-bottom: 30px; }
        .payment-form { display: flex; flex-direction: column; gap: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group label { color: #ddd; font-weight: 500; }
        .form-group input, .form-group select { padding: 12px 15px; border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.2); background: rgba(255, 255, 255, 0.1); color: white; font-size: 16px; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #6a5af9; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .btn { background: linear-gradient(45deg, #6a5af9, #d66efd); color: white; border: none; padding: 15px 30px; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.3s; text-align: center; text-decoration: none; display: inline-block; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(106, 90, 249, 0.3); }
        .btn-secondary { background: #6c757d; }
        .btn-full { width: 100%; }
        .button-group { display: flex; gap: 15px; margin-top: 20px; }
        .success-message { background: rgba(46, 213, 115, 0.2); border: 1px solid #2ed573; color: #2ed573; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        .error-message { background: rgba(255, 71, 87, 0.2); border: 1px solid #ff4757; color: #ff4757; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        .card-icons { display: flex; gap: 10px; margin-top: 10px; }
        .card-icon { width: 40px; height: 25px; background: rgba(255, 255, 255, 0.1); border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 12px; }
        .success-state { text-align: center; padding: 40px 20px; }
        .success-state i { font-size: 4rem; color: #2ed573; margin-bottom: 20px; }
        .success-state h2 { margin-bottom: 15px; color: #2ed573; }
        .success-state p { margin-bottom: 30px; color: #bbb; }
    </style>
</head>
<body>
    <div class="galaxy" id="galaxy"></div>
    <header>
        <div class="logo">
            <div class="logo-icon"><i class="fas fa-play-circle"></i></div>
            <div class="logo-text">TECHFLIX</div>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="browse.php">Browse</a></li>
                <li><a href="subscription_plans.php">Plans</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="payment.php" class="active">Payment</a></li>
            </ul>
        </nav>
        <div class="user-actions">
            <div class="search-bar">
                <input type="text" placeholder="Search...">
                <i class="fas fa-search"></i>
            </div>
            <div class="notification"><i class="fas fa-bell"></i></div>
            <div class="user-icon"><i class="fas fa-user"></i></div>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <?php if ($success): ?>
                <div class="success-state">
                    <i class="fas fa-check-circle"></i>
                    <h2>Payment Method Added!</h2>
                    <p><?php echo htmlspecialchars($success_message); ?></p>
                    <div class="button-group">
                        <a href="payment.php" class="btn btn-full">Back to Payment Methods</a>
                    </div>
                </div>
            <?php else: ?>
                <h1><i class="fas fa-credit-card"></i> Add Payment Method</h1>
                <p>Add a new payment method to your TechFlix account</p>

                <?php if (!empty($error_message)): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form class="payment-form" method="POST" action="">
                    <div class="form-group">
                        <label for="payment_type">Payment Type</label>
                        <select name="payment_type" id="payment_type" required onchange="togglePaymentFields()">
                            <option value="">Select Payment Type</option>
                            <option value="credit_card" <?php echo (isset($_POST['payment_type']) && $_POST['payment_type'] == 'credit_card') ? 'selected' : ''; ?>>Credit Card</option>
                            <option value="paypal" <?php echo (isset($_POST['payment_type']) && $_POST['payment_type'] == 'paypal') ? 'selected' : ''; ?>>PayPal</option>
                        </select>
                    </div>

                    <div id="credit_card_fields" style="display: none;">
                        <div class="form-group">
                            <label for="card_holder">Card Holder Name</label>
                            <input type="text" id="card_holder" name="card_holder" placeholder="John Doe" value="<?php echo isset($_POST['card_holder']) ? htmlspecialchars($_POST['card_holder']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="card_number">Card Number</label>
                            <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" value="<?php echo isset($_POST['card_number']) ? htmlspecialchars($_POST['card_number']) : ''; ?>">
                            <div class="card-icons">
                                <div class="card-icon"><i class="fab fa-cc-visa"></i></div>
                                <div class="card-icon"><i class="fab fa-cc-mastercard"></i></div>
                                <div class="card-icon"><i class="fab fa-cc-amex"></i></div>
                                <div class="card-icon"><i class="fab fa-cc-discover"></i></div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="expiry_month">Expiry Month</label>
                                <select name="expiry_month" id="expiry_month">
                                    <option value="">Month</option>
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo (isset($_POST['expiry_month']) && $_POST['expiry_month'] == $i) ? 'selected' : ''; ?>>
                                            <?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="expiry_year">Expiry Year</label>
                                <select name="expiry_year" id="expiry_year">
                                    <option value="">Year</option>
                                    <?php for ($i = intval(date('Y')); $i <= intval(date('Y')) + 15; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo (isset($_POST['expiry_year']) && $_POST['expiry_year'] == $i) ? 'selected' : ''; ?>>
                                            <?php echo $i; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV Code</label>
                            <input type="text" id="cvv" name="cvv" placeholder="123" maxlength="4" value="<?php echo isset($_POST['cvv']) ? htmlspecialchars($_POST['cvv']) : ''; ?>">
                        </div>
                    </div>

                    <div id="paypal_fields" style="display: none;">
                        <div class="form-group">
                            <label for="paypal_email">PayPal Email Address</label>
                            <input type="email" id="paypal_email" name="paypal_email" placeholder="your@email.com" value="<?php echo isset($_POST['paypal_email']) ? htmlspecialchars($_POST['paypal_email']) : ''; ?>">
                        </div>
                        <div style="background: rgba(255,255,255,0.05); padding: 15px; border-radius: 10px; margin-top: 10px;">
                            <p style="color: #bbb; font-size: 0.9rem;">
                                <i class="fab fa-paypal" style="color: #0070ba;"></i> 
                                You'll be redirected to PayPal to securely log in and authorize this payment method.
                            </p>
                        </div>
                    </div>

                    <div class="button-group">
                        <a href="payment.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn">Add Payment Method</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
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

        function togglePaymentFields() {
            const paymentType = document.getElementById('payment_type').value;
            const creditCardFields = document.getElementById('credit_card_fields');
            const paypalFields = document.getElementById('paypal_fields');
            creditCardFields.style.display = 'none';
            paypalFields.style.display = 'none';
            if (paymentType === 'credit_card') {
                creditCardFields.style.display = 'block';
            } else if (paymentType === 'paypal') {
                paypalFields.style.display = 'block';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
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
                    e.target.value = parts.length ? parts.join(' ') : value;
                });
            }

            const cvvInput = document.getElementById('cvv');
            if (cvvInput) {
                cvvInput.addEventListener('input', function(e) {
                    e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
                });
            }

            const paymentType = document.getElementById('payment_type').value;
            if (paymentType) togglePaymentFields();
            createGalaxy();
        });

        document.querySelector('form').addEventListener('submit', function(e) {
            const paymentType = document.getElementById('payment_type').value;
            if (!paymentType) {
                e.preventDefault();
                alert('Please select a payment type.');
                return;
            }
            if (paymentType === 'credit_card') {
                const cardHolder = document.getElementById('card_holder').value;
                const cardNumber = document.getElementById('card_number').value;
                const expiryMonth = document.getElementById('expiry_month').value;
                const expiryYear = document.getElementById('expiry_year').value;
                const cvv = document.getElementById('cvv').value;

                if (!cardHolder || !cardNumber || !expiryMonth || !expiryYear || !cvv) {
                    e.preventDefault();
                    alert('Please fill in all credit card details.');
                    return;
                }
                const cleanCardNumber = cardNumber.replace(/\s/g, '');
                if (cleanCardNumber.length < 13 || cleanCardNumber.length > 19) {
                    e.preventDefault();
                    alert('Please enter a valid card number (13-19 digits).');
                    return;
                }
                if (cvv.length < 3 || cvv.length > 4) {
                    e.preventDefault();
                    alert('Please enter a valid CVV code (3-4 digits).');
                    return;
                }
            } else if (paymentType === 'paypal') {
                const paypalEmail = document.getElementById('paypal_email').value;
                if (!paypalEmail) {
                    e.preventDefault();
                    alert('Please enter your PayPal email address.');
                    return;
                }
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(paypalEmail)) {
                    e.preventDefault();
                    alert('Please enter a valid email address.');
                    return;
                }
            }
        });
    </script>
</body>
</html>