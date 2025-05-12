<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'connection.php';
require_once 'session.php';
$conn = getDatabaseConnection();


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$email = isset($_POST['email']) ? $_POST['email'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

$sql = "SELECT client_id, Full_name, password FROM clients WHERE email = ?";
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

        http_response_code(200);
        // Redirect to client homepage
        header("Location: ../views/CLhomepage.php");
        exit;
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Email or password is incorrect']);
    }
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Email or password is incorrect']);
}

$stmt->close();
$conn->close();
?>
