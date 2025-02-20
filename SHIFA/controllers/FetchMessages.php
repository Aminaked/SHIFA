<?php
require 'connection.php';

$user_id = $_GET['user_id']; // The logged-in user

$result = $db->query("
    SELECT * FROM chats 
    WHERE receiver_id = ? OR sender_id = ? 
    ORDER BY created_at ASC", 
    [$user_id, $user_id]
);

$messages = $result->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($messages);
?>
