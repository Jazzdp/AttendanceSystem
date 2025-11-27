<?php

require_once 'config.php';

function connectDB() {
    try {
        // Create PDO connection using constants from config.php
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        
        $conn = new PDO(
            $dsn,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );

        return $conn;

    } catch (PDOException $e) {
        // Log error to file
        $logFile = __DIR__ . '/logs/db_errors.log';
        $timestamp = date('Y-m-d H:i:s');
        $errorMessage = "[{$timestamp}] Connection Failed: " . $e->getMessage() . "\n";

        // Create logs directory if it doesn't exist
        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents($logFile, $errorMessage, FILE_APPEND);

        // Return null on failure
        return null;
    }
}

?>
