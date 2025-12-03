<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['key'])) {
    echo json_encode(['ok' => false, 'error' => 'missing payload']);
    exit;
}
$key = $data['key'];

$sql = 'DELETE FROM kv_store WHERE kv_key = ?';
$res = db_query($sql, 's', [$key]);
if ($res === false) {
    echo json_encode(['ok' => false, 'error' => 'db_error']);
} else {
    echo json_encode(['ok' => true]);
}
?>
