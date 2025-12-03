<?php
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../database/db_operations.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}
$actorType = $_SESSION['user_type'];
$actorId = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) $input = [];
$messageId = isset($input['message_id']) ? (int)$input['message_id'] : 0;
$action = $input['action'] ?? null; // 'me' or 'everyone'

if (!$messageId || !in_array($action, ['me','everyone'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

if ($action === 'me') {
    $r = markMessageDeletedForUser($messageId, $actorType, $actorId);
    echo json_encode($r);
    exit;
} else {
    // delete for everyone - only sender allowed
    $r = deleteMessageForEveryone($messageId, $actorType, $actorId);
    echo json_encode($r);
    exit;
}
