CREATE TABLE order_meds (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    client_name VARCHAR(100) NOT NULL,
    pharmacy_id INT NOT NULL,
    pharmacy_name VARCHAR(100) NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL COMMENT 'price of order',
    order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM(
        'pending', 
        'processing', 
        'approved',
        'completed', 
        'cancelled',
        'expired'
    ) DEFAULT 'pending',
    due_date DATETIME COMMENT 'When the order should be ready',
  
    payment_method ENUM('cash', 'mobile_payment') DEFAULT 'cash',
    payment_status ENUM('pending', 'paid') DEFAULT 'pending',
    
    delivery_address TEXT,
    
    approved_at DATETIME,
    cancelled_at DATETIME,
    completed_at DATETIME,
    
    client_notes TEXT,
    pharmacy_notes TEXT,
 
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE RESTRICT,
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacy(pharmacy_id) ON DELETE RESTRICT,
    
    INDEX idx_client (client_id),
    INDEX idx_pharmacy (pharmacy_id),
    INDEX idx_status (status),
    INDEX idx_order_date (order_date),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
