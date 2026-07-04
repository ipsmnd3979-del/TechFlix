<?php
// subscription.php
session_start();

// Check if config file exists and include it
$config_path = '../includes/config.php';
if (!file_exists($config_path)) {
    die("Configuration file not found. Please create includes/config.php");
}

require_once $config_path;

// Check admin access with proper error handling
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    die("Access denied. Admin privileges required.");
}

// Create subscription tables if they don't exist
function createSubscriptionTables($conn) {
    $tables = [
        'subscription_plans' => "CREATE TABLE IF NOT EXISTS subscription_plans (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            duration_days INT(11) NOT NULL,
            features TEXT,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        'user_subscriptions' => "CREATE TABLE IF NOT EXISTS user_subscriptions (
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
        )",
        
        'payments' => "CREATE TABLE IF NOT EXISTS payments (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            subscription_id INT(11) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            payment_method VARCHAR(50),
            transaction_id VARCHAR(100),
            status ENUM('completed', 'pending', 'failed', 'refunded') DEFAULT 'pending',
            payment_date TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (subscription_id) REFERENCES user_subscriptions(id) ON DELETE CASCADE
        )"
    ];

    foreach ($tables as $table => $sql) {
        if (!$conn->query($sql)) {
            throw new Exception("Failed to create $table: " . $conn->error);
        }
    }

    // Insert default plans if none exist
    $check_plans = $conn->query("SELECT COUNT(*) as count FROM subscription_plans");
    $plan_count = $check_plans->fetch_assoc()['count'];
    
    if ($plan_count == 0) {
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
}

// Initialize subscription tables
try {
    createSubscriptionTables($conn);
} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_plan'])) {
        $name = sanitize_input($_POST['name']);
        $description = sanitize_input($_POST['description']);
        $price = floatval($_POST['price']);
        $duration_days = intval($_POST['duration_days']);
        $features = sanitize_input($_POST['features']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $conn->prepare("INSERT INTO subscription_plans (name, description, price, duration_days, features, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssdisi", $name, $description, $price, $duration_days, $features, $is_active);
            
            if ($stmt->execute()) {
                $success_message = "Subscription plan added successfully!";
            } else {
                $error_message = "Failed to add subscription plan: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Failed to prepare statement: " . $conn->error;
        }
    }
}

// Get subscription data
$plans_result = $conn->query("SELECT * FROM subscription_plans ORDER BY price ASC");
$subscriptions_result = $conn->query("
    SELECT us.*, u.username, u.email, sp.name as plan_name, sp.price
    FROM user_subscriptions us
    JOIN users u ON us.user_id = u.id
    JOIN subscription_plans sp ON us.plan_id = sp.id
    ORDER BY us.created_at DESC
");

// Get statistics
$total_subscriptions = $conn->query("SELECT COUNT(*) as count FROM user_subscriptions")->fetch_assoc()['count'];
$active_subscriptions = $conn->query("SELECT COUNT(*) as count FROM user_subscriptions WHERE status = 'active'")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Management - TechFlix Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Add your existing CSS styles here */
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

        .sidebar {
            width: 250px;
            background: var(--dark);
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

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
        }

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
            border: 1px solid rgba(255,255,255,0.05);
        }

        .form-section {
            background: var(--dark);
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: rgba(46, 213, 115, 0.1);
            color: #2ed573;
            border: 1px solid rgba(46, 213, 115, 0.3);
        }

        .alert-error {
            background: rgba(255, 71, 87, 0.1);
            color: #ff4757;
            border: 1px solid rgba(255, 71, 87, 0.3);
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
                    <span>Dashboard</span>
                </a></li>
                <li><a href="subscription.php" class="active">
                    <i class="fas fa-credit-card"></i>
                    <span>Subscriptions</span>
                </a></li>
                <li><a href="users.php">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="top-bar">
                <h1>Subscription Management</h1>
                <p>Manage subscription plans and user subscriptions</p>
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
                    <div class="stat-number"><?php echo $total_subscriptions; ?></div>
                    <div class="stat-label">Total Subscriptions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $active_subscriptions; ?></div>
                    <div class="stat-label">Active Subscriptions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">$<?php echo number_format($total_revenue, 2); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>

            <!-- Add Plan Form -->
            <div class="form-section">
                <h2>Add New Subscription Plan</h2>
                <form method="POST" action="">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label>Plan Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div>
                            <label>Price ($)</label>
                            <input type="number" name="price" step="0.01" min="0" class="form-control" required>
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label>Description</label>
                        <textarea name="description" class="form-control" required></textarea>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label>Duration (days)</label>
                            <input type="number" name="duration_days" min="1" value="30" class="form-control" required>
                        </div>
                        <div>
                            <label>Features (comma separated)</label>
                            <textarea name="features" class="form-control" required placeholder="Feature 1, Feature 2, Feature 3"></textarea>
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label>
                            <input type="checkbox" name="is_active" value="1" checked>
                            Active Plan
                        </label>
                    </div>
                    <button type="submit" name="add_plan" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Plan
                    </button>
                </form>
            </div>

            <!-- Existing Plans -->
            <div class="form-section">
                <h2>Existing Plans</h2>
                <?php if ($plans_result->num_rows > 0): ?>
                    <div style="display: grid; gap: 15px;">
                        <?php while($plan = $plans_result->fetch_assoc()): ?>
                        <div style="background: var(--darker); padding: 15px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                            <h3><?php echo $plan['name']; ?> - $<?php echo $plan['price']; ?>/month</h3>
                            <p><?php echo $plan['description']; ?></p>
                            <small>Duration: <?php echo $plan['duration_days']; ?> days | Status: <?php echo $plan['is_active'] ? 'Active' : 'Inactive'; ?></small>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>No subscription plans found.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        console.log('Subscription Management Loaded');
        
        // Example of window.location usage
        function redirectToDashboard() {
            window.location.href = 'dashboard.php';
        }

        function reloadPage() {
            window.location.reload();
        }

        function redirectWithParams() {
            window.location.href = 'subscription.php?action=refresh&tab=plans';
        }

        // Get current URL information
        console.log('Current URL:', window.location.href);
        console.log('Path:', window.location.pathname);
        console.log('Search params:', window.location.search);
    </script>
</body>
</html>