<?php 
include 'connection.php';
include 'ClearSession.php';
include 'GeoCode.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $pharmacy_name = $_POST['pharmacy_name'];
    $pharmacy_liscense = $_POST['pharmacy_liscense'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $address = $_POST['address'];

   
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

   
    $coordinates = geocodeAddress($address);

    if ($coordinates) {
        $latitude = $coordinates['latitude'];
        $longitude = $coordinates['longitude'];
        
       
        $sql = "INSERT INTO pharmacy (pharmacy_name, pharmacy_liscense_number, phone_number, address, email, password, longitude, latitude) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        // Prepare the statement
        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters
            $stmt->bind_param("ssssssss", $pharmacy_name, $pharmacy_liscense, $phone, $address, $email, $hashed_password, $longitude, $latitude);

            // Execute the statement
            if ($stmt->execute()) {
                echo "Pharmacy added successfully";
                // Redirect to homepage
                header("Location: ../views/PH_Pending_Statue.php");
                exit(); 
            } else {
                echo "Error: " . $stmt->error;
            }
        } else {
            echo "Error: Could not prepare the SQL statement.";
        }
    } else {
        echo "Error: Uncorrect address";
    }
}

$conn->close();
?>
