<?php
include 'connection.php';
require_once 'session.php';
header('Content-Type: application/json');

// Check if user is logged in
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
if (empty($data['order_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing order_id']);
    exit;
}

$orderId = filter_var($data['order_id'], FILTER_SANITIZE_NUMBER_INT);
$userId = $_SESSION['user_id'];

try {
    $conn = getDatabaseConnection();

    // Verify the order belongs to the logged-in user and is cancellable
    $stmt = $conn->prepare("SELECT status FROM order_meds WHERE order_id = ? AND client_id = ?");
    $stmt->bind_param("ii", $orderId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    if (in_array(strtolower($order['status']), ['cancelled', 'completed'])) {
        echo json_encode(['success' => false, 'message' => 'Order cannot be cancelled']);
        exit;
    }

    // Update order status to cancelled and set cancelled_at timestamp
    $updateStmt = $conn->prepare("UPDATE order_meds SET status = 'cancelled', cancelled_at = NOW() WHERE order_id = ? AND client_id = ?");
    $updateStmt->bind_param("ii", $orderId, $userId);

    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to cancel order']);
    }

    $updateStmt->close();
    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
