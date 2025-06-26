<?php
require_once '../config/database.php';
require_once '../config/constants.php';

function registerUser($conn, $username, $email, $password) {
    // Check if username or email already exists
    if (usernameExists($conn, $username)) {
        return false;
    }
    
    if (emailExists($conn, $email)) {
        return false;
    }
    
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    return $stmt->execute([$username, $email, $passwordHash]);
}

function loginUser($conn, $username, $password) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

function usernameExists($conn, $username) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch() !== false;
}

function emailExists($conn, $email) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch() !== false;
}

function updateUserProfile($conn, $userId, $username, $email, $bio, $avatar, $password) {
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, bio = ?, avatar = ?, password = ?, updated_at = NOW() WHERE id = ?");
    return $stmt->execute([$username, $email, $bio, $avatar, $password, $userId]);
}

function getUserById($conn, $userId) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === ROLE_ADMIN;
}

function isAuthor() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === ROLE_AUTHOR || $_SESSION['role'] === ROLE_ADMIN);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . SITE_URL . "/login.php");
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: " . SITE_URL . "/index.php");
        exit;
    }
}

function requireAuthor() {
    requireLogin();
    if (!isAuthor()) {
        header("Location: " . SITE_URL . "/index.php");
        exit;
    }
}
?>