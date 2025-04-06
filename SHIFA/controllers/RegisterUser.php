<?php

include 'connection.php';
require_once 'session.php';
if ($_SERVER["REQUEST_METHOD"] == "POST" ) {
    $full_name = $_POST['full_name'];
    $phone= $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO clients (Full_name, phone_number, email, password) VALUES ('$full_name','$phone','$email','$hashed_password')";

   
    if ($conn->query($sql) === TRUE) {
        echo "User added successfully";
        header("location: ../views/CLhomepage.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
    $conn->close();

?>