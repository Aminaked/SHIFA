<?php
include 'connection.php';


$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM clients WHERE email='$email' AND password='$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $_SESSION['email'] = $email;
    
    header("Location: ../views/CLhomepage.php");
} else {
    echo "email or password is incorrect";
}

$conn->close();
?>