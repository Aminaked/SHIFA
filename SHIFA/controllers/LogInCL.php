<?php 


include 'connection.php';
require_once 'session.php';
$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT client_id, password FROM clients WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hashed_password = $row['password'];

    if (password_verify($password, $hashed_password)) {
        // Store login data in session
        $_SESSION['user_id'] = $row['client_id'];
        $_SESSION['user_type'] = 'client';
        $_SESSION['user_name'] = $row['Full_name'];
        $_SESSION['email'] = $email;

        // Redirect with JavaScript to store sessionStorage values
        echo "<script>
           
            window.location.href = '../views/CLhomepage.php';
        </script>";
    } else {
        echo "Email or password is incorrect";
    }
} else {
    echo "Email or password is incorrect";
}

$stmt->close();
$conn->close();
?>
