<?php
session_start();
// Verify authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header('Location: login.php');
    exit();
}

// Verify recipient parameters
if (!isset($_GET['recipient_id']) || !isset($_GET['recipient_type'])) {
    die("Invalid chat request");
}

// Set user data for JS
$currentUser = [
    'id' => $_SESSION['user_id'],
    'type' => $_SESSION['user_type'],
    'name' => $_SESSION['user_name'] ?? 'You'
];


?>

