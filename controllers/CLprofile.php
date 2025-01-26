<?php

include 'connection.php';

include 'session.php';
$client_id = $_SESSION['client_id'];




$sql = "SELECT full_name, email, phone_number FROM Users WHERE client_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    


    while ($row = $result->fetch_assoc()) {
        echo "<h1>Profile</h1>";
        echo "<p><strong>Full Name:</strong> " . $row['full_name'] . "</p>";
        echo "<p><strong>Email:</strong> " . $row['email'] . "</p>";
        echo "<p><strong>Phone Number:</strong> " . $row['phone_number'] . "</p>";
        echo "<p><strong>Address:</strong> " . $row['address'] . "</p>";
        echo "<p><strong>Date of Birth:</strong> " . $row['date_of_birth'] . "</p>";
        echo "<p><strong>Gender:</strong> " . $row['gender'] . "</p>";
    }
} else {
    echo "No user data found.";
}

$stmt->close();
$conn->close();
?>
