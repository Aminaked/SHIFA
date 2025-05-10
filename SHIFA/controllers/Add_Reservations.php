<?php
include 'connection.php';
require_once 'session.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("Add_Reservations.php: Unauthorized access - no user_id in session");
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Add_Reservations.php: POST request received");
    $data = json_decode(file_get_contents('php://input'), true);
    error_log("Add_Reservations.php: Received data: " . print_r($data, true));
    
    // Validate and sanitize inputs
    $pharmacyId = filter_var($data['pharmacy_id'], FILTER_SANITIZE_NUMBER_INT);
    $pharmacyName = filter_var($data['pharmacy_name'], FILTER_SANITIZE_STRING);
    $brandName = filter_var($data['brand_name'], FILTER_SANITIZE_STRING);
    $clientId = $_SESSION['user_id'];
    $clientName = $_SESSION['user_name'] ?? 'Unknown';

    try {
        $conn = getDatabaseConnection();
        
        // Prepare statement using mysqli
        $stmt = $conn->prepare("INSERT INTO reserve_meds 
                            (client_id, client_name, pharmacy_id, pharmacy_name, product_name, reservation_date)
                            VALUES (?, ?, ?, ?, ?, NOW())");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        // Bind parameters
        $stmt->bind_param("isiss", 
            $clientId,
            $clientName,
            $pharmacyId,
            $pharmacyName,
            $brandName
        );
        
        if ($stmt->execute()) {
            error_log("Add_Reservations.php: Reservation inserted successfully");
            echo json_encode(['success' => true, 'message' => 'Reservation successful']);
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
    } catch(Exception $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Reservation failed: ' . $e->getMessage()
        ]);
    }
} else {
    error_log("Add_Reservations.php: Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>