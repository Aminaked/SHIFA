<?php

require './vendor/autoload.php';
require __DIR__ . '/../connection.php'; 

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $users;
    protected $conn;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->users = [];
        global $conn; // Access your existing MySQLi connection
        $this->conn = $conn;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if ($data['type'] == 'register') {
            $this->users[$from->resourceId] = [
                'id' => $data['id'],
                'type' => $data['user_type'],
                'conn' => $from
            ];
            return;
        }

        if ($data['type'] == 'message') {
            $senderId = $this->users[$from->resourceId]['id'];
            $senderType = $this->users[$from->resourceId]['type'];
            $recipientId = $data['to_id'];
            $recipientType = $data['to_type'];
            $message = $data['message'];
            
            try {
                $conversationId = $this->getConversationId($senderId, $senderType, $recipientId, $recipientType);
                $this->saveMessageToDatabase($senderId, $recipientId, $message, $conversationId);
                $this->updateConversation($conversationId, $message);
                
                $messageData = [
                    'type' => 'message',
                    'from_id' => $senderId,
                    'from_type' => $senderType,
                    'message' => $message,
                    'timestamp' => time(),
                    'avatar' => $senderType == 'client' ? 'client_avatar.png' : 'pharmacy_avatar.png',
                    'conversation_id' => $conversationId
                ];
                
                // Send to recipient if online
                foreach ($this->users as $userId => $user) {
                    if ($user['id'] == $recipientId && $user['type'] == $recipientType) {
                        $user['conn']->send(json_encode($messageData));
                        break;
                    }
                }
                
                // Send back to sender
                $from->send(json_encode($messageData));
                
            } catch (Exception $e) {
                error_log("Database error: " . $e->getMessage());
            }
        }
    }

    protected function getConversationId($senderId, $senderType, $recipientId, $recipientType) {
        $clientId = $senderType == 'client' ? $senderId : $recipientId;
        $pharmacyId = $senderType == 'pharmacy' ? $senderId : $recipientId;
        
        $stmt = $this->conn->prepare("
            SELECT conversation_id FROM conversations 
            WHERE client_id = ? AND pharmacy_id = ?
        ");
        $stmt->bind_param("ii", $clientId, $pharmacyId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['conversation_id'];
        }
        
        $stmt = $this->conn->prepare("
            INSERT INTO conversations (client_id, pharmacy_id)
            VALUES (?, ?)
        ");
        $stmt->bind_param("ii", $clientId, $pharmacyId);
        $stmt->execute();
        return $this->conn->insert_id;
    }
    
    protected function saveMessageToDatabase($senderId, $recipientId, $message, $conversationId) {
        $stmt = $this->conn->prepare("
            INSERT INTO chats 
            (sender_id, receiver_id, message, conversation_id)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iisi", $senderId, $recipientId, $message, $conversationId);
        $stmt->execute();
    }
    
    protected function updateConversation($conversationId, $message) {
        $stmt = $this->conn->prepare("
            UPDATE conversations 
            SET last_message = ?, last_message_time = NOW()
            WHERE conversation_id = ?
        ");
        $stmt->bind_param("si", $message, $conversationId);
        $stmt->execute();
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        if (isset($this->users[$conn->resourceId])) {
            unset($this->users[$conn->resourceId]);
        }
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    8080
);

$server->run();
?>