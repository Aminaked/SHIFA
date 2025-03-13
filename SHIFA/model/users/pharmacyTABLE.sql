CREATE TABLE pharmacy (
    pharmacy_id INT PRIMARY KEY AUTO_INCREMENT,
    pharmacy_name VARCHAR(100) NOT NULL,
    pharmacy_liscense_number VARCHAR(20) UNIQUE NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    address VARCHAR(255) NOT NULL,         
    longitude DECIMAL(9,6),             
    latitude DECIMAL(8,6),               
    status ENUM('pending', 'active') DEFAULT 'pending'
);