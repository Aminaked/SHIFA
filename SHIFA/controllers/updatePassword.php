<?php
include 'connection.php';
require_once 'session.php';

$client_id = $_SESSION['user_id'];
$old_pass = $_POST['old_password'];
$new_pass = $_POST['new_password'];
$confirm_pass = $_POST['confirm_password'];

if ($new_pass !== $confirm_pass) {
    echo "Passwords do not match!";
    exit;
}

$sql = "SELECT password FROM clients WHERE client_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!password_verify($old_pass, $row['password'])) {
    echo "Incorrect old password!";
    exit;
}

$new_pass_hashed = password_hash($new_pass, PASSWORD_DEFAULT);
$update_sql = "UPDATE clients SET password=? WHERE client_id=?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("si", $new_pass_hashed, $client_id);
$update_stmt->execute();

echo "Password updated successfully!";
?>