<?php
session_start();
include '../controllers/connection.php';

if (!isset($_SESSION['pharmacy_id'])) {
    echo "Unauthorized access.";
    exit;
}

$pharmacy_id = $_SESSION['pharmacy_id'];

$old_password = $_POST['old_password'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

// Fetch current password
$sql = "SELECT password FROM pharmacy WHERE pharmacy_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pharmacy_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Pharmacy not found.";
    exit;
}

$row = $result->fetch_assoc();

if (!password_verify($old_password, $row['password'])) {
    echo "Old password is incorrect.";
    exit;
}

if ($new_password !== $confirm_password) {
    echo "New passwords do not match.";
    exit;
}

$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$update = $conn->prepare("UPDATE pharmacy SET password = ? WHERE pharmacy_id = ?");
$update->bind_param("si", $hashed_password, $pharmacy_id);

if ($update->execute()) {
    echo "Password changed successfully.";
} else {
    echo "Failed to change password.";
}

$stmt->close();
$update->close();
$conn->close();
?>