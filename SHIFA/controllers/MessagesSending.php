<?php
// db_connection.php - Use your DataBaseConnection class for this
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_POST['sender_id'];
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];
    

    $sql = "INSERT INTO chats (sender_id, receiver_id, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $sender_id, $receiver_id, $message);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message_id" => $stmt->insert_id]);
    } else {
        echo json_encode(["status" => "error", "error" => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
