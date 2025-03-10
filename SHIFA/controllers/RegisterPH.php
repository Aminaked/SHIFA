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

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Geocode the address to get coordinates
    $coordinates = geocodeAddress($address);

    if ($coordinates) {
        $latitude = $coordinates['latitude'];
        $longitude = $coordinates['longitude'];
        
        // Set status to 'pending' by default
        $status = 'pending';

        $sql = "INSERT INTO pharmacy (pharmacy_name, pharmacy_liscense_number, phone_number, address, email, password, longitude, latitude, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        // Prepare the statement
        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters
            $stmt->bind_param("sssssssss", $pharmacy_name, $pharmacy_liscense, $phone, $address, $email, $hashed_password, $longitude, $latitude, $status);

            // Execute the statement
            if ($stmt->execute()) {
                echo "Pharmacy added successfully";
                // Redirect to pending status page
                header("Location: ../views/PH_Pending_Statue.php");
                exit(); 
            } else {
                echo "Error: " . $stmt->error;
            }
        } else {
            echo "Error: Could not prepare the SQL statement.";
        }
    } else {
        echo "Error: Incorrect address";
    }
}

$conn->close();
?>
