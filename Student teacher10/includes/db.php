<?php
// Simple mysqli connection helper for WAMP
// Copy this file to any PHP page and call `require_once __DIR__.'/db.php';` then use `$db`.

$db_host = '127.0.0.1';
$db_user = 'root';
$db_pass = 'SU16@Noone'; // set your MySQL root password if any
$db_name = 'student_teacher_db';

$db = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($db->connect_errno) {
    error_log('DB connect error: ' . $db->connect_error);
    // In production, avoid echoing DB errors. For development you can uncomment next line:
    // die('Database connection failed: ' . $db->connect_error);
}

// Helper to safely prepare and execute statements
function db_query($sql, $types = null, $params = null) {
    global $db;
    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        error_log('DB prepare error: ' . $db->error . ' SQL: ' . $sql);
        return false;
    }
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        error_log('DB execute error: ' . $stmt->error . ' SQL: ' . $sql);
        $stmt->close();
        return false;
    }
    $res = $stmt->get_result();
    if ($res === false) {
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

?>
