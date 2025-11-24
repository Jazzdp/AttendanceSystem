<?php

require_once 'config.php';

function connectDB() {
    try {
        // Create PDO connection object
        $conn = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
            DB_USERNAME,
            DB_PASSWORD
        );
        
        // Set error mode to throw exceptions
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        return $conn;
        
    } catch (PDOException $e) {
        // Log error to file
        $errorLog = 'logs/db_errors.log';
        $timestamp = date('Y-m-d H:i:s');
        $errorMessage = "[{$timestamp}] Connection Failed: " . $e->getMessage() . "\n";
        
        // Create logs directory if it doesn't exist
        if (!is_dir('logs')) {
            mkdir('logs', 0755, true);
        }
        
        file_put_contents($errorLog, $errorMessage, FILE_APPEND);
        
        // Return null or throw exception
        return null;
    }
}





?>