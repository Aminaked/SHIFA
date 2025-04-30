<?php

class PharmacySyncOrchestrator {
    public function syncBatch(array $pharmacyIds): void {
        $client = new \GuzzleHttp\Client();
        $promises = [];

        foreach ($pharmacyIds as $pharmacyId) {
            $promises[] = $client->getAsync("https://api.pharmacy.com/inventory", [
                'headers' => ['Authorization' => 'Bearer ' . $this->getApiKey($pharmacyId)],
                'timeout' => 10
            ])->then(
                function ($response) use ($pharmacyId) {
                    $data = json_decode($response->getBody(), true);
                    $this->indexData($pharmacyId, $data['inventory']);
                },
                function ($error) use ($pharmacyId) {
                    error_log("Failed sync for pharmacy $pharmacyId: " . $error->getMessage());
                }
            );
        }

        \GuzzleHttp\Promise\Utils::settle($promises)->wait();
    }
}
?>