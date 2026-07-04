<?php
// Admin-specific header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - TechFlix</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: var(--card-bg);
            border-right: 1px solid rgba(138, 43, 226, 0.3);
            padding: 20px 0;
            z-index: 1000;
        }
        
        .admin-logo {
            padding: 0 25px 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        
        .admin-nav {
            list-style: none;
            padding: 0;
        }
        
        .admin-nav li {
            margin: 5px 0;
        }
        
        .admin-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 25px;
            color: #ddd;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .admin-nav a:hover,
        .admin-nav a.active {
            background: rgba(138, 43, 226, 0.2);
            color: white;
            border-right: 3px solid var(--primary);
        }
        
        .admin-nav a i {
            width: 20px;
            text-align: center;
        }
        
        .admin-main {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .admin-sidebar.active {
                transform: translateX(0);
            }
            
            .admin-main {
                margin-left: 0;
            }
            
            .mobile-admin-toggle {
                display: block;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1001;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Toggle Button -->
    <button class="mobile-admin-toggle" style="display: none; background: var(--primary); color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer;">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Admin Sidebar -->
    <div class="admin-sidebar" id="adminSidebar">
        <div class="admin-logo">
            <h2 style="margin: 0; background: linear-gradient(to right, #fff, #d9e3f0); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                <i class="fas fa-crown"></i> Admin
            </h2>
        </div>
        
        <ul class="admin-nav">
            <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a></li>
            
            <li><a href="content.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'content.php' ? 'active' : ''; ?>">
                <i class="fas fa-film"></i> Content Management
            </a></li>
            
            <li><a href="media.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'media.php' ? 'active' : ''; ?>">
                <i class="fas fa-photo-video"></i> Media Library
            </a></li>
            
            <li><a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> User Management
            </a></li>
            
            <li><a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                <i class="fas fa-tags"></i> Categories
            </a></li>
            
            <li><a href="analytics.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i> Analytics
            </a></li>
            
            <li><a href="settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> Settings
            </a></li>
            
            <li style="margin-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 20px;">
                <a href="../home.php">
                    <i class="fas fa-arrow-left"></i> Back to Site
                </a>
            </li>
            
            <li>
                <a href="../auth/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="admin-main">
        <!-- Galaxy Background -->
        <div class="galaxy-bg" style="z-index: -1;"></div>
        
        <!-- Your page content will go here -->
