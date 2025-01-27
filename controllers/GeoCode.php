<?php
function geocodeAddress($address) {
    $apiKey = '67979ed357e9b903835538psg98272b';  

    $encodedAddress = urlencode($address); 
   // $url = "https://api.mapmaker.com/geocode?address=" . $encodedAddress . "&key=" . $apiKey;
    $url = "https://geocode.maps.co/search?q=". $encodedAddress . "&key=" . $apiKey;
     https://geocode.maps.co/search?q=Pharmacie+de.+Chawal,+%D8%A7%D9%84%D8%B7%D8%B1%D9%8A%D9%82+%D8%A7%D9%84%D9%88%D9%8F%D8%B7%D9%86%D9%8A+%D8%B1%D9%82%D9%85+4%D8%8C+Boukadir&key=67979ed357e9b903835538psg98272b&limit=1

    $response = file_get_contents($url);
    var_dump($response);
    $data = json_decode($response, true);

    if (isset($data['results'][0])) {
        $location = $data['results'][0]['geometry']['location'];
        return [
            'latitude' => $location['lat'],
            'longitude' => $location['lng']
        ];
    }

    return null;  
}
