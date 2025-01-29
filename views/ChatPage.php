<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/styles/Chat.css">
    <title>SHIFA- Chat</title>
</head>

<body>
    <div id="chat-container">
        <div id="messages" style="height: 400px; overflow-y: scroll;"></div>
        <form id="chat-form">
            <input type="text" id="message-input" placeholder="Type your message..." required />
            <button type="submit">Send</button>
        </form>
    </div>
    <script src="../controllers/JavaScript/Chatjs"></script>
</body>

</html>