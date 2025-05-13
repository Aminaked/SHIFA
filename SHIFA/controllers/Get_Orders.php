<?php
include 'connection.php';
require_once 'session.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized access']));
}

try {
    $conn = getDatabaseConnection();
    $userId = $_SESSION['user_id'];

    // Get reservations with formatted date
    $stmt = $conn->prepare("
        SELECT 
            order_id,
            product_name,
            pharmacy_name,
            quantity,
            price,
            DATE_FORMAT(order_date, '%Y-%m-%d %H:%i') AS formatted_order_date,
            status,
            DATE_FORMAT(due_date, '%Y-%m-%d %H:%i') AS formatted_due_date,
            pharmacy_notes
        FROM order_meds
        WHERE client_id = ? AND status != 'cancelled'
        ORDER BY order_date DESC
    ");
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode(['orders' => $orders]);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>