CREATE TABLE chats (
    chat_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_role ENUM('Users', 'pharmacy') NOT NULL,
    sender_id INT NOT NULL,                         
    receiver_id INT NOT NULL,                        
    message TEXT NOT NULL,                           
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,    
    is_read BOOLEAN DEFAULT FALSE,                   
    FOREIGN KEY (sender_id) 
        REFERENCES Users(client_id) 
        ON DELETE CASCADE,                           
    FOREIGN KEY (receiver_id) 
        REFERENCES pharmacy(pharmacy_id) 
        ON DELETE CASCADE                           
);
