<?php
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../database/db_operations.php';
header('Content-Type: application/json; charset=utf-8');
$roll = $_GET['roll'] ?? null;
if (!$roll) {
    echo json_encode(['success' => false, 'message' => 'Missing roll parameter']);
    exit;
}
$rows = getStudentRemarks($roll);
if ($rows === null) $rows = [];
echo json_encode(['success' => true, 'remarks' => $rows]);
