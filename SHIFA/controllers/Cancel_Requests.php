<?php
header('Content-Type: application/json');
include 'connection.php';
require_once 'session.php';

// Function to send JSON response and set HTTP status code
function send_response($success, $message, $http_status_code) {
    http_response_code($http_status_code);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(false, 'Bad request method. POST required.', 400);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['request_id'])) {
    send_response(false, 'Invalid input.', 400);
}

$request_id = $input['request_id'];

// Check if client is logged in
if (!isset($_SESSION['user_id'])) {
    send_response(false, 'Unauthorized.', 401);
}

$client_id = $_SESSION['user_id'];

try {
    $conn = getDatabaseConnection();

    // Verify the request belongs to the client
    $stmt = $conn->prepare("SELECT status FROM request_meds WHERE request_id = ? AND client_id = ?");
    if (!$stmt) {
        send_response(false, 'Database prepare statement failed.', 500);
    }
    $stmt->bind_param("ii", $request_id, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        send_response(false, 'Request not found.', 404);
    }
    $row = $result->fetch_assoc();
    if (strtolower($row['status']) === 'cancelled') {
        send_response(false, 'Request already cancelled.', 400);
    }
    if (strtolower($row['status']) === 'fulfilled') {
        send_response(false, 'Request already fulfilled and cannot be cancelled.', 400);
    }

    // Update request status to cancelled
    $updateStmt = $conn->prepare("UPDATE request_meds SET status = 'cancelled' WHERE request_id = ? AND client_id = ?");
    if (!$updateStmt) {
        send_response(false, 'Database prepare statement failed.', 500);
    }
    $updateStmt->bind_param("ii", $request_id, $client_id);
    if ($updateStmt->execute()) {
        send_response(true, 'Request cancelled successfully.', 200);
    } else {
        send_response(false, 'Failed to cancel request.', 500);
    }
} catch (Exception $e) {
    send_response(false, 'Error: ' . $e->getMessage(), 500);
}
?>
