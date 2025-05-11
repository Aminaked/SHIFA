<?php
include 'connection.php';
require_once 'session.php';

// Check if pharmacy user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    $conn = getDatabaseConnection();
    $pharmacyId = $_SESSION['user_id'];

    $stmt = $conn->prepare("
        SELECT 
            order_id,
            product_name,
            client_name,
            quantity,
            status,
            pharmacy_notes,
            client_notes,
            order_date,
            due_date,
            phone_number,
            payment_method
        FROM order_meds
        WHERE pharmacy_id = ?
        ORDER BY order_date DESC
    ");

    $stmt->bind_param("i", $pharmacyId);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode(['orders' => $orders]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
