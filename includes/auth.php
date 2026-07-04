<?php
// Authentication and authorization functions

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has admin role
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'superadmin');
}

/**
 * Check if user has super admin role
 */
function isSuperAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'superadmin';
}

/**
 * Get current user data
 */
function getCurrentUser() {
    global $conn;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    if (!$stmt) {
        error_log("Database error: " . $conn->error);
        return null;
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Require login - redirect to login if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: auth/login.php");
        exit();
    }
}

/**
 * Require admin access - redirect to home if not admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: ../home.php");
        exit();
    }
}

/**
 * Require super admin access
 */
function requireSuperAdmin() {
    if (!isSuperAdmin()) {
        header("Location: ../home.php");
        exit();
    }
}

/**
 * Login user
 */
function loginUser($user_id, $username, $role = 'user') {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['user_role'] = $role;
    $_SESSION['login_time'] = time();
}

/**
 * Logout user
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = array();
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
}

/**
 * Check if user can access content
 */
function canAccessContent($content_id) {
    global $conn;
    
    if (!isLoggedIn()) {
        return false;
    }
    
    // Admin users can access all content
    if (isAdmin()) {
        return true;
    }
    
    // Check if content is available for regular users
    $stmt = $conn->prepare("SELECT is_public FROM content WHERE id = ?");
    if (!$stmt) {
        error_log("Database error: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("i", $content_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($content = $result->fetch_assoc()) {
        return $content['is_public'] == 1;
    }
    
    return false;
}

/**
 * Check if user account is active
 */
function isUserActive($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT status FROM users WHERE id = ?");
    if (!$stmt) {
        error_log("Database error: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        return $user['status'] === 'active';
    }
    
    return false;
}

/**
 * Validate user credentials
 */
function validateCredentials($username, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, username, password, role, status FROM users WHERE username = ? OR email = ?");
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $conn->error
        ];
    }
    
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        // Check if account is active
        if ($user['status'] !== 'active') {
            return [
                'success' => false,
                'message' => 'Account is ' . $user['status'] . '. Please contact administrator.'
            ];
        }
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            return [
                'success' => true,
                'user_id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ];
        }
    }
    
    return [
        'success' => false,
        'message' => 'Invalid username or password'
    ];
}

/**
 * Register new user
 */
function registerUser($username, $email, $password, $full_name = '') {
    global $conn;
    
    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $conn->error
        ];
    }
    
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return [
            'success' => false,
            'message' => 'Username or email already exists'
        ];
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role, status) VALUES (?, ?, ?, ?, 'user', 'active')");
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $conn->error
        ];
    }
    
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $full_name);
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'user_id' => $stmt->insert_id
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Registration failed: ' . $stmt->error
        ];
    }
}

/**
 * Update user last login time
 */
function updateLastLogin($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }
}
?>