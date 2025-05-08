<!DOCTYPE html>
<html lang="ar">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SHIFA Online</title>

  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../public/styles/PharmacyHomePage.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body>

  <header>
    <div class="logo"><i class="fa-solid fa-pills"></i> SHIFA <span>Online</span>
    </div>

    <div class="header-buttons">
    <a href="../views/aboutus.php"> <button class="chat-btn">About Us</button></a>
      <a href="notifications.php" class="notification"><i class="fa-solid fa-bell"></i></a>

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
  <section class="content">
    <div class="dashboard-cards">
        <div class="card orders">
            <div class="card-icon">
                <i class="fas fa-prescription-bottle-alt"></i>
            </div>
            <h2>Orders</h2>
            <p>View and manage incoming medicine orders.</p>
            <a href="orders.php" class="card-button">View Orders <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="card reservations">
            <div class="card-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <h2>Reservations</h2>
            <p>Check and confirm reserved medicines.</p>
            <a href="reservations.php" class="card-button">View Reservations <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

    <script src="../controllers//JavaScript/PharmacyHomePage.js"></script>
</body>

</html>