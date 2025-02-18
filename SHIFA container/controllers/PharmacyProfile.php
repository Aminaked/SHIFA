<?php

include 'connection.php';

// Check if the session contains the pharmacy_id
if (!isset($_SESSION['pharmacy_id'])) {
    // Redirect to login or an error page if pharmacy_id is not found in session
    header("Location: login.php"); // Or wherever appropriate
    exit;
}


$pharmacy_id = $_SESSION['pharmacy_id'];


$sql = "SELECT * FROM pharmacy WHERE pharmacy_id = ?";
$stmt = $conn->prepare($sql);

// Bind the pharmacy_id to the prepared statement
$stmt->bind_param("i", $pharmacy_id); // "i" means the parameter is an integer


$stmt->execute();


$result = $stmt->get_result();

// Check if the pharmacy exists
if ($result->num_rows > 0) {
    // Fetch the data
    $pharmacy = $result->fetch_assoc();

    // Display the pharmacy details dynamically
    echo "<p><strong>Name:</strong> " . htmlspecialchars($pharmacy['pharmacy_name']) . "</p>";
    echo "<p><strong>License Number:</strong> " . htmlspecialchars($pharmacy['pharmacy_liscense_number']) . "</p>";
    echo "<p><strong>Phone:</strong> " . htmlspecialchars($pharmacy['phone_number']) . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($pharmacy['email']) . "</p>";
} else {
    echo "Pharmacy not found!";
}

// Close the prepared statement
$stmt->close();

// Close the database connection
$conn->close();
?>
