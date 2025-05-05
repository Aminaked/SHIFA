<?php
session_start();
// Verify authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header('Location: login.php');
    exit();
}



// Set user data for JS
$currentUser = [
    'id' => $_SESSION['user_id'],
    'type' => $_SESSION['user_type'],
    'name' => $_SESSION['user_name']
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
                <img src="images/<?= $recipient['type'] ?>.jpg" alt="Recipient" class="recipient-avatar">
                <div>
                    <div id="recipient-name">talebca</div>
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
            <button id="send-button"> Send</button>
        </div>
    </div>

    <script>

        const currentUser = <?= json_encode($currentUser) ?>;

        let recipient = null;

        // Check conversation flow data
        const convoSession = JSON.parse(sessionStorage.getItem('convoDetails'));
        if (convoSession) {
            recipient = {
                id: convoSession.recipient_id,  // Changed from pharmacy_id to match your earlier structure
                type: convoSession.recipient_type || 'pharmacy', // Fallback type
                name: convoSession.recipient_name
            };
           // sessionStorage.removeItem('convoDetails'); // Clean up
        }

        // Check search flow data (only if recipient not already set)
        if (!recipient) {
            const searchSession = JSON.parse(sessionStorage.getItem('medicationDetails'));
            if (searchSession) {
                recipient = {
                    id: searchSession.pharmacy_id,
                    type: 'pharmacy', // Explicit since search comes from pharmacy
                    name: searchSession.pharmacy_name
                };
               // sessionStorage.removeItem('medicationDetails'); // Clean up
            }
        }

        // Final validation
        if (!recipient) {
            console.error('No recipient data found');
            // Redirect or handle error
            window.location.href = '/CLhomepage.php';
        }

    </script>

    <script src="../controllers/JavaScript/Chat.js"></script>
</body>

</html>