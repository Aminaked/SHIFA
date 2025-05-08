<?php
session_start();
include '../controllers/connection.php';

if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized access.";
    exit;
}

$pharmacy_id = $_SESSION['user_id'];

if (!isset($_FILES['photo'])) {
    echo "No file uploaded.";
    exit;
}

$photo = $_FILES['photo'];
$targetDir = "../uploads/";
$filename = uniqid() . "_" . basename($photo["name"]);
$targetFile = $targetDir . $filename;

if (move_uploaded_file($photo["tmp_name"], $targetFile)) {
    // Update database
    $sql = "UPDATE pharmacy SET profile_photo = ? WHERE pharmacy_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $filename, $pharmacy_id);

    if ($stmt->execute()) {
        echo "Photo updated successfully.";
    } else {
        echo "Failed to update photo in database.";
    }

    $stmt->close();
} else {
    echo "Failed to upload file.";
}

$conn->close();
?>