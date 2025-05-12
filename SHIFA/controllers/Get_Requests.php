<?php
header('Content-Type: application/json');
include 'connection.php';
require_once 'session.php';

// Function to send JSON response and set HTTP status code
function send_response($data, $http_status_code) {
    http_response_code($http_status_code);
    echo json_encode($data);
    exit;
}

// Check if client is logged in
if (!isset($_SESSION['user_id'])) {
    send_response(['error' => 'Unauthorized'], 401);
}

$client_id = $_SESSION['user_id'];

try {
     $conn = getDatabaseConnection();
    // Prepare and execute query to get requests for this client
    $stmt = $conn->prepare("SELECT request_id, product_name, pharmacy_name, quantity, pharmacy_notes, request_date, status FROM request_meds WHERE client_id = ?");
    if (!$stmt) {
        send_response(['error' => 'Database prepare statement failed'], 500);
    }
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }

    if (count($requests) === 0) {
        send_response(['message' => 'No requests found'], 404);
    } else {
        send_response($requests, 200);
    }
} catch (Exception $e) {
    send_response(['error' => 'Error: ' . $e->getMessage()], 500);
}
?>
