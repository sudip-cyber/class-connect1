<?php
/**
 * Create student_remarks table if missing. Safe to run multiple times.
 * Usage (browser): http://localhost/Student%20teacher10/scripts/create_student_remarks.php
 * Usage (CLI): php "c:\wamp64\www\Student teacher10\scripts\create_student_remarks.php"
 */
require_once __DIR__ . '/../database/config.php';

try {
    $db = getDB();
    $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS student_remarks (
  remark_id INT AUTO_INCREMENT PRIMARY KEY,
  student_roll VARCHAR(64) NOT NULL,
  teacher_id VARCHAR(64) NOT NULL,
  remark TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_student_roll (student_roll),
  INDEX idx_teacher_id (teacher_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
    $db->exec($sql);
    echo "SUCCESS: ensured table `student_remarks` exists.\n";
} catch (Exception $e) {
    http_response_code(500);
    echo "FAILED: " . $e->getMessage() . "\n";
}

// Quick verification: show count
try {
    $stmt = $db->query('SELECT COUNT(*) AS c FROM student_remarks');
    $row = $stmt->fetch();
    echo "Rows in student_remarks: " . ($row['c'] ?? '0') . "\n";
} catch (Exception $e) {
    echo "Verify failed: " . $e->getMessage() . "\n";
}
