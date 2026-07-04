<?php
// subscription_management.php
session_start();
require_once '../includes/config.php';

// Check admin access
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    // For testing - allow access without login
    // header("Location: login.php");
    // exit();
}

// Create subscription tables if they don't exist
function createSubscriptionTables($conn) {
    // Subscription plans table
    $check_plans = $conn->query("SHOW TABLES LIKE 'subscription_plans'");
    if ($check_plans->num_rows == 0) {
        $create_plans = "CREATE TABLE subscription_plans (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            duration_days INT(11) NOT NULL,
            features TEXT,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if (!$conn->query($create_plans)) {
            throw new Exception("Failed to create subscription_plans table: " . $conn->error);
        }
        
        // Insert default plans
        $default_plans = [
            ['Basic', 'Basic streaming with ads', 4.99, 30, 'HD Streaming,1 Device,Ad-supported'],
            ['Standard', 'Standard streaming experience', 9.99, 30, 'HD Streaming,2 Devices,Ad-free,Download content'],
            ['Premium', 'Premium 4K streaming', 14.99, 30, '4K Streaming,4 Devices,Ad-free,Download content,Early access']
        ];
        
        $stmt = $conn->prepare("INSERT INTO subscription_plans (name, description, price, duration_days, features) VALUES (?, ?, ?, ?, ?)");
        foreach ($default_plans as $plan) {
            $stmt->bind_param("ssdis", ...$plan);
            $stmt->execute();
        }
        $stmt->close();
    }
    
    // User subscriptions table
    $check_subscriptions = $conn->query("SHOW TABLES LIKE 'user_subscriptions'");
    if ($check_subscriptions->num_rows == 0) {
        $create_subscriptions = "CREATE TABLE user_subscriptions (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) NOT NULL,
            plan_id INT(11) NOT NULL,
            status ENUM('active', 'canceled', 'expired', 'pending') DEFAULT 'pending',
            start_date DATE,
            end_date DATE,
            auto_renew TINYINT(1) DEFAULT 1,
            payment_method VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (plan_id) REFERENCES subscription_plans(id) ON DELETE CASCADE
        )";
        
        if (!$conn->query($create_subscriptions)) {
            throw new Exception("Failed to create user_subscriptions table: " . $conn->error);
        }
    }
    
    // Payments table
    $check_payments = $conn->query("SHOW TABLES LIKE 'payments'");
    if ($check_payments->num_rows == 0) {
        $create_payments = "CREATE TABLE payments (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            subscription_id INT(11) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            payment_method VARCHAR(50),
            transaction_id VARCHAR(100),
            status ENUM('completed', 'pending', 'failed', 'refunded') DEFAULT 'pending',
            payment_date TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (subscription_id) REFERENCES user_subscriptions(id) ON DELETE CASCADE
        )";
        
        if (!$conn->query($create_payments)) {
            throw new Exception("Failed to create payments table: " . $conn->error);
        }
    }
}

// Initialize subscription tables
try {
    createSubscriptionTables($conn);
} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add new subscription plan
    if (isset($_POST['add_plan'])) {
        $name = sanitize_input($_POST['name']);
        $description = sanitize_input($_POST['description']);
        $price = floatval($_POST['price']);
        $duration_days = intval($_POST['duration_days']);
        $features = sanitize_input($_POST['features']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $conn->prepare("INSERT INTO subscription_plans (name, description, price, duration_days, features, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdisi", $name, $description, $price, $duration_days, $features, $is_active);
        
        if ($stmt->execute()) {
            $success_message = "Subscription plan added successfully!";
        } else {
            $error_message = "Failed to add subscription plan: " . $stmt->error;
        }
        $stmt->close();
    }
    
    // Update subscription plan
    if (isset($_POST['update_plan'])) {
        $plan_id = intval($_POST['plan_id']);
        $name = sanitize_input($_POST['name']);
        $description = sanitize_input($_POST['description']);
        $price = floatval($_POST['price']);
        $duration_days = intval($_POST['duration_days']);
        $features = sanitize_input($_POST['features']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $conn->prepare("UPDATE subscription_plans SET name=?, description=?, price=?, duration_days=?, features=?, is_active=? WHERE id=?");
        $stmt->bind_param("ssdisii", $name, $description, $price, $duration_days, $features, $is_active, $plan_id);
        
        if ($stmt->execute()) {
            $success_message = "Subscription plan updated successfully!";
        } else {
            $error_message = "Failed to update subscription plan: " . $stmt->error;
        }
        $stmt->close();
    }
    
    // Delete subscription plan
    if (isset($_POST['delete_plan'])) {
        $plan_id = intval($_POST['plan_id']);
        
        // Check if plan has active subscriptions
        $check_subs = $conn->prepare("SELECT COUNT(*) FROM user_subscriptions WHERE plan_id = ? AND status = 'active'");
        $check_subs->bind_param("i", $plan_id);
        $check_subs->execute();
        $check_subs->bind_result($active_count);
        $check_subs->fetch();
        $check_subs->close();
        
        if ($active_count > 0) {
            $error_message = "Cannot delete plan with active subscriptions. Please deactivate it instead.";
        } else {
            $stmt = $conn->prepare("DELETE FROM subscription_plans WHERE id = ?");
            $stmt->bind_param("i", $plan_id);
            
            if ($stmt->execute()) {
                $success_message = "Subscription plan deleted successfully!";
            } else {
                $error_message = "Failed to delete subscription plan: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    
    // Update user subscription status
    if (isset($_POST['update_subscription_status'])) {
        $subscription_id = intval($_POST['subscription_id']);
        $status = sanitize_input($_POST['status']);
        $auto_renew = isset($_POST['auto_renew']) ? 1 : 0;
        
        $stmt = $conn->prepare("UPDATE user_subscriptions SET status = ?, auto_renew = ? WHERE id = ?");
        $stmt->bind_param("sii", $status, $auto_renew, $subscription_id);
        
        if ($stmt->execute()) {
            $success_message = "Subscription status updated successfully!";
        } else {
            $error_message = "Failed to update subscription status: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Get subscription plans
$plans_result = $conn->query("SELECT * FROM subscription_plans ORDER BY price ASC");

// Get user subscriptions with user and plan details
$subscriptions_result = $conn->query("
    SELECT us.*, u.username, u.email, sp.name as plan_name, sp.price
    FROM user_subscriptions us
    JOIN users u ON us.user_id = u.id
    JOIN subscription_plans sp ON us.plan_id = sp.id
    ORDER BY us.created_at DESC
");

// Get payment history
$payments_result = $conn->query("
    SELECT p.*, u.username, sp.name as plan_name
    FROM payments p
    JOIN user_subscriptions us ON p.subscription_id = us.id
    JOIN users u ON us.user_id = u.id
    JOIN subscription_plans sp ON us.plan_id = sp.id
    ORDER BY p.created_at DESC
    LIMIT 50
");

// Get subscription statistics
$total_subscriptions = $conn->query("SELECT COUNT(*) as count FROM user_subscriptions")->fetch_assoc()['count'];
$active_subscriptions = $conn->query("SELECT COUNT(*) as count FROM user_subscriptions WHERE status = 'active'")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0;
$monthly_revenue = $conn->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed' AND MONTH(payment_date) = MONTH(CURRENT_DATE())")->fetch_assoc()['total'] ?? 0;

// Get plan distribution
$plan_distribution = $conn->query("
    SELECT sp.name, COUNT(us.id) as subscriber_count
    FROM subscription_plans sp
    LEFT JOIN user_subscriptions us ON sp.id = us.plan_id AND us.status = 'active'
    GROUP BY sp.id
    ORDER BY sp.price ASC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Management - TechFlix Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6a5af9;
            --primary-dark: #5b4af0;
            --secondary: #d66efd;
            --success: #2ed573;
            --warning: #ffa502;
            --danger: #ff4757;
            --dark: #1e1e2d;
            --darker: #151521;
            --light: #f8f9fa;
            --gray: #6c757d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: var(--darker);
            color: var(--light);
            min-height: 100vh;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: var(--dark);
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--light);
            text-decoration: none;
        }

        .logo-icon {
            font-size: 24px;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo-text {
            font-size: 20px;
            font-weight: 700;
        }

        .nav-links {
            list-style: none;
        }

        .nav-links li {
            margin-bottom: 5px;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: var(--light);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .nav-links a:hover,
        .nav-links a.active {
            background: rgba(106, 90, 249, 0.1);
            border-left-color: var(--primary);
            color: var(--primary);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }

        .top-bar {
            background: var(--dark);
            padding: 15px 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title h1 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .page-title p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--dark);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            transition: transform 0.3s ease;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            opacity: 0.8;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Tabs */
        .tabs {
            display: flex;
            background: var(--dark);
            border-radius: 10px;
            padding: 5px;
            margin-bottom: 25px;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .tab {
            padding: 12px 24px;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 500;
        }

        .tab.active {
            background: var(--primary);
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Forms */
        .form-section {
            background: var(--dark);
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            color: var(--light);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
            color: var(--light);
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        /* Tables */
        .table-container {
            background: var(--dark);
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .table th {
            background: rgba(106, 90, 249, 0.1);
            color: var(--primary);
            font-weight: 600;
        }

        .table tr:hover {
            background: rgba(255,255,255,0.05);
        }

        /* Buttons */
        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.8rem;
            transition: all 0.3s;
            font-weight: 500;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-warning {
            background: var(--warning);
            color: black;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        .btn-sm {
            padding: 6px 10px;
            font-size: 0.75rem;
        }

        /* Badges */
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-primary {
            background: rgba(106, 90, 249, 0.2);
            color: var(--primary);
        }

        .badge-success {
            background: rgba(46, 213, 115, 0.2);
            color: var(--success);
        }

        .badge-warning {
            background: rgba(255, 165, 2, 0.2);
            color: var(--warning);
        }

        .badge-danger {
            background: rgba(255, 71, 87, 0.2);
            color: var(--danger);
        }

        /* Plan Cards */
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .plan-card {
            background: var(--dark);
            border-radius: 10px;
            padding: 25px;
            border: 1px solid rgba(255,255,255,0.05);
            transition: all 0.3s;
            position: relative;
        }

        .plan-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
        }

        .plan-card.featured {
            border-color: var(--primary);
            background: linear-gradient(135deg, var(--dark), rgba(106, 90, 249, 0.1));
        }

        .plan-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .plan-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .plan-price {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .plan-price .period {
            font-size: 1rem;
            color: var(--gray);
        }

        .plan-features {
            list-style: none;
            margin-bottom: 25px;
        }

        .plan-features li {
            padding: 8px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .plan-features li i {
            color: var(--success);
        }

        /* Alert Messages */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid transparent;
        }

        .alert-success {
            background: rgba(46, 213, 115, 0.1);
            color: #2ed573;
            border-color: rgba(46, 213, 115, 0.3);
        }

        .alert-error {
            background: rgba(255, 71, 87, 0.1);
            color: #ff4757;
            border-color: rgba(255, 71, 87, 0.3);
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
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: var(--dark);
            border-radius: 10px;
            padding: 25px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .close-modal {
            background: none;
            border: none;
            color: var(--gray);
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close-modal:hover {
            color: var(--danger);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }
            .sidebar .logo-text,
            .sidebar .nav-text {
                display: none;
            }
            .main-content {
                margin-left: 70px;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .plans-grid {
                grid-template-columns: 1fr;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .tabs {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <div class="logo-text">TechFlix</div>
                </a>
            </div>
            
            <ul class="nav-links">
                <li><a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="nav-text">Dashboard</span>
                </a></li>
                <li><a href="content.php">
                    <i class="fas fa-film"></i>
                    <span class="nav-text">Content</span>
                </a></li>
                <li><a href="users.php">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Users</span>
                </a></li>
                <li><a href="media.php">
                    <i class="fas fa-photo-video"></i>
                    <span class="nav-text">Media</span>
                </a></li>
                <li><a href="analytics.php">
                    <i class="fas fa-chart-bar"></i>
                    <span class="nav-text">Analytics</span>
                </a></li>
                <li><a href="subscription_management.php" class="active">
                    <i class="fas fa-credit-card"></i>
                    <span class="nav-text">Subscriptions</span>
                </a></li>
                <li><a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="nav-text">Logout</span>
                </a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="page-title">
                    <h1>Subscription Management</h1>
                    <p>Manage subscription plans, user subscriptions, and payments</p>
                </div>
                <div>
                    <button class="btn btn-primary" onclick="openPlanModal()">
                        <i class="fas fa-plus"></i> Add Plan
                    </button>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_subscriptions; ?></div>
                    <div class="stat-label">Total Subscriptions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-number"><?php echo $active_subscriptions; ?></div>
                    <div class="stat-label">Active Subscriptions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-number">$<?php echo number_format($total_revenue, 2); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-number">$<?php echo number_format($monthly_revenue, 2); ?></div>
                    <div class="stat-label">Monthly Revenue</div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="tabs">
                <div class="tab active" onclick="switchTab('plans')">Subscription Plans</div>
                <div class="tab" onclick="switchTab('subscriptions')">User Subscriptions</div>
                <div class="tab" onclick="switchTab('payments')">Payment History</div>
                <div class="tab" onclick="switchTab('analytics')">Subscription Analytics</div>
            </div>

            <!-- Subscription Plans Tab -->
            <div id="plans-tab" class="tab-content active">
                <div class="plans-grid">
                    <?php while($plan = $plans_result->fetch_assoc()): ?>
                    <div class="plan-card <?php echo $plan['is_active'] ? 'featured' : ''; ?>">
                        <div class="plan-header">
                            <div class="plan-name"><?php echo $plan['name']; ?></div>
                            <div class="plan-price">
                                $<?php echo $plan['price']; ?>
                                <span class="period">/month</span>
                            </div>
                            <div style="color: var(--gray); font-size: 0.9rem;">
                                <?php echo $plan['duration_days']; ?> days
                            </div>
                        </div>
                        
                        <p style="color: var(--gray); margin-bottom: 20px;"><?php echo $plan['description']; ?></p>
                        
                        <ul class="plan-features">
                            <?php 
                            $features = explode(',', $plan['features']);
                            foreach($features as $feature): 
                            ?>
                                <li><i class="fas fa-check"></i> <?php echo trim($feature); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <div style="display: flex; gap: 10px; justify-content: center;">
                            <button class="btn btn-outline btn-sm" onclick="editPlan(<?php echo $plan['id']; ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                                <button type="submit" name="delete_plan" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this plan?')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                        
                        <div style="position: absolute; top: 15px; right: 15px;">
                            <span class="badge <?php echo $plan['is_active'] ? 'badge-success' : 'badge-danger'; ?>">
                                <?php echo $plan['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- User Subscriptions Tab -->
            <div id="subscriptions-tab" class="tab-content">
                <div class="form-section">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Plan</th>
                                    <th>Status</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Auto Renew</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($subscription = $subscriptions_result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <strong><?php echo $subscription['username']; ?></strong>
                                            <div style="color: var(--gray); font-size: 0.8rem;"><?php echo $subscription['email']; ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo $subscription['plan_name']; ?></strong>
                                        <div style="color: var(--gray); font-size: 0.8rem;">$<?php echo $subscription['price']; ?>/month</div>
                                    </td>
                                    <td>
                                        <?php
                                        $status_badge = [
                                            'active' => 'badge-success',
                                            'canceled' => 'badge-danger',
                                            'expired' => 'badge-warning',
                                            'pending' => 'badge-primary'
                                        ];
                                        ?>
                                        <span class="badge <?php echo $status_badge[$subscription['status']]; ?>">
                                            <?php echo ucfirst($subscription['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $subscription['start_date'] ? date('M j, Y', strtotime($subscription['start_date'])) : 'N/A'; ?></td>
                                    <td><?php echo $subscription['end_date'] ? date('M j, Y', strtotime($subscription['end_date'])) : 'N/A'; ?></td>
                                    <td>
                                        <span class="badge <?php echo $subscription['auto_renew'] ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo $subscription['auto_renew'] ? 'Yes' : 'No'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-outline btn-sm" onclick="editSubscription(<?php echo $subscription['id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment History Tab -->
            <div id="payments-tab" class="tab-content">
                <div class="form-section">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>User</th>
                                    <th>Plan</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($payment = $payments_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $payment['transaction_id'] ?: 'N/A'; ?></td>
                                    <td><?php echo $payment['username']; ?></td>
                                    <td><?php echo $payment['plan_name']; ?></td>
                                    <td>$<?php echo $payment['amount']; ?></td>
                                    <td>
                                        <?php
                                        $status_badge = [
                                            'completed' => 'badge-success',
                                            'pending' => 'badge-warning',
                                            'failed' => 'badge-danger',
                                            'refunded' => 'badge-primary'
                                        ];
                                        ?>
                                        <span class="badge <?php echo $status_badge[$payment['status']]; ?>">
                                            <?php echo ucfirst($payment['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($payment['created_at'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Subscription Analytics Tab -->
            <div id="analytics-tab" class="tab-content">
                <div class="form-section">
                    <h3 style="margin-bottom: 20px;">Plan Distribution</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Plan Name</th>
                                    <th>Active Subscribers</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_active = array_sum(array_column($plan_distribution, 'subscriber_count'));
                                foreach($plan_distribution as $plan): 
                                    $percentage = $total_active > 0 ? ($plan['subscriber_count'] / $total_active) * 100 : 0;
                                ?>
                                <tr>
                                    <td><?php echo $plan['name']; ?></td>
                                    <td><?php echo $plan['subscriber_count']; ?></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="flex: 1; background: rgba(255,255,255,0.1); height: 8px; border-radius: 4px;">
                                                <div style="width: <?php echo $percentage; ?>%; background: var(--primary); height: 100%; border-radius: 4px;"></div>
                                            </div>
                                            <span><?php echo round($percentage, 1); ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Plan Modal -->
    <div id="planModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="planModalTitle">Add New Plan</h2>
                <button class="close-modal" onclick="closePlanModal()">&times;</button>
            </div>
            <form method="POST" action="" id="planForm">
                <input type="hidden" name="plan_id" id="plan_id">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Plan Name *</label>
                        <input type="text" name="name" class="form-control" required id="plan_name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price ($) *</label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" required id="plan_price">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description *</label>
                    <textarea name="description" class="form-control" required id="plan_description"></textarea>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Duration (days) *</label>
                        <input type="number" name="duration_days" class="form-control" min="1" value="30" required id="plan_duration">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Features (comma separated) *</label>
                        <textarea name="features" class="form-control" required id="plan_features" placeholder="Feature 1, Feature 2, Feature 3"></textarea>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="is_active" value="1" id="plan_active" checked>
                        <span>Active Plan</span>
                    </label>
                </div>
                
                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-outline" onclick="closePlanModal()">Cancel</button>
                    <button type="submit" name="add_plan" class="btn btn-primary" id="planSubmitBtn">
                        <i class="fas fa-save"></i> Add Plan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Subscription Modal -->
    <div id="subscriptionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Subscription</h2>
                <button class="close-modal" onclick="closeSubscriptionModal()">&times;</button>
            </div>
            <form method="POST" action="" id="subscriptionForm">
                <input type="hidden" name="subscription_id" id="subscription_id">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-control" required id="subscription_status">
                            <option value="active">Active</option>
                            <option value="canceled">Canceled</option>
                            <option value="expired">Expired</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Auto Renew</label>
                        <div style="margin-top: 8px;">
                            <input type="checkbox" name="auto_renew" value="1" id="subscription_auto_renew">
                            <label for="subscription_auto_renew">Enable auto renewal</label>
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-outline" onclick="closeSubscriptionModal()">Cancel</button>
                    <button type="submit" name="update_subscription_status" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Subscription
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Tab switching
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }

        // Plan modal functions
        function openPlanModal() {
            document.getElementById('planModal').style.display = 'flex';
            document.getElementById('planModalTitle').textContent = 'Add New Plan';
            document.getElementById('planSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Add Plan';
            document.getElementById('planSubmitBtn').name = 'add_plan';
            document.getElementById('planForm').reset();
        }

        function closePlanModal() {
            document.getElementById('planModal').style.display = 'none';
        }

        function editPlan(planId) {
            // In a real application, you would fetch plan data via AJAX
            // For now, we'll just show a placeholder
            document.getElementById('planModal').style.display = 'flex';
            document.getElementById('planModalTitle').textContent = 'Edit Plan';
            document.getElementById('planSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Update Plan';
            document.getElementById('planSubmitBtn').name = 'update_plan';
            document.getElementById('plan_id').value = planId;
            
            // Set placeholder values (in real app, fetch from server)
            document.getElementById('plan_name').value = 'Premium Plan';
            document.getElementById('plan_price').value = '14.99';
            document.getElementById('plan_description').value = 'Premium streaming experience with all features';
            document.getElementById('plan_duration').value = '30';
            document.getElementById('plan_features').value = '4K Streaming, 4 Devices, Ad-free, Download content, Early access';
            document.getElementById('plan_active').checked = true;
        }

        // Subscription modal functions
        function editSubscription(subscriptionId) {
            document.getElementById('subscriptionModal').style.display = 'flex';
            document.getElementById('subscription_id').value = subscriptionId;
            
            // Set placeholder values (in real app, fetch from server)
            document.getElementById('subscription_status').value = 'active';
            document.getElementById('subscription_auto_renew').checked = true;
        }

        function closeSubscriptionModal() {
            document.getElementById('subscriptionModal').style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const planModal = document.getElementById('planModal');
            const subscriptionModal = document.getElementById('subscriptionModal');
            
            if (event.target === planModal) {
                closePlanModal();
            }
            if (event.target === subscriptionModal) {
                closeSubscriptionModal();
            }
        }

        // Subscription Management Console
        console.group('💳 SUBSCRIPTION MANAGEMENT');
        console.log('📊 Total Subscriptions:', <?php echo $total_subscriptions; ?>);
        console.log('🔥 Active Subscriptions:', <?php echo $active_subscriptions; ?>);
        console.log('💰 Total Revenue: $', <?php echo $total_revenue; ?>);
        console.log('📈 Monthly Revenue: $', <?php echo $monthly_revenue; ?>);
        console.table(<?php echo json_encode($plan_distribution); ?>);
        console.groupEnd();
    </script>
</body>
</html>