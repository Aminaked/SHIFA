<?php

include 'connection.php';
include 'ClearSession.php';
include 'GeoCode.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" ) {
    $pharmacy_name = $_POST['pharmacy_name'];
    $pharmacy_liscense= $_POST['pharmacy_liscense'];
    $phone= $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $address = $_POST['address'];
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $coordinates = geocodeAddress($address);

    if ($coordinates) {
        
        $latitude = $coordinates['latitude'];
        $longitude = $coordinates['longitude'];}
    else{
        echo "Error: Could not geocode address.";
    }
   
    $sql = "INSERT INTO pharmacy (pharmacy_name,pharmacy_liscense_number, phone_number,address, email, password, longitude, latitude) VALUES ('$pharmacy_name','$pharmacy_liscense','$phone','$address','$email','$hashed_password','$longitude','$latitude')";

   
    if ($conn->query($sql) === TRUE) {
        echo "Pharmacy  added successfully";
        header("location: ../views/PHhomepage.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
    $conn->close();

?>