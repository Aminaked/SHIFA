<!-- forget_password.html -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="../public/styles/reset_password.css" />
  <link
      href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css"
      rel="stylesheet"
    />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <title>Forget password</title>
</head>
<body>
    
<header>
    <div class="logo"><i class="fa-solid fa-pills"></i> SHIFA <span>Online</span>
</div>
     
      <div class="header-buttons">
      <a href="../views/aboutus.php"> <button class="chat-btn">About Us</button></a>
        <a href="../views/AppUsers.php" class="login"> LOG IN <i class="fa-solid fa-user"></i></a>
      </div>
    </header>
    
  <div class="container">
          
     <?php
      session_start();
       if (isset($_SESSION['error'])) {
         echo "<p style='color:red; text-align:center;'>" . $_SESSION['error'] . "</p>";
         unset($_SESSION['error']);
      }
      ?>
    <form action="../controllers/reset_password_form.php" method="POST">
      <h2>Forget password</h2>
      <div class="input-text">
        <input type="email" name="email" placeholder="Enter your email" required />
      </div>
      
      <button type="submit">Send Email</button>
    </form>
  </div>
</body>
</html>