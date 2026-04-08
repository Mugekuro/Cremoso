<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if (isAdmin()) {
    header('Location: admin/dashboard.php');
} else {
    header('Location: staff/dashboard.php');
}
?>