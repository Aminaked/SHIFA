<?php
include 'connection.php';
require_once 'session.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $conn = getDatabaseConnection();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO clients (Full_name, phone_number, email, password) VALUES ('$full_name','$phone','$email','$hashed_password')";

    if ($conn->query($sql) === TRUE) {
        // Get the last inserted ID
        $client_id = $conn->insert_id;
        
        // Store user information in session
        $_SESSION['user_id'] = $client_id;
        $_SESSION['user_type'] = 'client';
        $_SESSION['user_name'] = $full_name;
        $_SESSION['email'] = $email;
        
        echo "User added successfully";
        header("location: ../views/CLhomepage.php");
        exit(); // Always exit after header redirect
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    
    $conn->close();
}
?>