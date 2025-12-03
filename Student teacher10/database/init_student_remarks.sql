-- Migration: create student_remarks table
CREATE TABLE IF NOT EXISTS student_remarks (
  remark_id INT AUTO_INCREMENT PRIMARY KEY,
  student_roll VARCHAR(64) NOT NULL,
  teacher_id VARCHAR(64) NOT NULL,
  remark TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_student_roll (student_roll),
  INDEX idx_teacher_id (teacher_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
