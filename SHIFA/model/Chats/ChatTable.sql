CREATE TABLE chats (
    chat_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    sender_type ENUM('client', 'pharmacy') NOT NULL,
    receiver_id INT NOT NULL,
    receiver_type ENUM('client', 'pharmacy') NOT NULL,
    message TEXT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE,
    conversation_id INT NOT NULL,
    FOREIGN KEY (conversation_id) 
        REFERENCES conversations(conversation_id) 
        ON DELETE CASCADE
);