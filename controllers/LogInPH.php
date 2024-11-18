<?php
include 'connection.php';


$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM pharmacy WHERE email='$email' AND password='$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $_SESSION['email'] = $email;
    header("Location: ../views/PHhomepage.php");
} else {
    echo "email or password is incorrect";
}

$conn->close();
?>