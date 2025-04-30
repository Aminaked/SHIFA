<?php
class DatabasePool {
    private static $pool = [];
    private const MAX_CONNECTIONS = 10;

    public static function getConnection(): PDO {
        if (empty(self::$pool)) {
            $conn = new PDO(
                "mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME'),
                getenv('DB_USER'),
                getenv('DB_PASS')
            );
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        }
        return array_pop(self::$pool);
    }

    public static function releaseConnection(PDO $conn): void {
        if (count(self::$pool) < self::MAX_CONNECTIONS) {
            self::$pool[] = $conn;
        } else {
            $conn = null; // Close if pool is full
        }
    }
}
?>