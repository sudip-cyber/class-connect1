<?php
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../database/db_operations.php';

$roll = $argv[1] ?? '2363050022';
$rows = getStudentRemarks($roll);
echo "DEBUG getStudentRemarks for roll={$roll}\n";
echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n";

?>
