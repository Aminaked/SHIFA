<?php
include 'connection.php';
require_once 'session.php';
header('Content-Type: application/json');

// Check if pharmacy user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (empty($data['order_id']) || empty($data['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing order_id or status']);
    exit;
}

$orderId = filter_var($data['order_id'], FILTER_SANITIZE_NUMBER_INT);
$status = filter_var($data['status'], FILTER_SANITIZE_STRING);
$pharmacyNote = isset($data['pharmacy_note']) ? filter_var($data['pharmacy_note'], FILTER_SANITIZE_STRING) : null;
$pharmacyId = $_SESSION['user_id'];

try {
    $conn = getDatabaseConnection();

    // Verify the order belongs to the logged-in pharmacy
    $stmt = $conn->prepare("SELECT status FROM order_meds WHERE order_id = ? AND pharmacy_id = ?");
    $stmt->bind_param("ii", $orderId, $pharmacyId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    if (strtolower($order['status']) === 'cancelled') {
        echo json_encode(['success' => false, 'message' => 'Order already cancelled']);
        exit;
    }

    // Update order status and pharmacy note
    $updateStmt = $conn->prepare("UPDATE order_meds SET status = ?, pharmacy_notes = ? WHERE order_id = ? AND pharmacy_id = ?");
    $updateStmt->bind_param("ssii", $status, $pharmacyNote, $orderId, $pharmacyId);

    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Order updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update order']);
    }

    $updateStmt->close();
    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
