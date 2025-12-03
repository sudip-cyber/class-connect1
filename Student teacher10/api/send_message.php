<?php
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../database/db_operations.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}
$fromType = $_SESSION['user_type'];
$fromId = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) $input = [];
$toType = $input['to_type'] ?? null;
$toId = $input['to_id'] ?? null;
$msg = trim($input['message'] ?? '');
$attachment = $input['attachment'] ?? null; // optional filename or indicator
$broadcast = $input['broadcast'] ?? null; // optional: 'teachers' | 'all' | null

if ($msg === '' && !$attachment) {
    echo json_encode(['success' => false, 'message' => 'Missing message or attachment']);
    exit;
}

// handle broadcast requests
if ($broadcast === 'teachers') {
    $teachers = getAllTeachers();
    $ok = true; $lastId = null; $created = null;
    foreach ($teachers as $t) {
        $tid = $t['teacher_id'] ?? $t['teacherId'] ?? null;
        if (!$tid) continue;
        $r = addMessage($fromType, $fromId, 'teacher', $tid, $msg, $attachment);
        if (!($r['success'] ?? false)) $ok = false;
        $lastId = $r['message_id'] ?? $lastId; $created = $r['created_at'] ?? $created;
    }
    // also insert a single group marker for teachers (optional)
    // Defensive: if DB schema still restricts to_type (ENUM), this insert may warn/fail.
    // Ignore canonical group insert failure so broadcasts still deliver per-recipient.
    $g = addMessage($fromType, $fromId, 'group', 'teachers', $msg, $attachment);
    if (!($g['success'] ?? false)) {
        // Non-fatal: log to PHP error log for later diagnosis but don't fail the whole request
        error_log('send_message: canonical group insert failed: ' . ($g['message'] ?? 'unknown'));
    }
    echo json_encode(['success' => $ok, 'message_id' => $lastId, 'created_at' => $created]);
    exit;
}

if ($broadcast === 'all') {
    $ok = true; $lastId = null; $created = null;
    $teachers = getAllTeachers();
    foreach ($teachers as $t) {
        $tid = $t['teacher_id'] ?? $t['teacherId'] ?? null;
        if (!$tid) continue;
        $r = addMessage($fromType, $fromId, 'teacher', $tid, $msg, $attachment);
        if (!($r['success'] ?? false)) $ok = false;
        $lastId = $r['message_id'] ?? $lastId; $created = $r['created_at'] ?? $created;
    }
    $students = getAllStudents();
    foreach ($students as $s) {
        $sid = $s['roll_number'] ?? $s['roll_no'] ?? null;
        if (!$sid) continue;
        $r = addMessage($fromType, $fromId, 'student', $sid, $msg, $attachment);
        if (!($r['success'] ?? false)) $ok = false;
        $lastId = $r['message_id'] ?? $lastId; $created = $r['created_at'] ?? $created;
    }
    // insert a single canonical group message for 'all' so clients can render one stream
    // Defensive: ignore failure if DB doesn't accept 'group' yet (run migration scripts to fix)
    $g = addMessage($fromType, $fromId, 'group', 'all', $msg, $attachment);
    if (!($g['success'] ?? false)) {
        error_log('send_message: canonical group insert failed (all): ' . ($g['message'] ?? 'unknown'));
    }
    echo json_encode(['success' => $ok, 'message_id' => $lastId, 'created_at' => $created]);
    exit;
}

// If client sent a group message via to_type='group', treat it as a broadcast request
if ($toType === 'group') {
    // map to broadcast semantics based on to_id
    if ($toId === 'teachers') {
        // reuse broadcast=teachers logic
        $teachers = getAllTeachers();
        $ok = true; $lastId = null; $created = null;
        foreach ($teachers as $t) {
            $tid = $t['teacher_id'] ?? $t['teacherId'] ?? null;
            if (!$tid) continue;
            $r = addMessage($fromType, $fromId, 'teacher', $tid, $msg, $attachment);
            if (!($r['success'] ?? false)) $ok = false;
            $lastId = $r['message_id'] ?? $lastId; $created = $r['created_at'] ?? $created;
        }
        $g = addMessage($fromType, $fromId, 'group', 'teachers', $msg, $attachment);
        if (!($g['success'] ?? false)) {
            error_log('send_message: canonical group insert failed: ' . ($g['message'] ?? 'unknown'));
        }
        echo json_encode(['success' => $ok, 'message_id' => $lastId, 'created_at' => $created]);
        exit;
    } elseif ($toId === 'all') {
        // reuse broadcast=all logic
        $ok = true; $lastId = null; $created = null;
        $teachers = getAllTeachers();
        foreach ($teachers as $t) {
            $tid = $t['teacher_id'] ?? $t['teacherId'] ?? null;
            if (!$tid) continue;
            $r = addMessage($fromType, $fromId, 'teacher', $tid, $msg, $attachment);
            if (!($r['success'] ?? false)) $ok = false;
            $lastId = $r['message_id'] ?? $lastId; $created = $r['created_at'] ?? $created;
        }
        $students = getAllStudents();
        foreach ($students as $s) {
            $sid = $s['roll_number'] ?? $s['roll_no'] ?? null;
            if (!$sid) continue;
            $r = addMessage($fromType, $fromId, 'student', $sid, $msg, $attachment);
            if (!($r['success'] ?? false)) $ok = false;
            $lastId = $r['message_id'] ?? $lastId; $created = $r['created_at'] ?? $created;
        }
        $g = addMessage($fromType, $fromId, 'group', 'all', $msg, $attachment);
        if (!($g['success'] ?? false)) {
            error_log('send_message: canonical group insert failed (all): ' . ($g['message'] ?? 'unknown'));
        }
        echo json_encode(['success' => $ok, 'message_id' => $lastId, 'created_at' => $created]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Unknown group target']);
        exit;
    }
}

// normal single recipient flow
if (!$toType || !$toId) {
    echo json_encode(['success' => false, 'message' => 'Missing recipient']);
    exit;
}

$res = addMessage($fromType, $fromId, $toType, $toId, $msg, $attachment);
if (isset($res['success']) && $res['success']) {
    echo json_encode(['success' => true, 'message_id' => $res['message_id'], 'created_at' => $res['created_at']]);
} else {
    echo json_encode(['success' => false, 'message' => $res['message'] ?? 'Failed to save']);
}
