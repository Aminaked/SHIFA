<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require 'connection.php';

// Debugging: Check if $conn is initialized
if (!isset($conn)) {
    die('Database connection failed: $conn is not initialized.');
}

// Fetch Doppler token from environment variables
$DOPPLER_TOKEN = $_SERVER['DOPPLER_TOKEN'] ?? null;
if (!$DOPPLER_TOKEN) {
    die('Doppler token not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $medicineName = htmlspecialchars(trim($_POST['medication']));
    $userLat = $_POST['user_lat'] ?? null;
    $userLon = $_POST['user_lon'] ?? null;

    // Basic validation
    if (empty($medicineName)) {
        echo json_encode(['error' => 'Please enter a medicine name.']);
        exit;
    }

    if ($userLat === null || $userLon === null) {
        echo json_encode(['error' => 'Location information is missing.']);
        exit;
    }

    // Fetch all pharmacies from the database
    try {
        $query = "SELECT pharmacy_id, pharmacy_name, longitude, latitude FROM pharmacy";
        $result = $conn->query($query);

        if (!$result) {
            die(json_encode(['error' => 'Database query failed: ' . $conn->error]));
        }

        $pharmacies = $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        die(json_encode(['error' => 'Database query failed: ' . $e->getMessage()]));
    }

    $results = [];
    foreach ($pharmacies as $pharmacy) {
        // Calculate distance using Haversine formula
        $distance = haversineDistance($userLat, $userLon, $pharmacy['latitude'], $pharmacy['longitude']);

        // Fetch API credentials for the pharmacy
        list($apiUrl, $apiKey) = fetchApiCredentials($pharmacy['pharmacy_id'], $DOPPLER_TOKEN);

        if ($apiUrl && $apiKey) {
            // Append the API key as a query parameter to the URL
            $apiUrlWithKey = $apiUrl . '?api_key=' . urlencode($apiKey);

            // Make a GET request to the API
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrlWithKey);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $apiResponse = curl_exec($ch);

            if (curl_errno($ch)) {
                // Handle cURL error
                curl_close($ch);
                continue;
            }

            curl_close($ch);

            // Decode the API response
            $stockData = json_decode($apiResponse, true);

            // Validate the API response
            if (!is_array($stockData)) {
                continue; // Skip to the next pharmacy
            }

            // Filter medications locally
            foreach ($stockData as $medication) {
                // Ensure required keys exist
                if (isset($medication['Brand_Name'], $medication['Generic_Name'], $medication['Quantity'])) {
                    // Check if the medication matches (brand name or generic name)
                    if (isMedicationMatch($medicineName, $medication['Brand_Name'], $medication['Generic_Name'])) {
                        // Check if the medication is in stock (quantity > 0)
                        if ($medication['Quantity'] > 0) {
                            $results[] = [
                                'pharmacy_id' => $pharmacy['pharmacy_id'],
                                'pharmacy_name' => $pharmacy['pharmacy_name'],
                                'address' => $pharmacy['address'] ?? 'N/A',
                                'distance' => round($distance, 2) . ' km',
                                'stock' => $medication['Quantity'] . ' in stock',
                                'Brand_Name' => $medication['Brand_Name'],
                                'Generic_Name' => $medication['Generic_Name']
                            ];
                        }
                    }
                }
            }
        }
    }

    // Sort results by distance (nearest first)
    usort($results, function ($a, $b) {
        return $a['distance'] <=> $b['distance'];
    });

    // Output results as JSON
    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}

// Haversine distance function
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

// Fetch API credentials from Doppler
function fetchApiCredentials($pharmacyId, $token) {
    $dopplerUrl = "https://api.doppler.com/v3/configs/config/secrets";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $dopplerUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        // Handle cURL error
        curl_close($ch);
        return [null, null];
    }

    curl_close($ch);

    $data = json_decode($response, true);

    // Extract URL and API key for the given pharmacy ID
    $apiUrl = $data['secrets']['API_URL_'.$pharmacyId]['raw'] ?? null;
    $apiKey = $data['secrets']['API_KEY_'.$pharmacyId]['raw'] ?? null;

    return [$apiUrl, $apiKey];
}

// Check if the medication matches (brand name or generic name)
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