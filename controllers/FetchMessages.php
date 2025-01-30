<?php

require 'connection.php';

// Ensure client_id and pharmacy_id are in session
if (!isset($_SESSION['client_id']) || !isset($_SESSION['pharmacy_id'])) {
    die("Session data not found.");
}

$client_id = $_SESSION['client_id'];
$pharmacy_id = $_SESSION['pharmacy_id'];

// Fetch messages
$stmt = $pdo->prepare("SELECT * FROM chats WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY timestamp ASC");
$stmt->execute([$client_id, $pharmacy_id, $pharmacy_id, $client_id]);

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return the messages in JSON format
echo json_encode($messages);
?>
