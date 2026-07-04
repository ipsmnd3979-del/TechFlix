<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// // Check admin permissions
// if (!isAdmin()) {
//     header("Location: ../login.php");
//     exit();
// }

$message = '';
$error = '';

// Handle database updates
if (isset($_POST['add_status_column'])) {
    try {
        // Add status column to users table
        $sql = "ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'";
        if ($conn->query($sql)) {
            $message = "Successfully added status column to users table!";
            
            // Update all existing users to active status
            $conn->query("UPDATE users SET status = 'active' WHERE status IS NULL");
        } else {
            $error = "Error adding status column: " . $conn->error;
        }
    } catch (Exception $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Check if status column already exists
$check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
$column_exists = $check_column->num_rows > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Update - TechFlix Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .update-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .btn {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #1e7e34;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="update-container">
        <h1>Database Update</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($column_exists): ?>
            <div class="alert alert-success">
                <strong>Status column already exists!</strong> The users table already has the status column.
            </div>
            <a href="content.php" class="btn">Return to Content Management</a>
        <?php else: ?>
            <div class="update-info">
                <h3>Required Database Update</h3>
                <p>The system needs to add the <strong>status</strong> column to your users table.</p>
                <p>This column will be used to manage user account status (active, inactive, suspended).</p>
                
                <form method="POST">
                    <button type="submit" name="add_status_column" class="btn btn-success">
                        Add Status Column to Users Table
                    </button>
                    <a href="content.php" class="btn" style="margin-left: 10px;">Cancel</a>
                </form>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>