<?php
header("Content-Type: application/json");

function getBaseName($name) {
    $parts = preg_split('/[^a-zA-Z]+/', $name, -1, PREG_SPLIT_NO_EMPTY);
    return empty($parts) ? '' : strtolower($parts[0]);
}

// Authentication
$expected_api_key = getenv('API_KEY');
$provided_api_key = $_GET['api_key'] ?? '';
if ($provided_api_key !== $expected_api_key) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// Database connection
$conn = new mysqli("taleb-pharmacy-db", "root", "taleb", "taleb_pharmacy_db");
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Get search query
$inputName = trim($_GET['name'] ?? '');
$medications = [];

// Fetch only required fields
$result = $conn->query("SELECT Produit, Prix_Vente_TTC, Quantite FROM inventaire_pharmacie");
while ($row = $result->fetch_assoc()) {
    $medications[] = [
        'Produit' => $row['Produit'],
        'Prix_Vente_TTC' => $row['Prix_Vente_TTC'],
        'Quantite' => $row['Quantite']
    ];
}

if (!empty($inputName)) {
    $inputBase = getBaseName($inputName);
    $closestDistance = PHP_INT_MAX;
    $closestBases = [];
    $MAX_DISTANCE = 3;

    // Find closest match in Produit field
    foreach ($medications as $med) {
        $produitBase = getBaseName($med['Produit']);
        $distance = levenshtein($inputBase, $produitBase);

        if ($distance < $closestDistance) {
            $closestDistance = $distance;
            $closestBases = [$produitBase];
        } elseif ($distance == $closestDistance) {
            $closestBases[] = $produitBase;
        }
    }

    // Apply threshold
    if ($closestDistance <= $MAX_DISTANCE) {
        $baseCounts = array_count_values($closestBases);
        arsort($baseCounts);
        $targetBase = array_key_first($baseCounts);

        $filtered = [];
        foreach ($medications as $med) {
            if (getBaseName($med['Produit']) === $targetBase) {
                $filtered[] = $med;
            }
        }
        $medications = $filtered;
    } else {
        $medications = [];
    }
}

echo json_encode($medications);
$conn->close();
?>