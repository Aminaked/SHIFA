<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link
      href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
   
   <link rel="stylesheet" href="../public/styles/MedDetails.css">
    <title>Document</title>
</head>
<body>
  
<header>
    <div class="logo"><i class="fa-solid fa-pills"></i> SHIFA <span>Online</span>
    </div>

    <div class="header-buttons">
      <button class="chat-btn">About Us</button>
      <a href="#" class="notification"><i class="fa-solid fa-bell"></i></a>
      <a href="ClientConversations.php" class="notification"><i class="bx bxs-message-rounded"></i></a>

      <div class="user-menu">
        <a href="#" class="login" onclick="toggleMenu(event)">
          <i class="fa-solid fa-user"></i>
        </a>
        <div class="menu-dropdown" id="menuDropdown">
          <a href="ClientProfile.php"><i class="bx bx-user-circle icon"></i>Profile</a>
          <a href="#"><i class="bx bx-box icon"></i>Orders</a>
          <a href="../views/logout.php"><i class="bx bx-log-out icon"></i>Log out</a>
        </div>
      </div>
      </div>
   
  </header>

<div class="details">
    <h2 id="brand-name"></h2>
    <p><strong>Pharmacy:</strong> 
           <span id="pharmacy-name" class="pharmacy-link"></span>
      </p>
    <p><strong>Address:</strong> <span id="address"></span></p>
    <p><strong>Distance:</strong> <span id="distance"></span></p>
    <p><strong>Stock:</strong> <span id="stock"></span></p>
    <p><strong>Generic Name:</strong> <span id="generic-name"></span></p>
    <div id="loading-spinner" class="hidden">
            <div class="spinner"></div>
            <span>Loading medication details from FDA...</span>
        </div>
    <h3>Uses</h3>
  <p id="indications">Loading...</p>
  
  <h3>Dosage</h3>
  <p id="dosage">Loading...</p>
  
  <h3>Warnings</h3>
  <p id="warnings">Loading...</p>
  
  <h3>Contraindications</h3>
  <p id="contraindications">Loading...</p>
    <button id="locate-button" class="locate-button">Locate Pharmacy</button>
  </div>
  <script src="../controllers/JavaScript/MedDetails.js" ></script> 
  
</body>
</html>