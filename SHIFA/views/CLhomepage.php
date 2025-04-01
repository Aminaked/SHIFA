<!DOCTYPE html>
<html lang="ar">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SHIFA Online</title>

  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../public/styles/CLhomepage.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body>

  <header>
    <div class="logo"><i class="fa-solid fa-pills"></i> SHIFA <span>Online</span>
    </div>

    <div class="header-buttons">
      <button class="chat-btn">About Us</button>
      <a href="#" class="notification"><i class="fa-solid fa-bell"></i></a>

      <div class="user-menu">
        <a href="#" class="login" onclick="toggleMenu(event)">
          <i class="fa-solid fa-user"></i>
        </a>
        <div class="menu-dropdown" id="menuDropdown">
          <a href="#"><i class="bx bx-user-circle icon"></i>Profile</a>
          <a href="#"><i class="bx bx-box icon"></i>Orders</a>
          <a href="#"><i class="bx bx-log-out icon"></i>Log out</a>
        </div>
      </div>
    </div>
  </header>

  <div class="container">
    <section class="hero">
      <h1>Welcome to your <br> <span>SHIFA </span> account!</h1>
      <p class="subtitle">Search and get your medicines easily</p>
      <p>Your trusted source for finding and reserving medications.</p>
      <br>
      <form id="searchForm" class="search-box">
        
          <input name="medication" type="text" placeholder="Search your medication here..">
          <button>Search</button>
        
      </form>
    </section>

    <div class="image-container">
      <img src="../public/images/d22.jpg" alt="Illustration Right" />
    </div>
    
  </div>
  <div id="results">
   
</div>
  <script src="../controllers/JavaScript/CLHomePage.js"></script>

</body>

</html>