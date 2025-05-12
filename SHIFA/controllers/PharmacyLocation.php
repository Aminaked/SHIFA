<?php
require 'connection.php'; 
require_once 'session.php';
if (isset($_GET['user_id'])) {
    $pharmacy_id = $_GET['user_id'];
     $conn = getDatabaseConnection();
    $sql = "SELECT pharmacy_name, latitude, longitude FROM pharmacy WHERE pharmacy_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pharmacy_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $pharmacy_name = $row['pharmacy_name'];
        $latitude = $row['latitude'];
        $longitude = $row['longitude'];
    } else {
        echo "Pharmacy not found.";
        exit;
    }
} else {
    echo "Invalid request.";
    exit;
}
?>
