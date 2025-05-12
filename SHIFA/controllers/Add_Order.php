<?php
include 'connection.php';
require_once 'session.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("Add_Orders.php: Unauthorized access - no user_id in session");
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Add_Orders.php: POST request received");
    $data = json_decode(file_get_contents('php://input'), true);
    error_log("Add_Orders.php: Received data: " . print_r($data, true));
    
    // Validate and sanitize inputs
    $pharmacyId = filter_var($data['pharmacy_id'], FILTER_SANITIZE_NUMBER_INT);
    $pharmacyName = filter_var($data['pharmacy_name'], FILTER_SANITIZE_STRING);
    $clientId = $_SESSION['user_id'];
    $clientName = filter_var($data['client_name'], FILTER_SANITIZE_STRING);
    $phone = filter_var($data['phone'], FILTER_SANITIZE_STRING);
    $brandName = filter_var($data['product_name'], FILTER_SANITIZE_STRING);
    $quantity = filter_var($data['quantity'], FILTER_SANITIZE_NUMBER_INT);
    $total = filter_var($data['total_price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $delivery_address = filter_var($data['delivery_address'], FILTER_SANITIZE_STRING);
    $payment_method = filter_var($data['payment_method'], FILTER_SANITIZE_STRING);
    $client_note = filter_var($data['client_note'], FILTER_SANITIZE_STRING);

    try {
        $conn = getDatabaseConnection();
        
        // Prepare statement using mysqli
        $stmt = $conn->prepare("INSERT INTO order_meds 
            (client_id, client_name, pharmacy_id, pharmacy_name, product_name, quantity, price, order_date, payment_method, delivery_address, client_notes, phone_number) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        // Bind parameters
        $stmt->bind_param("isissidssss", 
            $clientId,       // i - integer
            $clientName,     // s - string
            $pharmacyId,     // i - integer
            $pharmacyName,   // s - string
            $brandName,      // s - string
            $quantity,       // i - integer
            $total,         // d - double (price)
            $payment_method, // s - string
            $delivery_address, // s - string
            $client_note,    // s - string
            $phone           // s - string (phone numbers may contain +, so string)
        );
        
        if ($stmt->execute()) {
            error_log("Add_Orders.php: Order inserted successfully");
            echo json_encode(['success' => true, 'message' => 'Order successful']);
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
    } catch(Exception $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Order failed: ' . $e->getMessage()
        ]);
    }
} else {
    error_log("Add_Orders.php: Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
