<?php
include 'connection.php';
require_once 'session.php';

$client_id = $_SESSION['user_id'];

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['photo']['tmp_name'];
    $fileName = $client_id . "_" . basename($_FILES['photo']['name']); 
    $uploadDir = '../uploads/';
    $uploadPath = $uploadDir . $fileName;

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        echo "Invalid file type.";
        exit;
    }
    if (move_uploaded_file($fileTmp, $uploadPath)) {
        
        $stmt = $conn->prepare("UPDATE clients SET profile_photo = ? WHERE client_id = ?");
        $stmt->bind_param("si", $fileName, $client_id);
        $stmt->execute();
        $stmt->close();
    
        echo "Photo updated successfully.";
    } else {
        echo "Failed to upload photo.";
    }
}
?>