<?php
session_start();

// Simple login for testing - remove this in production
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simple authentication - replace with real authentication
    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['loggedin'] = true;
        $_SESSION['role'] = 'admin';
        $_SESSION['username'] = 'Admin';
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}

// If already logged in, redirect to dashboard
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - TechFlix</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #1e1e2d, #151521);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: rgba(30, 30, 45, 0.9);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(106, 90, 249, 0.3);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #fff;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        
        .login-header p {
            color: #6c757d;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #fff;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #6a5af9;
        }
        
        .login-btn {
            width: 100%;
            padding: 12px;
            background: #6a5af9;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .login-btn:hover {
            background: #5b4af0;
        }
        
        .error {
            background: rgba(255, 71, 87, 0.1);
            color: #ff4757;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid rgba(255, 71, 87, 0.3);
        }
        
        .demo-credentials {
            background: rgba(106, 90, 249, 0.1);
            color: #6a5af9;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 0.9rem;
            border: 1px solid rgba(106, 90, 249, 0.3);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>TechFlix Admin</h1>
            <p>Sign in to your account</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="login-btn">Sign In</button>
        </form>
        
        <div class="demo-credentials">
            <strong>Demo Credentials:</strong><br>
            Username: admin<br>
            Password: admin
        </div>
    </div>
</body>
</html>