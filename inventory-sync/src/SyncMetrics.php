<?php

// SyncMetrics.php
class SyncMetrics {
    public static function logSyncResult(bool $success, int $pharmacyId, float $duration): void {
        $tags = ["pharmacy_id:$pharmacyId"];
        if ($success) {
            StatsD::increment('pharmacy.sync.success', $tags);
            StatsD::timing('pharmacy.sync.latency', $duration, $tags);
        } else {
            StatsD::increment('pharmacy.sync.failure', $tags);
        }
    }
}
?>