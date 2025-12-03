<?php
// Simple attachment upload endpoint
// Expects multipart/form-data with field 'file'

require_once __DIR__ . '/../database/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];
$maxSize = 25 * 1024 * 1024; // 25 MB limit
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'File too large (max 25MB)']);
    exit;
}

$allowed = [
    'jpg','jpeg','png','gif','pdf','doc','docx','xls','xlsx','csv','txt','zip','rar','ppt','pptx','mp4','mp3','ogg'
];
$origName = $file['name'];
$ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
if (!in_array($ext, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'File type not allowed']);
    exit;
}

$uploadsDir = __DIR__ . '/../uploads';
if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);

$basename = bin2hex(random_bytes(8)) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($origName));
$target = $uploadsDir . '/' . $basename;
if (!move_uploaded_file($file['tmp_name'], $target)) {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
    exit;
}

// Return a URL relative to web root
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$urlPath = rtrim($scriptDir, '/') . '/../uploads/' . $basename;
// Normalize URL (remove duplicate slashes)
$urlPath = preg_replace('#/+#','/', $urlPath);

echo json_encode(['success' => true, 'url' => $urlPath]);
exit;
