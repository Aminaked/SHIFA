<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['error' => 'Invalid request method.']));
}


require 'connection.php';

if (!isset($conn)) {
    die(json_encode(['error' => 'Database connection failed.']));
}

// Validate and sanitize user input
$medicineName = filter_input(INPUT_POST, 'medication', FILTER_SANITIZE_STRING);
$userLat = filter_input(INPUT_POST, 'user_lat', FILTER_VALIDATE_FLOAT);
$userLon = filter_input(INPUT_POST, 'user_lon', FILTER_VALIDATE_FLOAT);

if (empty($medicineName)) {
    die(json_encode(['error' => 'Please enter a valid medicine name.']));
}

if ($userLat === false || $userLon === false) {
    die(json_encode(['error' => 'Invalid location coordinates.']));
}

// Fetch Doppler token from environment
$DOPPLER_TOKEN = $_SERVER['DOPPLER_TOKEN'] ?? null;
if (!$DOPPLER_TOKEN) {
    die(json_encode(['error' => 'Doppler token not found.']));
}

// Initialize Redis for caching
$redis = new Redis();
try {
    $redis->connect('127.0.0.1', 6379);
} catch (Exception $e) {
    die(json_encode(['error' => 'Redis connection failed: ' . $e->getMessage()]));
}

// Fetch all pharmacies from the database
try {
    $query = "SELECT pharmacy_id, pharmacy_name, address, longitude, latitude FROM pharmacy";
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception('Database query failed: ' . $conn->error);
    }

    $pharmacies = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    die(json_encode(['error' => $e->getMessage()]));
}

// Initialize Guzzle client for asynchronous API calls
$client = new GuzzleHttp\Client();
$promises = [];
$results = [];

foreach ($pharmacies as $pharmacy) {
    // Calculate distance using Haversine formula
    $distance = haversineDistance($userLat, $userLon, $pharmacy['latitude'], $pharmacy['longitude']);

    // Fetch API credentials for the pharmacy
    list($apiUrl, $apiKey) = fetchApiCredentials($pharmacy['pharmacy_id'], $DOPPLER_TOKEN);

    if (!$apiUrl || !$apiKey) {
        continue; // Skip if credentials are not found
    }

    // Append the API key as a query parameter to the URL
    $apiUrlWithKey = $apiUrl . '?api_key=' . urlencode($apiKey);

    // Check cache first
    $cacheKey = 'pharmacy_stock_' . md5($apiUrlWithKey);
    $stockData = $redis->get($cacheKey);

    if ($stockData) {
        $stockData = json_decode($stockData, true);
        $results = array_merge($results, processApiResponse($stockData, $pharmacy, $distance));
    } else {
        // Make asynchronous API call if not cached
        $promises[$pharmacy['pharmacy_id']] = $client->getAsync($apiUrlWithKey);
    }
}

// Wait for all asynchronous API calls to complete
if (!empty($promises)) {
    $responses = GuzzleHttp\Promise\Utils::settle($promises)->wait();

    foreach ($responses as $pharmacyId => $response) {
        if ($response['state'] === 'fulfilled') {
            $stockData = json_decode($response['value']->getBody(), true);

            // Cache the API response for 10 minutes
            $cacheKey = 'pharmacy_stock_' . md5($apiUrlWithKey);
            $redis->set($cacheKey, json_encode($stockData), 600);

            // Process the API response
            $pharmacy = $pharmacies[$pharmacyId];
            $distance = haversineDistance($userLat, $userLon, $pharmacy['latitude'], $pharmacy['longitude']);
            $results = array_merge($results, processApiResponse($stockData, $pharmacy, $distance));
        } else {
            // Log API call failure (you can use a logging library here)
            error_log("API call failed for pharmacy ID: $pharmacyId");
        }
    }
}

// Sort results by distance (nearest first)
usort($results, function ($a, $b) {
    return $a['distance'] <=> $b['distance'];
});

// Paginate results (e.g., limit to 10 results)
$limit = 10;
$paginatedResults = array_slice($results, 0, $limit);

// Compress API response
//ob_start('ob_gzhandler');
header('Content-Type: application/json');
echo json_encode($paginatedResults);
//ob_end_flush();
exit;

/**
 * Haversine distance function to calculate distance between two coordinates.
 */
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

/**
 * Fetch API credentials from Doppler.
 */
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
        curl_close($ch);
        return [null, null];
    }

    curl_close($ch);

    $data = json_decode($response, true);

    // Extract URL and API key for the given pharmacy ID
    $apiUrl = $data['secrets']['API_URL_' . $pharmacyId]['raw'] ?? null;
    $apiKey = $data['secrets']['API_KEY_' . $pharmacyId]['raw'] ?? null;

    return [$apiUrl, $apiKey];
}

/**
 * Process API response and match medication.
 */
function processApiResponse($stockData, $pharmacy, $distance, $medicineName) {
    $results = [];
    foreach ($stockData as $medication) {
        if (isMedicationMatch($medicineName, $medication['Brand_Name'], $medication['Generic_Name'])) {
            if ($medication['Quantity'] > 0) {
                $results[] = [
                    'pharmacy_id' => $pharmacy['pharmacy_id'],
                    'pharmacy_name' => $pharmacy['pharmacy_name'],
                    'address' => $pharmacy['address'] ?? 'N/A',
                    'distance' => round($distance, 2) . ' km',
                    'stock' => $medication['Quantity'] . ' in stock',
                    'Brand_Name' => $medication['Brand_Name'],
                    'longitude' => $pharmacy['longitude'],
                    'latitude' => $pharmacy['latitude'],
                    'Generic_Name' => $medication['Generic_Name']
                ];
            }
        }
    }
    return $results;
}

/**
 * Check if the medication matches (brand name or generic name).
 */
function isMedicationMatch($userInput, $brandName, $genericName) {
    $userInput = strtolower($userInput);
    $brandName = strtolower($brandName);
    $genericName = strtolower($genericName);

    // Step 1: Direct match
    if ($userInput === $brandName || $userInput === $genericName) {
        return true;
    }

    // Step 2: Metaphone match
    $userMetaphone = metaphone($userInput);
    $brandMetaphone = metaphone($brandName);
    $genericMetaphone = metaphone($genericName);

    if ($userMetaphone === $brandMetaphone || $userMetaphone === $genericMetaphone) {
        return true;
    }

    // Step 3: Levenshtein match
    $threshold = 3; // Allow up to 3 edits
    $levenshteinBrand = levenshtein($userInput, $brandName);
    $levenshteinGeneric = levenshtein($userInput, $genericName);

    return $levenshteinBrand <= $threshold || $levenshteinGeneric <= $threshold;
}
?>