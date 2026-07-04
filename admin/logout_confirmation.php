<?php
session_start();

// Only allow logged-in admins to see this page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 100px auto; padding: 20px; }
        .confirmation-box { border: 1px solid #ddd; padding: 30px; text-align: center; border-radius: 5px; }
        .btn { padding: 10px 20px; margin: 5px; text-decoration: none; border: none; cursor: pointer; }
        .btn-logout { background: #dc3545; color: white; }
        .btn-cancel { background: #6c757d; color: white; }
    </style>
</head>
<body>
    <div class="confirmation-box">
        <h2>Confirm Logout</h2>
        <p>Are you sure you want to logout from the admin panel?</p>
        
        <form method="POST" action="logout.php">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <button type="submit" class="btn btn-logout">Yes, Logout</button>
            <a href="admin_dashboard.php" class="btn btn-cancel">Cancel</a>
        </form>
    </div>
</body>
</html>