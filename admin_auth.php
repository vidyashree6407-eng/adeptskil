<?php
/**
 * Admin Authentication Handler
 * Handles login, session management, and logout
 */

session_start();

// Admin credentials (change in production!)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'adeptskil2024');
define('SESSION_TIMEOUT', 3600); // 1 hour
define('REMEMBER_TIMEOUT', 604800); // 7 days

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Get current logged-in user
 */
function getLoggedInUser() {
    return isset($_SESSION['admin_user']) ? $_SESSION['admin_user'] : null;
}

/**
 * Logout user
 */
function logout() {
    session_destroy();
    setcookie('admin_remember', '', time() - 3600, '/');
    header('Location: admin-login.html?error=logout');
    exit;
}

/**
 * Handle login request
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? 1 : 0;
    
    // Validate credentials
    if ($username === ADMIN_USERNAME && password_verify($password, '$2y$10$YourHashedPasswordHere')) {
        // Hash verification (fallback to plain text for demo)
        $valid = false;
    } elseif ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $valid = true;
    } else {
        $valid = false;
    }
    
    if ($valid) {
        // Set session
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $username;
        $_SESSION['login_time'] = time();
        
        // Set remember-me cookie if requested
        if ($remember) {
            setcookie('admin_remember', base64_encode($username . ':' . hash('sha256', $password . ADMIN_PASSWORD)), time() + 604800, '/');
        }
        
        // Redirect to dashboard
        header('Location: admin_dashboard.php');
        exit;
    } else {
        header('Location: admin-login.html?error=invalid');
        exit;
    }
}

// Check remember-me cookie
if (!isLoggedIn() && isset($_COOKIE['admin_remember'])) {
    $cookie_data = base64_decode($_COOKIE['admin_remember']);
    list($cookie_user, $cookie_hash) = explode(':', $cookie_data);
    
    if ($cookie_user === ADMIN_USERNAME && $cookie_hash === hash('sha256', ADMIN_PASSWORD . ADMIN_PASSWORD)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $cookie_user;
        $_SESSION['login_time'] = time();
    }
}

// Check session timeout
if (isLoggedIn() && time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
    logout();
}

// Redirect if trying to access admin page without login
if (!isLoggedIn() && basename($_SERVER['PHP_SELF']) !== 'admin-login.html' && strpos($_SERVER['PHP_SELF'], 'admin') !== false) {
    header('Location: admin-login.html?error=unauthorized');
    exit;
}
?>
