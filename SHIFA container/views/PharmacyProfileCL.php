
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
        
       
        <?php include('../controllers/PharmacyProfile.php'); ?>
    </section>
    

    <section class="chat-section">
        <h2>Contact Us</h2>
        <a href="ClientChatPage.php?pharmacy_id=<?php echo $pharmacy_id; ?>" class="chat-button">
            <img src="../public/images/chat_icon.png" alt="Chat with Pharmacy" />
            Start Chat
        </a>
    </section>

    <footer>
        <p>&copy; 2025 Medication Finder</p>
    </footer>
</body>
</html>
