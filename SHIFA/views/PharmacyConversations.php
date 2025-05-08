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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<div class="container">
        <h1>Your Conversations</h1>
        <div id="conversations-list" class="conversation-list"></div>
    </div>
<script>
    const currentUser = <?= json_encode($currentUser) ?>;
</script>
    <script src="../controllers/JavaScript/FetchConversations.js" ></script>
</body>
</html>