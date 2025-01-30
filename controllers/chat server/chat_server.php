<?php


require 'vendor/autoload.php';
require 'connection.php'; // Include connection to database

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;


class ChatServer implements MessageComponentInterface {
    protected $clients;
    protected $db;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        global $db; // Use the $db object from connection.php
        $this->db = $db;
    }

    public function onOpen(ConnectionInterface $conn) {
        echo "New connection: {$conn->resourceId}\n";
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if (isset($data['message'], $data['pharmacy_id'], $data['client_id'])) {
            $message = $data['message'];
            $pharmacy_id = $data['pharmacy_id'];
            $client_id = $data['client_id'];

            // Check if conversation exists
            $conversation = $this->db->query("SELECT * FROM conversations WHERE pharmacy_id = ? AND client_id = ?", [$pharmacy_id, $client_id]);
            if ($conversation->numRows === 0) {
                // Create a new conversation if none exists
                $this->db->query("INSERT INTO conversations (client_id, pharmacy_id) VALUES (?, ?)", [$client_id, $pharmacy_id]);
                $conversation_id = $this->db->getInsertId();
            } else {
                $conversation_id = $conversation->fetchAssoc()['conversation_id'];
            }

            // Save the message
            $this->db->query("INSERT INTO chats (sender_role, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)", [
                'Users', $client_id, $pharmacy_id, $message
            ]);

            // Broadcast message to clients
            foreach ($this->clients as $client) {
                if ($client !== $from) {
                    $client->send(json_encode([
                        'sender' => 'client', // Update based on sender
                        'message' => $message
                    ]));
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        echo "Connection closed: {$conn->resourceId}\n";
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Start the WebSocket server
$server = IoServer::factory(
    new WsServer(
        new ChatServer()
    ),
    8080
);
$server->run();
