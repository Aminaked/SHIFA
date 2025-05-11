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
            reservation_id,
            product_name,
            client_name,
              phone_number,
            quantity,
            status,
            reservation_date,
            due_date,
             pharmacy_notes,
             client_notes
        FROM reserve_meds
        WHERE pharmacy_id = ?
        ORDER BY reservation_date DESC
    ");

    $stmt->bind_param("i", $pharmacyId);
    $stmt->execute();
    $result = $stmt->get_result();

    $reservations = [];
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode(['reservations' => $reservations]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
