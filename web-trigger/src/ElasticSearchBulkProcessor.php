<?php
// ElasticsearchBulkProcessor.php
class ElasticsearchBulkProcessor {
    private $client;
    private $queue = [];
    private $maxBatchSize;

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
            $this->client->bulk(['body' => $this->queue]);
            $this->queue = [];
        }
    }

    public function __destruct() {
        $this->flush(); // Auto-flush on script end
    }
}
?>