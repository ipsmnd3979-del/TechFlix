<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Include dependencies first
require_once 'config.php';
require_once 'functions.php';
require_once 'auth.php';
require_once 'content_functions.php';
require_once 'error_handler.php';

// 2. Perform checks once
$isLoggedIn = isLoggedIn(); // Ensure this function checks session_id/user_id correctly
$userData = $isLoggedIn ? getCurrentUser() : null;

// 3. User count redirect logic (as per your existing code)
$user_count_result = $conn->query("SELECT COUNT(*) as count FROM users");
$user_count = ($user_count_result) ? $user_count_result->fetch_assoc()['count'] : 0;

if ($user_count == 0 && basename($_SERVER['PHP_SELF']) != 'index.php') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFlix | Streaming Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <script src="assets/js/user-clicker.js"></script>
</head>
<body>
    <div class="galaxy-bg" id="galaxy"></div>

    <header class="header-responsive">
        <div class="logo">
            <div class="logo-icon"><i class="fas fa-play-circle"></i></div>
            <div class="logo-text"><a href="index.php">TechFlix</a></div>
        </div>

        <button class="menu-toggle" id="mobileMenuBtn">
            <i class="fas fa-bars"></i>
        </button>

        <nav class="nav-responsive">
            <ul id="navMenu">
                <?php if ($isLoggedIn): ?>
                    <!-- Navigation for logged-in users -->
                    <li><a href="home.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="browse.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'browse.php' ? 'active' : ''; ?>">Browse</a></li>
                    <li><a href="movies.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'movies.php' ? 'active' : ''; ?>">Movies</a></li>
                    <li><a href="tvshows.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'tvshows.php' ? 'active' : ''; ?>">TV Shows</a></li>
                    
                    <li><a href="profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">Profile</a></li>
                    <?php if ($isLoggedIn && isset($userData['role']) && in_array($userData['role'], ['admin', 'superadmin'])): ?>
                    <li><a href="admin/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'admin/') !== false ? 'active' : ''; ?>">Admin</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Navigation for non-logged-in users -->
                    <li><a href="browse.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'browse.php' ? 'active' : ''; ?>">Browse</a></li>
                    <li><a href="movies.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'movies.php' ? 'active' : ''; ?>">Movies</a></li>
                    <li><a href="tvshows.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'tvshows.php' ? 'active' : ''; ?>">TV Shows</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <div class="user-actions">
            <!-- <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search..." id="searchInput">
            </div> -->
            
            <?php if ($isLoggedIn): ?>
                <!-- <div class="notification">
                    <i class="fas fa-bell"></i>
                </div> -->
                <div class="user-dropdown">
                    <div class="user-icon">
                        <img src="<?php echo isset($userData['profile_picture']) && !empty($userData['profile_picture']) ? $userData['profile_picture'] : 'assets/img/default-profile.jpg'; ?>" alt="User Profile"
                            style="height: 40px; width: 40px; border-radius: 50%; border: 0.1rem solid #ff00cc;">
                    </div>
                    <div class="dropdown-menu">
                        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                        <a href="watchlist.php"><i class="fas fa-bookmark"></i> My Watchlist</a>
                        <a href="subscription_plans.php"><i class="fas fa-crown"></i> Subscription</a>
                        <?php if (isset($userData['role']) && in_array($userData['role'], ['admin', 'superadmin'])): ?>
                        <a href="admin/index.php"><i class="fas fa-crown"></i> Admin Panel</a>
                        <?php endif; ?>
                        <a href="auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="auth/login.php" class="btn btn-outline">Login</a>
                    <a href="auth/register.php" class="btn btn-primary">Sign Up</a>
                </div>
            <?php endif; ?>
        </div>
    </header>
    <script>
        
// Fix for main.js loading issues
window.addEventListener('load', function() {
    // Check if main.js functions exist before calling them
    if (typeof InitUserClicker === 'function') {
        InitUserClicker();
    } else {
        console.warn('InitUserClicker not found, loading fallback');
        // Load fallback or create the function
        if (!window.InitUserClicker) {
            window.InitUserClicker = function() {
                console.log('Fallback UserClicker initialized');
            };
        }
    }
});
</script>