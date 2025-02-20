CREATE TABLE clients (
    client_id INT PRIMARY KEY AUTO_INCREMENT,
    Full_name VARCHAR (100),
    phone_number VARCHAR (20),
    email VARCHAR (100) UNIQUE,
    password  VARCHAR (255))
