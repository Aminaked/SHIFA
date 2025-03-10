<?php 

include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT pharmacy_id, email, password, status FROM pharmacy WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['password'];

        if (password_verify($password, $hashed_password)) {
            if ($row['status'] === 'pending') {
                // Redirect to pending page if account status is pending
                header('Location: ../views/PH_Pending_Statue.php');
                exit;
            } elseif ($row['status'] === 'active') {
                // Store user data in PHP session
                $_SESSION['pharmacy_id'] = $row['pharmacy_id'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['user_role'] = "pharmacy";

                // Redirect with JavaScript to store in sessionStorage
                echo "<script>
                    sessionStorage.setItem('user_role', 'pharmacy'); 
                    sessionStorage.setItem('user_id', '{$row['pharmacy_id']}');
                    window.location.href = '../views/PHhomepage.php';
                </script>";
                exit;
            }
        }
    }

    // If login fails, show a generic error message
    echo "Invalid email or password.";
    $stmt->close();
}

$conn->close();
?>
