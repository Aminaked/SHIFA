<?php
include 'connection.php';
require_once 'session.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT); // Encrypt the password

    // Connect to the database
    $conn = new mysqli('localhost', 'root', '', 'shifa',3307);  // Replace these values

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Update the password in the database
    $update_query = "UPDATE clients SET password = '$new_password', reset_token = NULL WHERE reset_token = '$token'";
    if ($conn->query($update_query) === TRUE) {
        echo 'Password has been successfully updated!';
    } else {
        echo 'Error updating password: ' . $conn->error;
    }

    $conn->close();
}
?>