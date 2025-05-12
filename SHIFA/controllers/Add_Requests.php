<?php
header('Content-Type: application/json');
include 'connection.php';
require_once 'session.php';

// --------------------------
// Authentication Check
// --------------------------
if (!isset($_SESSION['user_id'])) {
    error_log("RequestMed.php: Unauthorized access attempt");
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// --------------------------
// Input Handling & Validation
// --------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("RequestMed.php: POST request started");

    // Sanitize inputs using filter_var (original fields preserved)
    $client_name = filter_var($_POST['client_name'] ?? '', FILTER_SANITIZE_STRING);
    $phone = filter_var($_POST['phone_number'] ?? '', FILTER_SANITIZE_STRING);
    $medication_name = filter_var($_POST['medication_name'] ?? '', FILTER_SANITIZE_STRING);
    $pharmacy_id = filter_var($_POST['pharmacy_id'] ?? '', FILTER_SANITIZE_NUMBER_INT);
    $pharmacy_name = filter_var($_POST['pharmacy_name'] ?? '', FILTER_SANITIZE_STRING);
    $quantity = filter_var($_POST['quantity'] ?? '', FILTER_SANITIZE_NUMBER_INT);
    $client_notes = filter_var($_POST['client_notes'] ?? '', FILTER_SANITIZE_STRING);

    // Convert to integers where needed
    $pharmacy_id = (int) $pharmacy_id;
    $quantity = (int) $quantity;
    $client_id = (int) $_SESSION['user_id'];

    // --------------------------
    // Validate Required Fields
    // --------------------------
    $errors = [];
    if (empty($client_name))
        $errors[] = "Client name required";
    if (empty($phone))
        $errors[] = "Phone number required";
    if (empty($medication_name))
        $errors[] = "Medication name required";
    if ($pharmacy_id <= 0)
        $errors[] = "Invalid pharmacy ID";
    if (empty($pharmacy_name))
        $errors[] = "Pharmacy name required";
    if ($quantity <= 0)
        $errors[] = "Quantity must be > 0";

    if (!empty($errors)) {
        error_log("RequestMed.php: Validation errors - " . implode(", ", $errors));
        echo json_encode(['success' => false, 'message' => implode(", ", $errors)]);
        exit;
    }

    // --------------------------
    // Database Operation
    // --------------------------
    try {
        $conn = getDatabaseConnection();

        // Keep original table/column names
        $stmt = $conn->prepare("INSERT INTO request_meds 
            (client_id, client_name, pharmacy_id, pharmacy_name,product_name, quantity, request_date,client_notes,phone_number)
            VALUES (?, ?, ?, ?, ?, ?,  NOW(), ?,?)");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // Bind parameters with original field order
        $stmt->bind_param(
            "isississ",
            $client_id,      // i
            $client_name,
         
           // s
          $pharmacy_id,    // i
            $pharmacy_name,
             $medication_name,  // s         
            $quantity,       // i
            $client_notes,
               $phone
        );

        if ($stmt->execute()) {
            error_log("RequestMed.php: Request inserted for client $client_id");
            echo json_encode(['success' => true, 'message' => 'Request submitted']);
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
    } catch (Exception $e) {
        error_log("RequestMed.php: Database error - " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Request failed: ' . $e->getMessage()
        ]);
    }
} else {
    error_log("RequestMed.php: Invalid method - " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'POST requests only']);
}
?>