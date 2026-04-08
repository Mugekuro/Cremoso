<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

session_destroy();
header('Location: login.php');
exit();
?>