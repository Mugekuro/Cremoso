<?php


// Include database connection first
require_once __DIR__ . '/../config/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Check if user is staff
function isStaff() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'staff';
}

// Redirect if not logged in
function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: /cremoso_system/login.php');
        exit();
    }
}

// Redirect if not admin
function redirectIfNotAdmin() {
    if (!isAdmin()) {
        header('Location: /cremoso_system/index.php');
        exit();
    }
}

// Redirect if not staff
function redirectIfNotStaff() {
    if (!isStaff()) {
        header('Location: /cremoso_system/index.php');
        exit();
    }
}
?>