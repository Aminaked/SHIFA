<?php
include 'connection.php';

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM clients WHERE email='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hashed_password = $row['password'];

    if (password_verify($password, $hashed_password)) {
        $_SESSION['email'] = $email;
        header("Location: ../views/CLhomepage.php");
    } else {
        echo "Email or password is incorrect";
    }
} else {
    echo "Email or password is incorrect";
}

$conn->close();
?>
