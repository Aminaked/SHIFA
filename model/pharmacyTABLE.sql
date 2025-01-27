CREATE TABLE pharmacy (
    pharmacy_id INT PRIMARY KEY AUTO_INCREMENT,
    pharmacy_name VARCHAR (100),
    pharmacy_liscense_number VARCHAR(20)  UNIQUE,
    phone_number VARCHAR (20) ,
    email VARCHAR (100)  UNIQUE,
    password VARCHAR (255),
    address 

)
