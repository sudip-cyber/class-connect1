<?php
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../database/db_operations.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

// Only teacher or admin allowed
if (empty($_SESSION['user_type']) || ($_SESSION['user_type'] !== 'teacher' && $_SESSION['user_type'] !== 'admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$roll = trim($input['roll'] ?? '');
if ($roll === '') {
    echo json_encode(['success' => false, 'message' => 'Missing roll']);
    exit;
}

$res = deleteStudent($roll);
if (!empty($res) && !empty($res['success'])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $res['message'] ?? 'Delete failed']);
}
exit;
