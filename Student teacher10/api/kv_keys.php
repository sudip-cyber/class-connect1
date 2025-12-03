<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

$prefix = isset($_GET['prefix']) ? $_GET['prefix'] : '';
$sql = 'SELECT kv_key FROM kv_store WHERE kv_key LIKE ? ORDER BY kv_key';
$like = $prefix . '%';
$rows = db_query($sql, 's', [$like]);
if ($rows === false) {
    echo json_encode(['ok' => false, 'error' => 'db_error']);
    exit;
}
$keys = array_map(function($r){ return $r['kv_key']; }, $rows);
echo json_encode(['ok' => true, 'keys' => $keys]);
?>
