
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../public/styles/ClientProfile.css">
    <title>SHIFA-Pharmacy Profile</title>
    
</head>
<body>
<header>
    <div class="logo"><i class="fa-solid fa-pills"></i> SHIFA <span>Online</span>
    </div>

    <div class="header-buttons">
    <a href="../views/aboutus.php"> <button class="chat-btn">About Us</button></a>
    <a href="#" class="notification"><i class="fa-solid fa-bell"></i></a>
   
    <div class="user-menu">
        <a href="#" class="login" onclick="toggleMenu(event)">
          <i class="fa-solid fa-user"></i>
        </a>
        <div class="menu-dropdown" id="menuDropdown">
          <a href="../views/PharmacyProfilePH.php"><i class="bx bx-user-circle icon"></i>Profile</a>
          
          <a href="../views/logout.php"><i class="bx bx-log-out icon"></i>Log out</a>
        </div>
      </div>
       
    </div>
  </header>
  

    <section class="pharmacy-details">
    
       
        <?php include('../controllers/PharmacyProfile.php'); ?>
       
    </section>
    

    

    <footer>
        <p>&copy; 2025 Medication Finder</p>
    </footer>
    <script src="../controllers//JavaScript/PharmacyHomePage.js"></script>
</body>
</html>
