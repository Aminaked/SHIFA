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
    protected $userConnections; // Store active users (socket connections)

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->userConnections = []; // Track online users
        global $db; // Use the $db object from connection.php
        $this->db = $db;
    }

    public function onOpen(ConnectionInterface $conn) {
        echo "New connection: {$conn->resourceId}\n";
        $this->clients->attach($conn);

        // Assume the user sends their ID immediately after connecting
        $conn->send(json_encode(["action" => "request_id"]));
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        if (isset($data['action']) && $data['action'] === "register") {
            $user_id = $data['user_id'];
            $user_role = $data['role']; // "client" or "pharmacy"
            $this->userConnections[$user_id] = $from;

            // Mark user as online in DB
            if ($user_role === "client") {
                $this->db->query("UPDATE clients SET online_status = 1 WHERE client_id = ?", [$user_id]);
            } else {
                $this->db->query("UPDATE pharmacies SET online_status = 1 WHERE pharmacy_id = ?", [$user_id]);
            }

            echo "User $user_id registered as online\n";

            // Check for unread messages
            $this->sendUnreadMessages($user_id, $from);
            return;
        }

        if (isset($data['message'], $data['pharmacy_id'], $data['client_id'])) {
            $message = $data['message'];
            $pharmacy_id = $data['pharmacy_id'];
            $client_id = $data['client_id'];
            $sender_id = $data['sender_id']; // The user who sent the message
            $sender_role = $data['sender_role']; // "client" or "pharmacy"
            $receiver_id = ($sender_role === "client") ? $pharmacy_id : $client_id;

            // Check if the recipient is online
            $isRecipientOnline = isset($this->userConnections[$receiver_id]);

            // Save message in DB
            $this->db->query("INSERT INTO chats (sender_id, receiver_id, message, is_read) VALUES (?, ?, ?, ?)", [
                $sender_id, $receiver_id, $message, $isRecipientOnline ? 1 : 0 // Mark as read if online
            ]);

            // If recipient is online, send the message immediately
            if ($isRecipientOnline) {
                $this->userConnections[$receiver_id]->send(json_encode([
                    'sender' => $sender_role,
                    'message' => $message
                ]));
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        echo "Connection closed: {$conn->resourceId}\n";
        $this->clients->detach($conn);

        // Find the disconnected user and mark them offline
        $userId = array_search($conn, $this->userConnections);
        if ($userId !== false) {
            unset($this->userConnections[$userId]);

            // Mark as offline in DB
            $this->db->query("UPDATE clients SET online_status = 0 WHERE client_id = ?", [$userId]);
            $this->db->query("UPDATE pharmacies SET online_status = 0 WHERE pharmacy_id = ?", [$userId]);
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }

    private function sendUnreadMessages($user_id, ConnectionInterface $conn) {
        $messages = $this->db->query("SELECT * FROM chats WHERE receiver_id = ? AND is_read = 0", [$user_id])->fetchAll();

        foreach ($messages as $message) {
            $conn->send(json_encode([
                'sender' => $message['sender_id'],
                'message' => $message['message']
            ]));

            // Mark message as read
            $this->db->query("UPDATE chats SET is_read = 1 WHERE id = ?", [$message['id']]);
        }
    }
}

// Start WebSocket server
$server = IoServer::factory(
    new WsServer(
        new ChatServer()
    ),
    8080
);
$server->run();
