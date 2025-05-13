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
            request_id,
            product_name,
            client_name,
              phone_number,
            quantity,
            status,
            request_date,
             pharmacy_notes
        FROM request_meds
        WHERE pharmacy_id = ? AND status != 'cancelled'
        ORDER BY request_date DESC
    ");

    $stmt->bind_param("i", $pharmacyId);
    $stmt->execute();
    $result = $stmt->get_result();

    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode(['requests' => $requests]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
