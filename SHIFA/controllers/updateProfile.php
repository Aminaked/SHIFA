<?php
include 'connection.php';
require_once 'session.php';

$client_id = $_SESSION['user_id'];
$full_name = $_POST['full_name'];
$email = $_POST['email'];
$phone = $_POST['phone_number'];

$sql = "UPDATE clients SET full_name=?, email=?, phone_number=? WHERE client_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $full_name, $email, $phone, $client_id);

if ($stmt->execute()) {
    echo "Update succcessful";
} else {
    echo "Update Failed";
}

$stmt->close();
$conn->close();
?>