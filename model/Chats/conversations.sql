CREATE TABLE conversations (
    conversation_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,                         
    pharmacy_id INT NOT NULL,                       
    last_message TEXT,                               
    last_message_time DATETIME DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (client_id) 
        REFERENCES Users(client_id) 
        ON DELETE CASCADE,                          
    FOREIGN KEY (pharmacy_id) 
        REFERENCES pharmacy(pharmacy_id) 
        ON DELETE CASCADE                            
);
