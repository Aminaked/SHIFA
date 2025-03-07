<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medicineName = htmlspecialchars(trim($_POST['medicine']));
    $userLat = isset($_POST['user_lat']) ? $_POST['user_lat'] : null;
    $userLon = isset($_POST['user_lon']) ? $_POST['user_lon'] : null;

    // Basic validation
    if (empty($medicineName)) {
        echo "Please enter a medicine name.";
        exit;
    }
    
    if ($userLat === null || $userLon === null) {
        echo "Location information is missing.";
        exit;
    }

   
    
    echo "Medicine: " . $medicineName . "<br>";
    echo "User Latitude: " . $userLat . "<br>";
    echo "User Longitude: " . $userLon . "<br>";

    // Continue with fetching medicine data, applying fuzzy matching, 
    // and using the Haversine formula to sort by proximity.
}
?>
