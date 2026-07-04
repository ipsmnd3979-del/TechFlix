<?php
session_start();
require_once '../includes/config.php';

// Check admin access
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    // For testing - allow access without login
    // header("Location: login.php");
    // exit();
}

// Set session user_id if not set (for testing)
if (!isset($_SESSION['user_id']) && isset($_SESSION['loggedin'])) {
    $_SESSION['user_id'] = 1; // Temporary for testing - replace with actual user ID from your system
}

// Define required functions
if (!function_exists('sanitize_input')) {
    function sanitize_input($data)
    {
        if (is_null($data)) return '';
        return htmlspecialchars(strip_tags(trim($data)));
    }
}

// Function to add status column
function addStatusColumn($conn)
{
    try {
        $sql = "ALTER TABLE users ADD COLUMN status VARCHAR(20) DEFAULT 'active' AFTER role";
        if ($conn->query($sql)) {
            return ['success' => true, 'message' => 'Status column added successfully'];
        } else {
            return ['success' => false, 'message' => $conn->error];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Handle AJAX request for adding status column
if (isset($_GET['action']) && $_GET['action'] == 'add_status_column') {
    header('Content-Type: application/json');
    echo json_encode(addStatusColumn($conn));
    exit();
}

// Check if users table has status column
$check_columns = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
$has_status_column = $check_columns->num_rows > 0;

// Handle user management
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add new user
    if (isset($_POST['add_user'])) {
        $username = sanitize_input($_POST['username']);
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $role = sanitize_input($_POST['role']);
        $status = $has_status_column ? sanitize_input($_POST['status']) : 'active';

        // Validate inputs
        if (empty($username) || empty($email) || empty($password)) {
            $error_message = "All fields are required!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Invalid email format!";
        } elseif ($password !== $confirm_password) {
            $error_message = "Passwords do not match!";
        } elseif (strlen($password) < 6) {
            $error_message = "Password must be at least 6 characters long!";
        } else {
            // Check if user already exists
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $check_stmt->bind_param("ss", $username, $email);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $error_message = "Username or email already exists!";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Build query based on available columns
                if ($has_status_column) {
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                    $stmt->bind_param("sssss", $username, $email, $password_hash, $role, $status);
                } else {
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->bind_param("ssss", $username, $email, $password_hash, $role);
                }

                if ($stmt->execute()) {
                    $success_message = "User added successfully!";
                } else {
                    $error_message = "Failed to add user: " . $stmt->error;
                }
                $stmt->close();
            }
            $check_stmt->close();
        }
    }

    // Handle user deletion
    if (isset($_POST['delete_user'])) {
        $user_id = intval($_POST['user_id']);

        // Prevent self-deletion
        if (isset($_SESSION['user_id']) && $user_id == $_SESSION['user_id']) {
            $error_message = "You cannot delete your own account!";
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);

            if ($stmt->execute()) {
                $success_message = "User deleted successfully!";
            } else {
                $error_message = "Failed to delete user: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    // Handle user status update (only if status column exists)
    if (isset($_POST['update_status']) && $has_status_column) {
        $user_id = intval($_POST['user_id']);
        $status = sanitize_input($_POST['status']);

        // Prevent self-deactivation
        if (isset($_SESSION['user_id']) && $user_id == $_SESSION['user_id'] && $status == 'inactive') {
            $error_message = "You cannot deactivate your own account!";
        } else {
            $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $user_id);

            if ($stmt->execute()) {
                $success_message = "User status updated successfully!";
            } else {
                $error_message = "Failed to update user status: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    // Handle user role update
    if (isset($_POST['update_role'])) {
        $user_id = intval($_POST['user_id']);
        $role = sanitize_input($_POST['role']);

        // Prevent self-demotion
        if (isset($_SESSION['user_id']) && $user_id == $_SESSION['user_id'] && $role == 'user') {
            $error_message = "You cannot change your own role to user!";
        } else {
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $role, $user_id);

            if ($stmt->execute()) {
                $success_message = "User role updated successfully!";
            } else {
                $error_message = "Failed to update user role: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Get all users
$users_result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");

// Get statistics with improved error handling
try {
    $total_users_result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($total_users_result) {
        $total_users = $total_users_result->fetch_assoc()['count'];
    } else {
        throw new Exception("Failed to count total users");
    }

    if ($has_status_column) {
        $active_result = $conn->query("SELECT COUNT(*) as count FROM users WHERE status='active'");
        $inactive_result = $conn->query("SELECT COUNT(*) as count FROM users WHERE status='inactive'");

        if ($active_result && $inactive_result) {
            $active_users = $active_result->fetch_assoc()['count'];
            $inactive_users = $inactive_result->fetch_assoc()['count'];
        } else {
            throw new Exception("Failed to count status users");
        }
    } else {
        $active_users = $total_users;
        $inactive_users = 0;
    }

    $admin_result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='admin' OR role='superadmin'");
    $regular_result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='user'");

    if ($admin_result && $regular_result) {
        $admin_users = $admin_result->fetch_assoc()['count'];
        $regular_users = $regular_result->fetch_assoc()['count'];
    } else {
        throw new Exception("Failed to count role users");
    }
} catch (Exception $e) {
    error_log("User statistics error: " . $e->getMessage());
    $total_users = $active_users = $inactive_users = $admin_users = $regular_users = 0;
    $error_message = "Error loading statistics: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - TechFlix</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reuse the same CSS variables and base styles from dashboard.php */
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

        /* Sidebar - Same as dashboard.php */
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
        }

        .stat-card:hover {
            transform: translateY(-5px);
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

        /* User Table */
        .users-table {
            width: 100%;
            background: var(--dark);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .table-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        th {
            background: rgba(255, 255, 255, 0.05);
            font-weight: 600;
            color: var(--light);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 10px;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-active {
            background: rgba(46, 213, 115, 0.2);
            color: #2ed573;
        }

        .status-inactive {
            background: rgba(255, 71, 87, 0.2);
            color: #ff4757;
        }

        .role-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .role-admin {
            background: rgba(106, 90, 249, 0.2);
            color: var(--primary);
        }

        .role-user {
            background: rgba(214, 110, 253, 0.2);
            color: var(--secondary);
        }

        .role-superadmin {
            background: rgba(255, 165, 2, 0.2);
            color: var(--warning);
        }

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
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-warning {
            background: var(--warning);
            color: black;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        /* Forms */
        .form-section {
            background: var(--dark);
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
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
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            color: var(--light);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: var(--dark);
            border-radius: 10px;
            padding: 25px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .close-modal {
            background: none;
            border: none;
            color: var(--gray);
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Search and Filter */
        .table-controls {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .search-box {
            position: relative;
            flex: 1;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border-radius: 5px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            color: var(--light);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .info-banner {
            background: rgba(255, 165, 2, 0.1);
            border: 1px solid rgba(255, 165, 2, 0.3);
            color: var(--warning);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-banner i {
            font-size: 1.2rem;
        }

        .success-message {
            background: rgba(46, 213, 115, 0.1);
            color: #2ed573;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid rgba(46, 213, 115, 0.3);
        }

        .error-message {
            background: rgba(255, 71, 87, 0.1);
            color: #ff4757;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 71, 87, 0.3);
        }

        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .form-control.error {
            border-color: #ff4757 !important;
        }

        .field-error {
            color: #ff4757;
            font-size: 0.8rem;
            margin-top: 5px;
            display: block;
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

            .form-grid {
                grid-template-columns: 1fr;
            }

            .table-controls {
                flex-direction: column;
            }

            .action-buttons {
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
                <li><a href="users.php" class="active">
                        <i class="fas fa-users"></i>
                        <span class="nav-text">Users</span>
                    </a></li>
                <li><a href="media.php">
                        <i class="fas fa-photo-video"></i>
                        <span class="nav-text">Media</span>
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
                    <h1>User Management</h1>
                    <p>Manage system users and permissions</p>
                </div>
                <div>
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Add New User
                    </button>
                </div>
            </div>

            <?php if (!$has_status_column): ?>
                <div class="info-banner">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Database Notice:</strong> The users table is missing the 'status' column.
                        <a href="javascript:void(0)" onclick="createStatusColumn()" style="color: var(--warning); text-decoration: underline; margin-left: 5px;">
                            Click here to add it automatically.
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($success_message)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_users; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $active_users; ?></div>
                    <div class="stat-label">Active Users</div>
                </div>
                <?php if ($has_status_column): ?>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $inactive_users; ?></div>
                        <div class="stat-label">Inactive Users</div>
                    </div>
                <?php endif; ?>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $admin_users; ?></div>
                    <div class="stat-label">Admin Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $regular_users; ?></div>
                    <div class="stat-label">Regular Users</div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="form-section">
                <div class="table-controls">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search users...">
                    </div>
                    <select id="roleFilter" class="form-control" style="width: 200px;">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                        <option value="superadmin">Super Admin</option>
                    </select>
                    <?php if ($has_status_column): ?>
                        <select id="statusFilter" class="form-control" style="width: 200px;">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    <?php endif; ?>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <?php if ($has_status_column): ?>
                                    <th>Status</th>
                                <?php endif; ?>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTable">
                            <?php if ($users_result->num_rows > 0): ?>
                                <?php while ($user = $users_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <strong><?php echo $user['username']; ?></strong>
                                                    <div style="font-size: 0.8rem; color: var(--gray);">ID: <?php echo $user['id']; ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo $user['email']; ?></td>
                                        <td>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <select name="role" onchange="this.form.submit()" class="form-control" style="width: 120px; padding: 5px;">
                                                    <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                                    <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                    <option value="superadmin" <?php echo $user['role'] == 'superadmin' ? 'selected' : ''; ?>>Super Admin</option>
                                                </select>
                                                <input type="hidden" name="update_role" value="1">
                                            </form>
                                        </td>
                                        <?php if ($has_status_column): ?>
                                            <td>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <select name="status" onchange="this.form.submit()" class="form-control" style="width: 120px; padding: 5px;">
                                                        <option value="active" <?php echo $user['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                        <option value="inactive" <?php echo $user['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>
                                                    <input type="hidden" name="update_status" value="1">
                                                </form>
                                            </td>
                                        <?php endif; ?>
                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-warning" onclick="editUser(<?php echo $user['id']; ?>)">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" name="delete_user" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?php echo $has_status_column ? '6' : '5'; ?>" style="text-align: center; padding: 40px; color: var(--gray);">
                                        <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                                        <h3>No Users Found</h3>
                                        <p>Start by adding your first user.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add User Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New User</h2>
                <button class="close-modal" onclick="closeAddModal()">&times;</button>
            </div>
            <form method="POST" action="" id="addForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" class="form-control" required minlength="3">
                        <span class="field-error" id="username-error"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                        <span class="field-error" id="email-error"></span>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                        <span class="field-error" id="password-error"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                        <span class="field-error" id="confirm-password-error"></span>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Role *</label>
                        <select name="role" class="form-control" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                    </div>
                    <?php if ($has_status_column): ?>
                        <div class="form-group">
                            <label class="form-label">Status *</label>
                            <select name="status" class="form-control" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-outline" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" name="add_user" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        function openAddModal() {
            document.getElementById('addModal').style.display = 'flex';
        }

        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
            document.getElementById('addForm').reset();
            clearErrors();
        }

        // Clear error messages
        function clearErrors() {
            document.querySelectorAll('.field-error').forEach(el => {
                el.textContent = '';
            });
            document.querySelectorAll('.form-control').forEach(el => {
                el.classList.remove('error');
            });
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#usersTable tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Filter functionality
        document.getElementById('roleFilter').addEventListener('change', filterTable);
        <?php if ($has_status_column): ?>
            document.getElementById('statusFilter').addEventListener('change', filterTable);
        <?php endif; ?>



        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addModal');
            if (event.target === modal) {
                closeAddModal();
            }
        }

        // Enhanced form validation
        document.getElementById('addForm').addEventListener('submit', function(e) {
            let isValid = true;
            clearErrors();

            const username = document.querySelector('input[name="username"]').value;
            const email = document.querySelector('input[name="email"]').value;
            const password = document.querySelector('input[name="password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value;

            // Username validation
            if (username.length < 3) {
                document.getElementById('username-error').textContent = 'Username must be at least 3 characters';
                document.querySelector('input[name="username"]').classList.add('error');
                isValid = false;
            }

            // Email validation
            if (!email.includes('@') || !email.includes('.')) {
                document.getElementById('email-error').textContent = 'Please enter a valid email address';
                document.querySelector('input[name="email"]').classList.add('error');
                isValid = false;
            }

            // Password validation
            if (password.length < 6) {
                document.getElementById('password-error').textContent = 'Password must be at least 6 characters long';
                document.querySelector('input[name="password"]').classList.add('error');
                isValid = false;
            }

            // Confirm password validation
            if (password !== confirmPassword) {
                document.getElementById('confirm-password-error').textContent = 'Passwords do not match';
                document.querySelector('input[name="confirm_password"]').classList.add('error');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        // Function to create status column
        function createStatusColumn() {
            if (confirm('This will add the status column to your users table. Continue?')) {
                // Show loading state
                const banner = document.querySelector('.info-banner');
                const originalContent = banner.innerHTML;
                banner.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding status column...';

                fetch('users.php?action=add_status_column')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            banner.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message + ' Page will reload...';
                            setTimeout(() => location.reload(), 2000);
                        } else {
                            banner.innerHTML = '<i class="fas fa-exclamation-circle"></i> Error: ' + data.message;
                            setTimeout(() => banner.innerHTML = originalContent, 5000);
                        }
                    })
                    .catch(error => {
                        banner.innerHTML = '<i class="fas fa-exclamation-circle"></i> Error: ' + error;
                        setTimeout(() => banner.innerHTML = originalContent, 5000);
                    });
            }
        }

        // Edit user function (placeholder for future implementation)
        function editUser(userId) {
            alert('Edit user functionality for user ID: ' + userId + ' will be implemented in the next version.');
            // Future implementation: Open edit modal with user data
        }

        // Dashboard Console Insights
        console.group('👥 USER MANAGEMENT DEBUG MODE');
        console.log('🕒 Users page loaded at:', new Date().toLocaleString());
        console.log('📊 Total users:', <?php echo $total_users; ?>);
        console.log('👑 Admin users:', <?php echo $admin_users; ?>);
        console.log('🔧 Status column available:', <?php echo $has_status_column ? 'true' : 'false'; ?>);
        console.log('🔐 Current user ID:', <?php echo $_SESSION['user_id'] ?? 'null'; ?>);
        console.groupEnd();
        // Initialize filtering with debouncing
        function initializeFilters() {
            let filterTimeout;

            ['searchInput', 'roleFilter', 'statusFilter'].forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('input', function() {
                        clearTimeout(filterTimeout);
                        filterTimeout = setTimeout(filterTable, 300);
                    });

                    element.addEventListener('change', function() {
                        clearTimeout(filterTimeout);
                        filterTable();
                    });
                }
            });

            // Clear filters button
            const clearFiltersBtn = document.createElement('button');
            clearFiltersBtn.type = 'button';
            clearFiltersBtn.className = 'btn btn-outline';
            clearFiltersBtn.innerHTML = '<i class="fas fa-times"></i> Clear Filters';
            clearFiltersBtn.onclick = clearAllFilters;

            const tableControls = document.querySelector('.table-controls');
            if (tableControls) {
                tableControls.appendChild(clearFiltersBtn);
            }
        }

        function clearAllFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('roleFilter').value = '';
            <?php if ($has_status_column): ?>
                document.getElementById('statusFilter').value = '';
            <?php endif; ?>
            filterTable();
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeFilters();
            filterTable(); // Initial filter to set counts
        });
    </script>
</body>

</html>