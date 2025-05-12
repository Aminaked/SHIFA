<?php
require_once 'connection.php';
require_once 'session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SHIFA-main/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = $_POST['email'];

     $conn = getDatabaseConnection();

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the email exists in the database
    $query = "SELECT * FROM clients WHERE email = '$email'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        // If email exists, generate a random token
        $reset_token = bin2hex(random_bytes(16)); // Generate a 32-character token

        // Update the token in the database
        $update_query = "UPDATE clients SET reset_token = '$reset_token' WHERE email = '$email'";
        if ($conn->query($update_query) === TRUE) {
            // PHPMailer settings
            $mail = new PHPMailer(true);
            $mail->SMTPDebug = 2;  
            $mail->Debugoutput = 'html';

            try {
                // SMTP settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';  // Replace with your SMTP server settings
                $mail->SMTPAuth = true;
                $mail->Username = 'kimf94006@gmail.com';  // Your email address
                $mail->Password = 'ecoibrsvvxfdhqta';  // Your email password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Set email content
                $mail->setFrom('kimf94006@gmail.com', 'Your Company');
                $mail->addAddress($email);  // Send to the entered email address

                // Email body content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body = 'To reset your password, please click on the following link: <a href="http://localhost/SHIFA-main/SHIFA/controllers/reset_password.php?token=' . $reset_token . '">Reset your password</a>';// Send the email
                $mail->send();
                echo 'A password reset link has been sent to your email.';
            } catch (Exception $e) {
                echo "Message could not be sent. Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Error updating the token: " . $conn->error;
        }
    } else {
        $_SESSION['error'] = "The email address is not registered in the database!";
        header("Location: ../views/forget_password.html"); // Redirect back to the forgot password page with an error message
        exit();
    }

    $conn->close();
}
?>