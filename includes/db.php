<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // XAMPP default user
define('DB_PASS', '');           // XAMPP default password (blank)
define('DB_NAME', 'matka_king'); // XAMPP local database name

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

function sanitize($conn, $data) {
    return $conn->real_escape_string(trim($data));
}
?>
