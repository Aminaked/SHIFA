<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "SHIFA";

function getDatabaseConnection() {
    global $servername, $username, $password, $dbname;
    static $conn = null;
    
    if ($conn === null || !$conn->ping()) {
        // Close existing connection if it exists
        if ($conn instanceof mysqli) {
            $conn->close();
        }

        // Create new connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        // Check connection
        if ($conn->connect_error) {
            error_log("MySQL Connection Error: " . $conn->connect_error);
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }
        
        // Set timezone if needed
        $conn->query("SET time_zone = '+00:00'");
        
        // Configure connection settings
        $conn->query("SET SESSION wait_timeout = 28800");  // 8 hours
        $conn->query("SET SESSION interactive_timeout = 28800");
    }
    
    return $conn;
}

// Test connection on include
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    die("Critical database error. Please try again later.");
}

// require_once 'session.php';
?>