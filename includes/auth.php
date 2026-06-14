<?php
/**
 * BookVerse - Authentication Functions
 * Handles login, registration, session management
 */

require_once __DIR__ . '/db.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Register a new user
 */
function registerUser($name, $email, $password, $phone) {
    global $conn;

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Email already registered.'];
    }
    $stmt->close();

    // Hash password
    $hashed = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $hashed, $phone);

    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'message' => 'Registration successful!'];
    } else {
        $stmt->close();
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

/**
 * Login a user
 */
function loginUser($email, $password) {
    global $conn;

    $stmt = $conn->prepare("SELECT id, name, email, password, role, profile_picture FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    // Set session
    $_SESSION['user_id']      = $user['id'];
    $_SESSION['user_name']    = $user['name'];
    $_SESSION['user_email']   = $user['email'];
    $_SESSION['user_role']    = $user['role'];
    $_SESSION['user_picture'] = $user['profile_picture'];

    // Set remember-me cookie (7 days)
    setcookie('bv_user', $user['id'], time() + (7 * 24 * 3600), '/');

    return ['success' => true, 'message' => 'Login successful!', 'role' => $user['role']];
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if logged in user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Require login - redirect if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

/**
 * Require admin - redirect if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

/**
 * Logout user
 */
function logoutUser() {
    session_unset();
    session_destroy();
    setcookie('bv_user', '', time() - 3600, '/');
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

/**
 * Get current user data
 */
function getCurrentUser() {
    global $conn;
    if (!isLoggedIn()) return null;

    $stmt = $conn->prepare("SELECT id, name, email, phone, address, profile_picture, role, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}
