<?php
 error_reporting(E_ALL);
 ini_set('display_errors', 1);

 $DOPPLER_TOKEN = $_SERVER['DOPPLER_TOKEN'] ;
 if (!$DOPPLER_TOKEN) {
     die('Doppler token not found.');
 }


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

    $stmt = $pdo->query("SELECT pharmacy_id, pharmacy_name, latitude, longitude FROM pharmacy");
    $pharmacies = $stmt->fetchAll(PDO::FETCH_ASSOC);



    $results = [];
    foreach ($pharmacies as $pharmacy) {
       
        $distance = haversineDistance($userLat, $userLon, $pharmacy['latitude'], $pharmacy['longitude']);
    
        
        list($apiUrl, $apiKey) = fetchApiCredentials($pharmacy['pharmacy_id'],$DOPPLER_TOKEN );
    
        if ($apiUrl && $apiKey) {
            // Make API request to check stock
            $apiData = [
                'medication' => $medicationQuery,
                'api_key' => $apiKey
            ];
    
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($apiData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $apiResponse = curl_exec($ch);
            curl_close($ch);
    
            $stockData = json_decode($apiResponse, true);
    
            // Check if the medication matches (brand name or generic name)
            if ($stockData['in_stock'] && isMedicationMatch($medicationQuery, $stockData['brand_name'], $stockData['generic_name'])) {
                $results[] = [
                    'pharmacy_id' => $pharmacy['pharmacy_id'],
                    'pharmacy_name' => $pharmacy['pharmacy_name'],
                    'address' => $pharmacy['address'],
                    'distance' => round($distance, 2) . ' km',
                    'stock' => $stockData['quantity'],
                    'brand_name' => $stockData['brand_name'],
                    'generic_name' => $stockData['generic_name']
                ];
            }
        }
    }
    
    // Sort results by distance (nearest first)
    usort($results, function ($a, $b) {
        return $a['distance'] <=> $b['distance'];
    });
    
    // Output results
    header('Content-Type: application/json');
    echo json_encode($results);
    
}

  
    
    
    
    function haversineDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // Earth's radius in kilometers
    
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
    
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
    
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
        return $earthRadius * $c; // Distance in kilometers
    }
    
  
    function fetchApiCredentials($pharmacyId, $token) {
        // Replace with your Doppler API endpoint and token
        $dopplerUrl = "https://api.doppler.com/v3/configs/config/secrets";
       
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $dopplerUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
    
        $data = json_decode($response, true);
    
        // Extract URL and API key for the given pharmacy ID
        $apiUrl = $data['secrets'][$pharmacyId . '_URL']['value'] ?? null;
        $apiKey = $data['secrets'][$pharmacyId . '_KEY']['value'] ?? null;
    
        return [$apiUrl, $apiKey];
    }
    
    function isMedicationMatch($userInput, $brandName, $genericName) {
        $userInput = strtolower($userInput);
        $brandName = strtolower($brandName);
        $genericName = strtolower($genericName);
    
        // Define a threshold for Levenshtein distance (adjust as needed)
        $threshold = 3;
    
        // Check if the user input matches the brand name or generic name
        $brandMatch = levenshtein($userInput, $brandName) <= $threshold;
        $genericMatch = levenshtein($userInput, $genericName) <= $threshold;
    
        return $brandMatch || $genericMatch;
    }
    
?>
