<?php
include 'connection.php';
require_once 'session.php';
header('Content-Type: application/json');

// Check if client user is logged in
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
if (empty($data['reservation_id']) || empty($data['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing reservation_id or status']);
    exit;
}

$reservationId = filter_var($data['reservation_id'], FILTER_SANITIZE_NUMBER_INT);
$status = filter_var($data['status'], FILTER_SANITIZE_SPECIAL_CHARS);
$clientId = $_SESSION['user_id'];

try {
    $conn = getDatabaseConnection();

    // Verify the reservation belongs to the logged-in client
    $stmt = $conn->prepare("SELECT status FROM reserve_meds WHERE reservation_id = ? AND client_id = ?");
    $stmt->bind_param("ii", $reservationId, $clientId);
    $stmt->execute();
    $result = $stmt->get_result();
    $reservation = $result->fetch_assoc();

    if (!$reservation) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Reservation not found']);
        exit;
    }

    if (strtolower($reservation['status']) === 'cancelled') {
        echo json_encode(['success' => false, 'message' => 'Reservation already cancelled']);
        exit;
    }

    // Update reservation status
    $updateStmt = $conn->prepare("UPDATE reserve_meds SET status = ? WHERE reservation_id = ? AND client_id = ?");
    $updateStmt->bind_param("sii", $status, $reservationId, $clientId);

    if ($updateStmt->execute()) {
        $affectedRows = $updateStmt->affected_rows;
        if ($affectedRows > 0) {
            echo json_encode(['success' => true, 'message' => 'Reservation updated successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No rows updated. Check reservation_id and client_id match.']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update reservation']);
    }

    $updateStmt->close();
    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
