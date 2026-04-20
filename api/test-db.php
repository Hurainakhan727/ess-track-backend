<?php
// Database Connectivity Test
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();

    if ($conn) {
        // Test query to check if table exists
        $result = $conn->query("SHOW TABLES LIKE 'inquiries'");
        $tableExists = $result->num_rows > 0;

        echo json_encode([
            'status' => 'connected',
            'message' => 'Database connection successful',
            'host' => $conn->host_info,
            'database' => 'ess-track-backend-db',
            'table_inquiries_exists' => $tableExists,
            'charset' => $conn->character_set_name()
        ]);
        $conn->close();
    } else {
        echo json_encode([
            'status' => 'failed',
            'message' => 'Database connection failed'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Exception: ' . $e->getMessage()
    ]);
}
?>