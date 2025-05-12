<?php
// Force strict error reporting
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Connection parameters
define('DB_HOST', '127.0.0.1'); // Force IPv4
define('DB_PORT', 3306);
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'SHIFA');

function getDatabaseConnection(): mysqli {
    static $conn = null;

    try {
        if (!$conn instanceof mysqli || !$conn->ping()) {
            $conn = new mysqli(
                DB_HOST,
                DB_USER,
                DB_PASS,
                DB_NAME,
                DB_PORT
            );

            // Remove or comment out unsupported options
            // $conn->options(MYSQLI_OPT_WRITE_TIMEOUT, 2);

            $conn->set_charset('utf8mb4');
            $conn->query("SET SESSION wait_timeout = 60");
            $conn->query("SET SESSION interactive_timeout = 60");
        }
        return $conn;
    } catch (mysqli_sql_exception $e) {
        error_log("Connection failed: " . $e->getMessage());
        throw new RuntimeException("Database unavailable. Please try again later.");
    }
}
