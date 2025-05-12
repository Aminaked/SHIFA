CREATE TABLE request_meds (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    client_name VARCHAR(100) NOT NULL,
    pharmacy_id INT NOT NULL,
    pharmacy_name VARCHAR(100) NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    request_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'cancelled', 'fulfilled') DEFAULT 'pending',
    client_notes TEXT,
    pharmacy_notes TEXT,
     phone_number VARCHAR (20),
    INDEX idx_client (client_id),
    INDEX idx_pharmacy (pharmacy_id),
    INDEX idx_status (status),
    INDEX idx_request_date (request_date),
 
   
    CONSTRAINT fk_request_client
        FOREIGN KEY (client_id)
        REFERENCES clients(client_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
        
    CONSTRAINT fk_request_pharmacy
        FOREIGN KEY (pharmacy_id)
        REFERENCES pharmacy(pharmacy_id)  
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;