<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

$sql = 'SELECT kv_key, kv_value FROM kv_store ORDER BY kv_key';
$rows = db_query($sql);
if ($rows === false) {
    echo json_encode(['ok' => false, 'error' => 'db_error']);
    exit;
}

$out = [];
foreach ($rows as $r) {
    $out[$r['kv_key']] = $r['kv_value'];
}

echo json_encode(['ok' => true, 'data' => $out]);
?>
