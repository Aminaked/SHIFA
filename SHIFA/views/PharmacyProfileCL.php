
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/styles/PharmacyProfile.css">
    <title>SHIFA-Pharmacy Profile</title>
    
</head>
<body>
    <header>
        <h1>Pharmacy Profile</h1>
    </header>

    <section class="pharmacy-details">
       <p id="pharmacy_name" ></p> 
       <p id="email" ></p>
       <p id="phone_number" ></p>
        <p id="address" ></p>
        <button id="get-directions" >get directions</button>
    </section>
    

    <section class="chat-section">
        <h2>Contact Us</h2>
        <a href="ClientChatPage.php?pharmacy_id=<?php echo $pharmacy_id; ?>" class="chat-button">
            
            Start Chat
        </a>
    </section>

    <footer>
        <p>&copy; 2025 Medication Finder</p>
    </footer>
    <script src="../controllers/JavaScript/PharmacyProfileCL.js" ></script>
</body>
</html>
