<?php
header("Content-Type: application/json");

$expected_api_key = getenv('API_KEY');
$provided_api_key = isset($_GET['api_key']) ? $_GET['api_key'] : '';

if ($provided_api_key !== $expected_api_key) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$servername = "taleb-pharmacy-db";
$username = "root";
$password = "taleb";
$dbname = "taleb_pharmacy_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

$sql = "SELECT * FROM Medication_Stock";
$result = $conn->query($sql);

$medications = [];

while ($row = $result->fetch_assoc()) {
    $medications[] = $row;
}

echo json_encode($medications);
$conn->close();
?>
