<?php
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../database/db_operations.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}
$myType = $_SESSION['user_type'];
$myId = $_SESSION['user_id'];

$otherType = $_GET['other_type'] ?? null;
$otherId = $_GET['other_id'] ?? null;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 200;

if (!$otherType || !$otherId) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$rows = getConversationMessages($myType, $myId, $otherType, $otherId, $limit);

echo json_encode(['success' => true, 'messages' => $rows]);
