<?php

include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" ) {
    $full_name = $_POST['full_name'];
    $phone= $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $datebirth= $_POST['datebirth'];

   
    $sql = "INSERT INTO clients (Full_name, phone_number, email, password, date_birth) VALUES ('$full_name','$phone','$email','$password','$datebirth')";

   
    if ($conn->query($sql) === TRUE) {
        echo "User added successfully";
        header("location: ../views/homepage.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
    $conn->close();

?>