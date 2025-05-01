<?php

require __DIR__ . '/../vendor/autoload.php';
use Elastic\Elasticsearch\ClientBuilder;
use App\ElasticsearchBulkProcessor;
use App\DatabasePool;
class PharmacySync {
   // private  $DOPPLER_TOKEN = "security measurement";
    private $elastic;
    private $initialized = false;
    private $bulkProcessor;

    public function __construct() {
        $this->elastic = Elastic\Elasticsearch\ClientBuilder::create()
            ->setHosts([getenv('ELASTICSEARCH_URL')?: 'http://172.23.64.1:9200'])
            ->build();
            
        $this->bulkProcessor = new ElasticsearchBulkProcessor($this->elastic);
        $this->initializePharmacies(); // Initialize once on startup
       

    }

    private function initializePharmacies(): void {
        if ($this->initialized || file_exists('/tmp/.pharmacies_initialized')) return;
        
        $pharmacyIds = $this->getActivePharmacyIds();
        foreach ($pharmacyIds as $id) {
            $this->updatePharmacyInfo($id, true);
        }
        
        file_put_contents('/tmp/.pharmacies_initialized', '1');
        $this->initialized = true;
    }

    public function runHourlySync(): void {
        while (true) {
            foreach ($this->getActivePharmacyIds() as $pharmacyId) {
                $this->syncInventory($pharmacyId);
            }
            
            $this->bulkProcessor->flush();
            sleep((int) getenv('SYNC_INTERVAL') ?: 3600);
        }
    }

    private function syncInventory(int $pharmacyId): void {
        try {
            [$apiUrl, $apiKey] = $this->getPharmacyCredentials($pharmacyId);
            $inventory = $this->callPharmacyApi($apiUrl, $apiKey);
            
            $this->bulkProcessor->addToBatch(
                'pharmacies',
                (string)$pharmacyId,
                [
                    'doc' => [
                        'inventory' => $inventory,
                        'inventory_updated' => date('c')
                    ],
                    'doc_as_upsert' => true
                ],
                'update'
            );
            
        } catch (Exception $e) {
            error_log("Inventory sync failed for $pharmacyId: " . $e->getMessage());
        }
    }

    public function updatePharmacyInfo(int $pharmacyId, bool $initialSetup = false): void {
        try {
            $db = DatabasePool::getConnection();
            $stmt = $db->prepare("
                SELECT pharmacy_id, pharmacy_name, phone_number, 
                       email, address,  longitude, 
                       latitude
                FROM pharmacy 
                WHERE status = 'active' 
AND pharmacy_id = ?
            ");
            
            $stmt->execute([$pharmacyId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (empty($data)) {
                throw new Exception("Pharmacy $pharmacyId not found");
            }

            $this->elastic->update([
                'index' => 'pharmacies',
                'id'    => (string)$pharmacyId,
                'body'  => [
                    'doc' => [
                        'info' => [
                            'name' => $data['pharmacy_name'],
                            
                                'phone_number' => $data['phone_number'],
                                'email' => $data['email']
                            ,
                            'address' => $data['address'],
                            
                                'ph_latitude' => $data['latitude'],
                                'ph_longitude' => $data['longitude']
                            ,
                            'info_updated' => date('c')
                        ]
                    ],
                    'doc_as_upsert' => true
                ],
                'refresh' => $initialSetup
            ]);
            
        } catch (Exception $e) {
            error_log("Pharmacy info update failed: " . $e->getMessage());
            throw $e;
        } finally {
            if (isset($db)) DatabasePool::releaseConnection($db);
        }
    }

    private function getActivePharmacyIds(): array {
        $db = DatabasePool::getConnection();
        try {
            return $db->query("SELECT pharmacy_id FROM pharmacy WHERE status = 'active'")
                     ->fetchAll(PDO::FETCH_COLUMN);
        } finally {
            DatabasePool::releaseConnection($db);
        }
    }

    private function getPharmacyCredentials(int $pharmacyId): array {
        global $DOPPLER_TOKEN;
        if (empty($DOPPLER_TOKEN)) {
            throw new RuntimeException("Doppler token not configured");
        }
        static $credentials = [];
        if (isset($credentials[$pharmacyId])) return $credentials[$pharmacyId];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.doppler.com/v3/configs/config/secrets?project=shifa&config=dev",
            CURLOPT_HTTPHEADER => ["Authorization: Bearer *DOPPLER_TOKEN"],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = json_decode(curl_exec($ch),);
        error_log("Doppler API raw response: " . $response); 
        $secrets = $response['secrets'] ?? [];
        error_log("Available secrets: " . print_r(array_keys($secrets), true));
        return $credentials[$pharmacyId] = [
            $secrets["API_URL_{$pharmacyId}"]['raw'] ?? null,
            $secrets["API_KEY_{$pharmacyId}"]['raw'] ?? null
        ];
    }

    private function callPharmacyApi(string $apiUrl, string $apiKey): array {
        $client = new GuzzleHttp\Client();
        
        $response = $client->get("{$apiUrl}/inventory", [
            'headers' => [
                'Authorization' => "Bearer {$apiKey}",
                'Accept' => 'application/json'
            ],
            'timeout' => 15
        ]);
        
        $data = json_decode($response->getBody(), true);
        
        if (empty($data['inventory'])) {
            throw new Exception("Empty inventory response");
        }
        
        return $data;
    }
    private function syncWithRetry(int $pharmacyId, int $maxRetries = 3): bool {
        $retryDelays = explode(',', getenv('RETRY_DELAYS') ?: '100,500,1000');
        $attempt = 0;
    
        while ($attempt < $maxRetries) {
            try {
                // 1. Get credentials FIRST
                [$apiUrl, $apiKey] = $this->getPharmacyCredentials($pharmacyId);
                
                if (empty($apiUrl) || empty($apiKey)) {
                    throw new Exception("Missing credentials for pharmacy $pharmacyId");
                }
    
                // 2. Call API with credentials
                $inventory = $this->callPharmacyApi($apiUrl, $apiKey);
                
                // 3. Index data
                $this->indexData($pharmacyId, $inventory);
                
                return true;
                
            } catch (Exception $e) {
                $attempt++;
                error_log(sprintf(
                    "Attempt %d/%d failed for pharmacy %d: %s",
                    $attempt,
                    $maxRetries,
                    $pharmacyId,
                    $e->getMessage()
                ));
                
                if ($attempt >= $maxRetries) return false;
                usleep((int) $retryDelays[$attempt-1] * 1000);
            }
        }
        return false;
    }
    // Keep existing helper methods (getActivePharmacyIds, 
    // getPharmacyCredentials, callPharmacyApi, syncWithRetry) unchanged
}

// Execution context handling
if (php_sapi_name() === 'cli') {
    // CLI mode: Hourly inventory sync
    (new PharmacySync())->runHourlySync();
} else {
    // Web mode: Handle DB triggers
    header('Content-Type: text/plain');
    
    // Authentication
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if ($authHeader !== 'Bearer ' . getenv('SYNC_TOKEN')) {
        http_response_code(403);
        exit('Forbidden');
    }

    // Validate input
    $pharmacyId = (int)($_POST['pharmacy_id'] ?? 0);
    if ($pharmacyId <= 0) {
        http_response_code(400);
        exit('Invalid pharmacy ID');
    }

    // Process trigger
    try {
        (new PharmacySync())->updatePharmacyInfo($pharmacyId);
        http_response_code(204);
    } catch (Exception $e) {
        http_response_code(500);
        exit('Update failed');
    }
}