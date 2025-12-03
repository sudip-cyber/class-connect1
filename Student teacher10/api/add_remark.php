<?php
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../database/db_operations.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

// Only teachers allowed
if (empty($_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher' || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$studentRoll = trim($input['student_roll'] ?? '');
$remark = trim($input['remark'] ?? '');
$teacherId = $_SESSION['user_id'];

if ($studentRoll === '' || $remark === '') {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$res = addStudentRemark($teacherId, $studentRoll, $remark);
if (!empty($res) && !empty($res['success'])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $res['message'] ?? 'Failed to add remark']);
}

exit;
