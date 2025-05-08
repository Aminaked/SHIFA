<?php
require_once 'connection.php';
require_once 'session.php';

if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized access.";
    exit;
}

$pharmacy_id = $_SESSION['user_id'];

$pharmacy_name = $_POST['pharmacy_name'];
$pharmacy_liscense_number = $_POST['pharmacy_liscense_number'];
$email = $_POST['email'];
$phone_number = $_POST['phone_number'];

$sql = "UPDATE pharmacy SET pharmacy_name = ?, pharmacy_liscense_number = ?, email = ?, phone_number = ? WHERE pharmacy_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssi", $pharmacy_name, $pharmacy_liscense_number, $email, $phone_number, $pharmacy_id);

if ($stmt->execute()) {
    echo "Pharmacy info updated successfully.";
} else {
    echo "Failed to update pharmacy info.";
}

$stmt->close();
$conn->close();
?>