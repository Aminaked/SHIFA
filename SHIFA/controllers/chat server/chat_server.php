<?php
require __DIR__ . '/vendor/autoload.php';
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
        
        // Initialize database connection
        global $conn; // Assuming connection.php creates this
        if (!$conn || !$conn->ping()) {
            throw new RuntimeException("Database connection failed");
        }
        $this->conn = $conn;
        
        echo "Chat server initialized\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        try {
            $data = json_decode($msg, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException("Invalid JSON received");
            }
            
            if (!isset($data['type'])) {
                throw new InvalidArgumentException("Missing message type");
            }

            switch ($data['type']) {
                case 'register':
                    $this->handleRegistration($from, $data);
                    break;
                    
                case 'message':
                    $this->handleMessage($from, $data);
                    break;
                    
                default:
                    throw new InvalidArgumentException("Unknown message type");
            }
        } catch (Exception $e) {
            error_log("Error processing message: " . $e->getMessage());
            $from->send(json_encode([
                'error' => $e->getMessage(),
                'original' => $msg
            ]));
        }
    }

    protected function handleRegistration(ConnectionInterface $conn, array $data) {
        if (!isset($data['id']) || !isset($data['user_type'])) {
            throw new InvalidArgumentException("Missing registration data");
        }
        
        $this->users[$conn->resourceId] = [
            'id' => $data['id'],
            'type' => $data['user_type'],
            'conn' => $conn
        ];
        
        echo "User registered: {$data['id']} ({$data['user_type']})\n";
    }

    protected function handleMessage(ConnectionInterface $from, array $data) {
        error_log("Received message data: " . print_r($data, true));
        
        if (!isset($this->users[$from->resourceId])) {
            $error = "User not registered (Resource ID: {$from->resourceId})";
            error_log($error);
            throw new RuntimeException($error);
        }
        
        error_log("Sender info: " . print_r($this->users[$from->resourceId], true));

        $required = ['to_id', 'to_type', 'message'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new InvalidArgumentException("Missing $field in message");
            }
        }

        $sender = $this->users[$from->resourceId];
        error_log("Processing message from {$sender['type']} ID {$sender['id']} to {$data['to_type']} ID {$data['to_id']}");
        $recipientId = $data['to_id'];
        $recipientType = $data['to_type'];
        $message = trim($data['message']);

        if (empty($message)) {
            throw new InvalidArgumentException("Message cannot be empty");
        }

        $conversationId = $this->getConversationId(
            $sender['id'], 
            $sender['type'], 
            $recipientId, 
            $recipientType
        );

        $this->saveMessageToDatabase(
            $sender['id'], 
            $sender['type'], 
            $recipientId,
            $recipientType,
            $message, 
            $conversationId
        );

        $this->updateConversation($conversationId, $message);

        $messageData = [
            'type' => 'message',
            'from_id' => $sender['id'],
            'from_type' => $sender['type'],
            'message' => $message,
            'timestamp' => time(),
            'conversation_id' => $conversationId
        ];

        $this->broadcastMessage($messageData, $recipientId, $recipientType, $from);
    }

    protected function broadcastMessage(array $message, $recipientId, $recipientType, ConnectionInterface $sender) {
        error_log("Broadcasting message to {$recipientType} ID {$recipientId}");
        $jsonMessage = json_encode($message);
        error_log("Message content: " . $jsonMessage);
        
        // Log all connected users
        error_log("Connected users: " . print_r(array_map(function($u) {
            return ['id'=>$u['id'], 'type'=>$u['type']];
        }, $this->users), true));
        
        // Send to recipient if online
        foreach ($this->users as $user) {
            if ($user['id'] == $recipientId && $user['type'] == $recipientType) {
                $user['conn']->send($jsonMessage);
                break;
            }
        }
        
        // Send back to sender
        $sender->send($jsonMessage);
    }

    protected function getConversationId($senderId, $senderType, $recipientId, $recipientType) {
        $clientId = $senderType == 'client' ? $senderId : $recipientId;
        $pharmacyId = $senderType == 'pharmacy' ? $senderId : $recipientId;
        
        $stmt = $this->conn->prepare("
            SELECT conversation_id FROM conversations 
            WHERE client_id = ? AND pharmacy_id = ?
        ");
        
        if (!$stmt) {
            throw new RuntimeException("Prepare failed: " . $this->conn->error);
        }
        
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
        
        if (!$stmt) {
            throw new RuntimeException("Prepare failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("ii", $clientId, $pharmacyId);
        $stmt->execute();
        return $this->conn->insert_id;
    }
    
    protected function saveMessageToDatabase($senderId, $senderType, $recipientId, $recipientType, $message, $conversationId) {
        $stmt = $this->conn->prepare("
            INSERT INTO chats 
            (sender_id, sender_type, receiver_id, receiver_type, message, conversation_id, is_read)
            VALUES (?, ?, ?, ?, ?, ?, 0)
        ");
        
        $stmt->bind_param("isissi", 
            $senderId, 
            $senderType,
            $recipientId,
            $recipientType,
            $message, 
            $conversationId
        );
        
        $stmt->execute();
        return $this->conn->insert_id;
    }
    
    protected function updateConversation($conversationId, $message) {
        $stmt = $this->conn->prepare("
            UPDATE conversations 
            SET last_message = ?, last_message_time = NOW()
            WHERE conversation_id = ?
        ");
        
        if (!$stmt) {
            throw new RuntimeException("Prepare failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("si", $message, $conversationId);
        if (!$stmt->execute()) {
            throw new RuntimeException("Execute failed: " . $stmt->error);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        if (isset($this->users[$conn->resourceId])) {
            $user = $this->users[$conn->resourceId];
            echo "User disconnected: {$user['id']} ({$user['type']})\n";
            unset($this->users[$conn->resourceId]);
        }
        
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Start the server
try {
    echo "Starting chat server...\n";
    
    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new Chat()
            )
        ),
        8083,
        '0.0.0.0' // Listen on all interfaces
    );
    
    echo "Server running on ws://0.0.0.0:8083\n";
    $server->run();
    
} catch (Exception $e) {
    echo "Failed to start server: " . $e->getMessage() . "\n";
    exit(1);
}