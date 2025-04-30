<?php
namespace App;

// Add this line to import the Elasticsearch client class
use Elastic\Elasticsearch\Client;

class ElasticsearchBulkProcessor {
    private Client $client;
    private array $queue = [];
    private int $maxBatchSize;

    public function __construct(Client $client, int $maxBatchSize = 100) {
        $this->client = $client;
        $this->maxBatchSize = $maxBatchSize;
    }

    public function addToBatch(string $index, string $id, array $document): void {
        $this->queue[] = ['index' => ['_index' => $index, '_id' => $id]];
        $this->queue[] = $document;

        if (count($this->queue) >= $this->maxBatchSize * 2) {
            $this->flush();
        }
    }

    public function flush(): void {
        if (!empty($this->queue)) {
            $params = [
                'body' => $this->queue,
                'refresh' => true // Optional: make changes visible immediately
            ];
            
            try {
                $response = $this->client->bulk($params);
                
                if ($response['errors'] ?? false) {
                    error_log('Bulk operation had errors: ' . json_encode($response));
                }
            } catch (\Exception $e) {
                error_log('Elasticsearch bulk error: ' . $e->getMessage());
                throw $e;
            }
            
            $this->queue = [];
        }
    }

    public function __destruct() {
        $this->flush();
    }
}