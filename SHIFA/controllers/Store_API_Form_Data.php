<?php

require_once 'connection.php'; 



$DOPPLER_TOKEN = $_SERVER['DOPPLER_TOKEN'] ;
if (!$DOPPLER_TOKEN) {
    die('Doppler token not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['pharmacy_id'])) {
        die('Pharmacy ID not found in session.');
    }

    $pharmacy_id = $_SESSION['pharmacy_id'];
    $pharmacy_name = $_POST['pharmacy_name'];
    $api_url = $_POST['api_url'];
    $api_key = $_POST['api_key'];

    if (empty($api_url) || empty($api_key)) {
        die('API URL and key are required.');
    }

    
    if (testPharmacyAPI($api_url, $api_key)) {
        // Store credentials in Doppler
        if (storeSecretsInDoppler($pharmacy_id,$pharmacy_name, $api_url, $api_key, $DOPPLER_TOKEN)) {
            // Update pharmacy status to 'active'
            $stmt = $conn->prepare('UPDATE pharmacy SET status = "active" WHERE pharmacy_id = ?');
            $stmt->bind_param('i', $pharmacy_id);

            if ($stmt->execute()) {
                header('Location: ../views/PHhomepage.php');
                exit;
            } else {
                echo 'Failed to update pharmacy status.';
            }
        } else {
            echo 'Failed to store API credentials in Doppler.';
        }
    } else {
        echo 'Invalid API credentials. Please check and try again.';
    }
}


function testPharmacyAPI($url, $key) {
    // Add the API key as a query parameter
    $url = $url . '?api_key=' . urlencode($key);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Debugging: Log the API URL and response
    error_log("Debug: API URL: $url");
    error_log("Debug: API Response Code: $http_code");
    error_log("Debug: API Response: $response");

    if (curl_errno($ch)) {
        error_log("CURL Error: " . curl_error($ch));
        curl_close($ch);
        return false;
    }

    curl_close($ch);
    return $http_code === 200;
}

function storeSecretsInDoppler($pharmacy_id, $pharmacy_name, $api_url, $api_key, $token) {
   
    $doppler_url = 'https://api.doppler.com/v3/configs/config/secrets/download?format=json';

    // Prepare the data to be sent as JSON
    $data = [
        "pharmacy_name_$pharmacy_id" => $pharmacy_name,
        "api_url_$pharmacy_id" => $api_url,
        "api_key_$pharmacy_id" => $api_key
    ];

    // Initialize cURL
    $ch = curl_init($doppler_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET'); // Use GET instead of POST
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ]);

    // Execute the request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Debugging: Log the request and response
    error_log("Debug: Doppler API Request URL: $doppler_url");
    error_log("Debug: Doppler API Response Code: $http_code");
    error_log("Debug: Doppler API Response: $response");

    if (curl_errno($ch)) {
        error_log("CURL Error: " . curl_error($ch));
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    // Check if the request was successful
    return $http_code === 200;
}
?>
