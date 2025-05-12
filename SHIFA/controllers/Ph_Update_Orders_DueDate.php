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
if (!$input || !isset($input['order_id']) || !isset($input['due_date'])) {
    send_response(false, 'Invalid input.', 400);
}

$order_id = $input['order_id'];
$due_date = $input['due_date'];

// Check if pharmacy is logged in
if (!isset($_SESSION['user_id'])) {
    send_response(false, 'Unauthorized.', 401);
}

$pharmacy_id = $_SESSION['user_id'];

// Validate due_date format (basic check)
if (strtotime($due_date) === false) {
    send_response(false, 'Invalid due date format.', 400);
}

try {
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("SELECT order_id FROM order_meds WHERE order_id = ? AND pharmacy_id = ?");
    if (!$stmt) {
        send_response(false, 'Database prepare statement failed.', 500);
    }
    $stmt->bind_param("ii", $order_id, $pharmacy_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        send_response(false, 'Order not found.', 404);
    }

    // Update due date
    $updateStmt = $conn->prepare("UPDATE orders SET due_date = ? WHERE order_id = ? AND pharmacy_id = ?");
    if (!$updateStmt) {
        send_response(false, 'Database prepare statement failed.', 500);
    }
    $updateStmt->bind_param("sii", $due_date, $order_id, $pharmacy_id);
    if ($updateStmt->execute()) {
        send_response(true, 'Due date updated successfully.', 200);
    } else {
        send_response(false, 'Failed to update due date.', 500);
    }
} catch (Exception $e) {
    send_response(false, 'Error: ' . $e->getMessage(), 500);
}
?>
