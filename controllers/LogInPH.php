<?php
include 'connection.php';

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM pharmacy WHERE email='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hashed_password = $row['password'];

    if (password_verify($password, $hashed_password)) {
        $_SESSION['email'] = $email;
        header("Location: ../views/PHhomepage.php");
    } else {
        echo "email or password is incorrect";
    }
} else {
    echo "email or password is incorrect";
}

$conn->close();
?>
