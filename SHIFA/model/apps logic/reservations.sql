CREATE TABLE reserve_meds (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    client_name VARCHAR(100) NOT NULL,
    pharmacy_id INT NOT NULL,
    pharmacy_name VARCHAR(100) NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    reservation_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'cancelled', 'expired') DEFAULT 'pending',
    due_date DATETIME COMMENT 'Set by pharmacy when approving',
    pharmacy_notes TEXT,
    
    INDEX idx_client (client_id),
    INDEX idx_pharmacy (pharmacy_id),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date),
    INDEX idx_reservation_date (reservation_date),
 
   
    CONSTRAINT fk_reserve_client
        FOREIGN KEY (client_id)
        REFERENCES clients(client_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
        
    CONSTRAINT fk_reserve_pharmacy
        FOREIGN KEY (pharmacy_id)
        REFERENCES pharmacy(pharmacy_id)  
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;