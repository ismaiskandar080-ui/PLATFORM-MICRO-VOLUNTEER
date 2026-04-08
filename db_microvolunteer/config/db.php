<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'microvolunteer');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

function requireLogin($role = null) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /db_microvolunteer/auth/login.php");
        exit();
    }
    if ($role && $_SESSION['role'] !== $role) {
        header("Location: /db_microvolunteer/auth/login.php");
        exit();
    }
}

function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>
