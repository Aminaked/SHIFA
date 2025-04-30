<?php
namespace App;
class DatabasePool {
    private static $pool = [];
    private const MAX_CONNECTIONS = 10;

    public static function getConnection(): \PDO {
        $host = '172.23.64.1'; // Special DNS for Docker->host communication
        $db   = 'SHIFA';
        $user = 'root';
        $pass = '';
        $port = '3306';
        if (empty(self::$pool)) {
            $conn = new \PDO(
              "mysql:host=$host;port=$port;dbname=$db",
        $user,
        $pass,
            );
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $conn;
        }
        return array_pop(self::$pool);
    }

    public static function releaseConnection(\PDO $conn): void {
        if (count(self::$pool) < self::MAX_CONNECTIONS) {
            self::$pool[] = $conn;
        } else {
            $conn = null; // Close if pool is full
        }
    }
}
?>