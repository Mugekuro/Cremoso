<?php
$host = 'localhost';
$dbname = 'cremoso_db';
$username = 'root';
$password = '';



try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set timezone to match PHP timezone
    date_default_timezone_set('Asia/Manila');
    $pdo->exec("SET time_zone = '+08:00'");
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}




// Make $pdo available globally
global $pdo;


/*

// $servername = $host
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
} else {
    echo "DB Connection Success";
}


*/
    
    
?>