<?php
session_start();
// Verify authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header('Location: login.php');
    exit();
}

// Verify recipient parameters
if (!isset($_GET['recipient_id']) || !isset($_GET['recipient_type'])) {
    die("Invalid chat request");
}

// Set user data for JS
$currentUser = [
    'id' => $_SESSION['user_id'],
    'type' => $_SESSION['user_type'],
    'name' => $_SESSION['user_name'] ?? 'You'
];


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medication Finder Chat</title>
    <link rel="stylesheet" href="../public/styles/Chat.css">
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <h2>Medication Finder Chat</h2>
            <div class="recipient-info">
                <img src="images/<?= $recipient['type'] ?>_avatar.png" 
                     alt="Recipient" class="recipient-avatar">
                <div>
                    <div id="recipient-name"><?= htmlspecialchars($recipient['name']) ?></div>
                    <div id="recipient-status">
                        <span class="status-indicator offline"></span>
                        <span>Offline</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="chat-messages" id="chat-messages"></div>
        
        <div class="chat-input-area">
            <input type="text" id="message-input" placeholder="Type your message...">
            <button id="send-button">Send</button>
        </div>
    </div>

    <script>
        // Pass PHP data to JavaScript
        const pharmacyData = JSON.parse(sessionStorage.getItem('medicationDetails'));
        const currentUser = <?= json_encode($currentUser) ?>;
        const recipient = {
    id: pharmmacyData.pharmacy_id,
    type: 'pharmacy',
    name: pharmmacyData.pharmacy_name
};
    </script>
    
    <script src="../controllers/JavaScript/Chat.js"></script>
</body>
</html>