<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

$key = isset($_GET['key']) ? $_GET['key'] : null;
if (!$key) {
    echo json_encode(['ok' => false, 'error' => 'missing key']);
    exit;
}

$sql = 'SELECT kv_value FROM kv_store WHERE kv_key = ? LIMIT 1';
$rows = db_query($sql, 's', [$key]);
if ($rows === false) {
    echo json_encode(['ok' => false, 'error' => 'db_error']);
    exit;
}

if (count($rows) === 0) {
    echo json_encode(['ok' => true, 'found' => false, 'value' => null]);
} else {
    // return raw stored string so existing pages that call JSON.parse(localStorage.getItem(...)) continue to work
    echo json_encode(['ok' => true, 'found' => true, 'value' => $rows[0]['kv_value']]);
}

?>
