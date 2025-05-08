<?php


header('Content-Type: application/json');

// Error reporting for development
ini_set('display_errors', 0);
ini_set('log_errors', 1);
require_once 'connection.php';
require_once 'session.php';
// Verify user authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

$userType = $_SESSION['user_type'];
$userId = $_SESSION['user_id'];

try {
    if ($userType === 'client') {
        $sql = "SELECT c.conversation_id, p.pharmacy_id, p.pharmacy_name, 
                       c.last_message, c.last_message_time
                FROM conversations c
                INNER JOIN pharmacy p ON c.pharmacy_id = p.pharmacy_id
                WHERE c.client_id = ?";
    } else {
        $sql = "SELECT c.conversation_id, cl.client_id, cl.Full_name
                FROM conversations c
                INNER JOIN clients cl ON c.client_id = cl.client_id
                WHERE c.pharmacy_id = ?";
    }
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $conversations = [];
    while ($row = $result->fetch_assoc()) {
        $conversations[] = [
            'conversation_id' => $row['conversation_id'],
            'recipient_id' => $userType === 'client' ? $row['pharmacy_id'] : $row['client_id'],
            'name' => $userType === 'client' ? $row['pharmacy_name'] : $row['Full_name'],
            'recipient_type' => $userType === 'client' ? 'pharmacy' : 'client'
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($conversations);

} catch (Exception $e) {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['error' => $e->getMessage()]);
}