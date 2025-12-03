<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../database/db_operations.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$to_type = $payload['to_type'] ?? '';
$to_id = $payload['to_id'] ?? '';
$message = $payload['message'] ?? '';
$attachment = $payload['attachment'] ?? null;

// Determine sender from session if available
session_start();
$from_type = $_SESSION['user_type'] ?? 'guest';
$from_id = $_SESSION['user_id'] ?? '0';

if (!$to_type) {
    echo json_encode(['success' => false, 'message' => 'Missing to_type']);
    exit;
}
if (!$to_id) {
    echo json_encode(['success' => false, 'message' => 'Missing to_id']);
    exit;
}

try {
    // Use addMessage from db_operations (which uses messaging DB)
    $res = addMessage($from_type, $from_id, $to_type, $to_id, $message, $attachment);
    if ($res && isset($res['success']) && $res['success']) {
        echo json_encode(['success' => true, 'message_id' => $res['message_id'] ?? null]);
        exit;
    }
    echo json_encode(['success' => false, 'message' => $res['message'] ?? 'Failed to save message']);
    exit;
} catch (Exception $e) {
    // Log server error for diagnostics
    @file_put_contents(__DIR__ . '/../logs/api_errors.log', '[' . date('c') . '] send_message error: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}
