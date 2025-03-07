<?php
session_start();
require_once '../connection.php'; // Using your connection file

// Load Doppler token from environment
$DOPPLER_TOKEN = getenv('DOPPLER_TOKEN');
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

    // Test API credentials
    if (testPharmacyAPI($api_url, $api_key)) {
        // Store credentials in Doppler
        if (storeSecretsInDoppler($pharmacy_id,$pharmacy_name, $api_url, $api_key, $DOPPLER_TOKEN)) {
            // Update pharmacy status to 'active'
            $stmt = $conn->prepare('UPDATE pharmacies SET status = "active" WHERE pharmacy_id = ?');
            $stmt->bind_param('i', $pharmacy_id);

            if ($stmt->execute()) {
                header('Location: /home.php');
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
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $key
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $http_code === 200 && $response;
}

function storeSecretsInDoppler($pharmacy_id, $pharmacy_name, $api_url, $api_key, $token) {
    $doppler_url = 'https://api.doppler.com/v3/configs/dev/secrets';

    $data = [
        "pharmacy_name_$pharmacy_id" => $pharmacy_name,
        "api_url_$pharmacy_id" => $api_url,
        "api_key_$pharmacy_id" => $api_key
    ];

    $ch = curl_init($doppler_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["secrets" => $data]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $http_code === 200;
}
?>
