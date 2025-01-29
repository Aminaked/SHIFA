<?php
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_GET['user_id'];
    $other_user_id = $_GET['other_user_id'];


    $sql = "SELECT * FROM chats 
            WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
            ORDER BY timestamp ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $user_id, $other_user_id, $other_user_id, $user_id);

    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $messages = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($messages);
    } else {
        echo json_encode(["status" => "error", "error" => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
