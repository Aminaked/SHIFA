<?php
function geocodeAddress($address) {
    $apiKey = '67979ed357e9b903835538psg98272b';
    $encodedAddress   = urlencode($address); 
    $url = "https://geocode.maps.co/search?q=" . $encodedAddress . "&api_key=" . $apiKey;
  



    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP status code
    curl_close($ch);

    // Debug: Log the raw response and HTTP status code
    file_put_contents('geocode_debug.log', "URL: $url\nHTTP Code: $httpCode\nResponse: $response\n\n", FILE_APPEND);

    $data = json_decode($response, true);

    // Check if the response contains valid data
    if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
        return [
            'latitude' => $data[0]['lat'],
            'longitude' => $data[0]['lon']
        ];
    }

    return null; // Geocoding failed
}
