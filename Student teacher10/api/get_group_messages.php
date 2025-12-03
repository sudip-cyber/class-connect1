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
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 200;

// Use DB helper to fetch group messages while excluding deletions for this viewer
try{
    $db = getDB();
    $sql = 'SELECT m.*, CASE WHEN m.from_type = ? AND m.from_id = ? THEN 1 ELSE 0 END as self_flag
            FROM messages m
            WHERE m.to_type = "group" AND m.to_id IN ("all","teachers")
            AND NOT EXISTS (SELECT 1 FROM message_deletions md WHERE md.message_id = m.message_id AND md.deleted_for_everyone = 1)
            AND NOT EXISTS (SELECT 1 FROM message_deletions md2 WHERE md2.message_id = m.message_id AND md2.deleted_by_type = ? AND md2.deleted_by_id = ?)
            ORDER BY m.created_at ASC
            LIMIT ?';
    $stmt = $db->prepare($sql);
    $stmt->execute([$myType, $myId, $myType, $myId, (int)$limit]);
    $rows = $stmt->fetchAll();
    // If no canonical group rows exist (migration not run), fall back to per-recipient messages
    if (empty($rows)) {
        $fallbackSql = 'SELECT m.*, CASE WHEN m.from_type = ? AND m.from_id = ? THEN 1 ELSE 0 END as self_flag
            FROM messages m
            WHERE (m.to_type = ? AND m.to_id = ?) OR (m.from_type = ? AND m.from_id = ?)
            AND NOT EXISTS (SELECT 1 FROM message_deletions md WHERE md.message_id = m.message_id AND md.deleted_for_everyone = 1)
            AND NOT EXISTS (SELECT 1 FROM message_deletions md2 WHERE md2.message_id = m.message_id AND md2.deleted_by_type = ? AND md2.deleted_by_id = ?)
            ORDER BY m.created_at ASC
            LIMIT ?';
        $fstmt = $db->prepare($fallbackSql);
        $fstmt->execute([$myType, $myId, $myType, $myId, $myType, $myId, $myType, $myId, (int)$limit]);
        $rows = $fstmt->fetchAll();
    }
    echo json_encode(['success' => true, 'messages' => $rows]);
}catch(Exception $e){
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
