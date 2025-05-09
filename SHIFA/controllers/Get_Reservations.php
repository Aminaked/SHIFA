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
            reservation_id,
            product_name,
            pharmacy_name,
            quantity,
            DATE_FORMAT(reservation_date, '%Y-%m-%d %H:%i') AS formatted_reservation_date,
            status,
            DATE_FORMAT(due_date, '%Y-%m-%d %H:%i') AS formatted_due_date,
            pharmacy_notes
        FROM reserve_meds
        WHERE client_id = ?
        ORDER BY reservation_date DESC
    ");
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reservations = [];
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode(['reservations' => $reservations]);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>