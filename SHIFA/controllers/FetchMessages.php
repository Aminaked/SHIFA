<?php
require 'connection.php'; // Include your existing MySQLi connection file
require_once 'session.php';
header('Content-Type: application/json');

try {
    global $conn; // Access your existing MySQLi connection
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $user_id = $input['user_id'];
    $user_type = $input['user_type'];
    $recipient_id = $input['recipient_id'];
    $recipient_type = $input['recipient_type'];
    
    // Determine client and pharmacy IDs
    $clientId = $user_type == 'client' ? $user_id : $recipient_id;
    $pharmacyId = $user_type == 'pharmacy' ? $user_id : $recipient_id;
    
    // Get conversation ID
    $stmt = $conn->prepare("
        SELECT conversation_id FROM conversations 
        WHERE client_id = ? AND pharmacy_id = ?
    ");
    $stmt->bind_param("ii", $clientId, $pharmacyId);
    $stmt->execute();
    $result = $stmt->get_result();
    $conversation = $result->fetch_assoc();
    
    if (!$conversation) {
        echo json_encode([]);
        exit;
    }
    
    // Get messages
    $stmt = $conn->prepare("
        SELECT 
            chat_id,
            sender_id,
            sender_type,
            receiver_id,
            receiver_type,
            message,
            timestamp
        FROM chats
        WHERE conversation_id = ? AND is_deleted=0
        ORDER BY timestamp ASC
    ");
    $stmt->bind_param("i", $conversation['conversation_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = $result->fetch_all(MYSQLI_ASSOC);
    
    // Mark messages as read
    if (!empty($messages)) {
        $stmt = $conn->prepare("
            UPDATE chats 
            SET is_read = TRUE 
            WHERE conversation_id = ? 
            AND receiver_id = ?
        ");
        $stmt->bind_param("ii", $conversation['conversation_id'], $user_id);
        $stmt->execute();
    }
    
    echo json_encode($messages);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>