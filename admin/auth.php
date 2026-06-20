<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
require_once '../includes/db.php';
require_once '../includes/functions.php';
?>
