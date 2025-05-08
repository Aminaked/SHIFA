<?php
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Connect to the database
    $conn = new mysqli('localhost', 'root', '', 'shifa', 3307);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check the token in the database
    $query = "SELECT * FROM clients WHERE reset_token = '$token'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        // If the token exists, display the password reset form
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css"rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  
            <title>Reset Password</title>
            <link rel="stylesheet" href="../public/styles/reset_password.css">
        </head>
        <body>
         
           <header>
    <div class="logo"><i class="fa-solid fa-pills"></i> SHIFA <span>Online</span></div>
     <div class="header-buttons">
      <a href="../views/aboutus.php"> <button class="chat-btn">About Us</button></a>
        <a href="../views/AppUsers.php" class="login"> LOG IN <i class="fa-solid fa-user"></i></a>
      </div>
    </header> 

         <div class="container">
        <form class="reset-password-form" action="update_password.php" method="POST">
        <h2>Reset Password</h2>
        <input type="hidden" name="token" value="' . $token . '">

        <div class="input-text">
            <input type="password" name="new_password" id="new_password" required placeholder="New Password">
            
        </div>

        <button type="submit">Change Password</button>
    </form>
</div>
        </body>
        </html>
        ';
    } else {
        echo 'The link is invalid or expired.';
    }

    $conn->close();
} else {
    echo 'The link is invalid or not found.';
}
?>