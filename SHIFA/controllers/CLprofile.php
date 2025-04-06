<?php

include 'connection.php';
require_once 'session.php';

$client_id = $_SESSION['client_id'];




$sql = "SELECT full_name, email, phone_number FROM clients WHERE client_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    


    while ($row = $result->fetch_assoc()) {
        
        echo "<p><strong>Full Name:</strong> " . $row['full_name'] . "</p>";
        echo "<p><strong>Email:</strong> " . $row['email'] . "</p>";
        echo "<p><strong>Phone Number:</strong> " . $row['phone_number'] . "</p>";
        
       
    }
} else {
    echo "No user data found.";
}

$stmt->close();
$conn->close();
?>
