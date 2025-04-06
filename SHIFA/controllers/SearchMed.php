<?php
declare(strict_types=1);

// Configuration
const ENV_PRODUCTION = false;
const MAX_EXECUTION_TIME = 30; // seconds
const API_TIMEOUT = 5; // seconds
const EARTH_RADIUS_KM = 6371;
const LEVENSHTEIN_THRESHOLD = 3;

// Environment setup
ini_set('display_errors', ENV_PRODUCTION ? '0' : '1');
error_reporting(ENV_PRODUCTION ? E_ALL & ~E_DEPRECATED & ~E_STRICT : E_ALL);
set_time_limit(MAX_EXECUTION_TIME);

// Database and API dependencies
require_once 'connection.php';
require_once 'session.php';
$DOPPLER_TOKEN = $_SERVER['DOPPLER_TOKEN'] ?? die(json_encode([
    'success' => false,
    'error' => 'Server configuration error',
    'error_code' => 500
]));

// Main controller
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $response = processSearchRequest();
        header('Content-Type: application/json');
        echo json_encode($response);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => ENV_PRODUCTION ? 'Service unavailable' : $e->getMessage(),
            'error_code' => 500
        ]);
    }
} else {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed',
        'error_code' => 405
    ]);
}

function processSearchRequest(): array {
    // Validate and sanitize input
    $input = validateInput($_POST);
    
    // Get pharmacy data with single query
    $pharmacies = getPharmacies();
    if (empty($pharmacies)) {
        return createResponse([], $input['medication'], $input['latitude'], $input['longitude']);
    }

    // Process pharmacies in optimized batch
    $results = processPharmacies($pharmacies, $input);
    
    // Sort by distance (optimized comparison)
    usort($results, fn($a, $b) => $a['distance_value'] <=> $b['distance_value']);
    
    return createResponse($results, $input['medication'], $input['latitude'], $input['longitude']);
}

function validateInput(array $post): array {
    $medication = trim($post['medication'] ?? '');
    $lat = $post['user_lat'] ?? null;
    $lon = $post['user_lon'] ?? null;

    if (empty($medication)) {
        http_response_code(400);
        die(json_encode([
            'success' => false,
            'error' => 'Medication name required',
            'error_code' => 400
        ]));
    }

    if (!is_numeric($lat) || !is_numeric($lon)) {
        http_response_code(400);
        die(json_encode([
            'success' => false,
            'error' => 'Valid coordinates required',
            'error_code' => 400
        ]));
    }

    return [
        'medication' => $medication,
        'latitude' => (float)$lat,
        'longitude' => (float)$lon
    ];
}

function getPharmacies(): array {
    global $conn;
    
    static $query = "SELECT pharmacy_id, pharmacy_name, phone_number, email,  address, longitude, latitude 
                     FROM pharmacy 
                     WHERE status = 'active' "; // Added active flag check
    
    $result = $conn->query($query);
    if (!$result) {
        error_log("Database error: " . $conn->error);
        return [];
    }
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function processPharmacies(array $pharmacies, array $input): array {
    $results = [];
    $credentialsCache = [];
    
    foreach ($pharmacies as $pharmacy) {
        $distance = haversineDistance(
            $input['latitude'],
            $input['longitude'],
            (float)$pharmacy['latitude'],
            (float)$pharmacy['longitude']
        );
        
        // Cache credentials for performance
        $pharmacyId = (int)$pharmacy['pharmacy_id'];
        if (!isset($credentialsCache[$pharmacyId])) {
            $credentialsCache[$pharmacyId] = getPharmacyCredentials($pharmacyId);
        }
        
        list($apiUrl, $apiKey) = $credentialsCache[$pharmacyId];
        

        if ($apiUrl && $apiKey) {
            $stockData = fetchApiData($apiUrl, $apiKey);
            if ($stockData === false) continue;
            
            foreach (findMedicationMatches($input['medication'], $stockData) as $medication) {
                $results[] = [
                    'pharmacy_id' =>$pharmacyId,
                    'pharmacy_name' => $pharmacy['pharmacy_name'],
                    'address' => $pharmacy['address'] ?? 'N/A',
                    'email'=> $pharmacy['email'] ??'N/A',
                    'phone_number'=> $pharmacy['phone_number'] ??'N/A',
                    'distance' => round($distance, 2) . ' km',
                    'distance_value' => $distance,
                    'stock' => 'in stock',
                    'Brand_Name' => $medication['Brand_Name'],
                    'ph_longitude' => $pharmacy['longitude'],
                    'ph_latitude' => $pharmacy['latitude'],
                    'Generic_Name' => $medication['Generic_Name']
                ];
            }
        }
    }
    
    return $results;
}

function getPharmacyCredentials(int $pharmacyId): array {
    global $DOPPLER_TOKEN;
    
    static $credentials = [];
    if (isset($credentials[$pharmacyId])) {
        return $credentials[$pharmacyId];
    }
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.doppler.com/v3/configs/config/secrets',
        CURLOPT_HTTPHEADER => ["Authorization: Bearer $DOPPLER_TOKEN"],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => API_TIMEOUT
    ]);
    
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return $credentials[$pharmacyId] = [null, null];
    }
    curl_close($ch);
    
    $data = json_decode($response, true)['secrets'] ?? [];
    return $credentials[$pharmacyId] = [
        $data["API_URL_$pharmacyId"]['raw'] ?? null,
        $data["API_KEY_$pharmacyId"]['raw'] ?? null
    ];
}

function fetchApiData(string $url, string $apiKey): array {
    static $curlHandle = null;
    
    if ($curlHandle === null) {
        $curlHandle = curl_init();
        curl_setopt_array($curlHandle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => API_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 2
        ]);
    }
    
    curl_setopt($curlHandle, CURLOPT_URL, $url . '?api_key=' . urlencode($apiKey));
    $response = curl_exec($curlHandle);
    
    return curl_errno($curlHandle) ? false : (json_decode($response, true) ?: []);
}

function findMedicationMatches(string $searchTerm, array $stockData): Generator {
    $searchTerm = strtolower($searchTerm);
    
    foreach ($stockData as $item) {
        if (empty($item['Brand_Name']) || empty($item['Generic_Name']) || empty($item['Quantity'])) {
            continue;
        }
        
        if ($item['Quantity'] > 0) {
            $brandMatch = levenshtein($searchTerm, strtolower($item['Brand_Name'])) <= LEVENSHTEIN_THRESHOLD;
            $genericMatch = levenshtein($searchTerm, strtolower($item['Generic_Name'])) <= LEVENSHTEIN_THRESHOLD;
            
            if ($brandMatch || $genericMatch) {
                yield $item;
            }
        }
    }
}

function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float {
    $latDelta = deg2rad($lat2 - $lat1);
    $lonDelta = deg2rad($lon2 - $lon1);
    
    $a = sin($latDelta / 2) ** 2 + 
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
         sin($lonDelta / 2) ** 2;
    
    return EARTH_RADIUS_KM * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

function createResponse(array $results, string $medication, float $lat, float $lon): array {
    return [
        'success' => true,
        'message' => empty($results) ? 'No matches found' : sprintf('%d %s found', count($results), count($results) === 1 ? 'pharmacy' : 'pharmacies'),
        'data' => $results,
        'metadata' => [
            'count' => count($results),
            'timestamp' => date(DATE_ATOM),
            'search_term' => $medication,
            'location' => ['latitude' => $lat, 'longitude' => $lon],
            'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
        ]
    ];
}