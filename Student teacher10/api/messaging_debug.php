<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/config.php';

try {
    // Attempt to ensure/connect to messaging DB
    $pdo = ensureMessageDB();

    // Check tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(['success' => true, 'db' => MSG_DB_NAME, 'tables' => $tables]);
    exit;
} catch (Exception $e) {
    $err = ['success' => false, 'message' => 'Messaging DB error'];
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        $err['detail'] = $e->getMessage();
    }
    // Log error for server-side inspection
    @file_put_contents(__DIR__ . '/../logs/api_errors.log', '[' . date('c') . '] messaging_debug: ' . ($e->getMessage()) . PHP_EOL, FILE_APPEND);
    http_response_code(500);
    echo json_encode($err);
    exit;
}
