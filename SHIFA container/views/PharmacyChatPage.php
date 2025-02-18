<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Chat</title>
    <style>
        #chat-box {
            width: 400px;
            height: 500px;
            border: 1px solid #ccc;
            padding: 10px;
            overflow-y: scroll;
            margin-bottom: 10px;
        }
        #message-input {
            width: 300px;
            padding: 5px;
        }
        #send-button {
            padding: 5px 10px;
        }
    </style>
</head>
<body>
    <h1>Pharmacy Chat</h1>
    <div id="chat-box">
        <div id="messages"></div>
    </div>
    <form id="chat-form">
        <input type="text" id="message-input" placeholder="Type your message..." required>
        <button type="submit" id="send-button">Send</button>
    </form>

    <script>
        // Fetch receiver ID from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const receiverId = urlParams.get('receiver_id');

        // Store receiver ID in sessionStorage
        if (receiverId) {
            sessionStorage.setItem("receiver_id", receiverId);
        } else {
            console.error("Receiver ID not found in URL parameters.");
        }
    </script>
    <script src="PharmacyChat.js"></script> <!-- Link to pharmacy-side JS -->
</body>
</html>