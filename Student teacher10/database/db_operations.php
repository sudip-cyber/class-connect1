<?php
/**
 * Database Operations File
 * This file contains all CRUD operations for the application
 */

require_once __DIR__ . '/config.php';

// ==================== STUDENT OPERATIONS ====================

/**
 * Register a new student
 */
function registerStudent($name, $rollNumber, $email, $phone, $password) {
    try {
        $db = getDB();
        
        // Check if student already exists
        $stmt = $db->prepare('SELECT roll_number FROM students WHERE roll_number = ?');
        $stmt->execute([$rollNumber]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Student with this roll number already exists'];
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_HASH_ALGO, PASSWORD_HASH_OPTIONS);
        
        // Insert student
        $stmt = $db->prepare('INSERT INTO students (roll_number, name, email, phone, password) VALUES (?, ?, ?, ?, ?)');
        $result = $stmt->execute([$rollNumber, $name, $email, $phone, $hashedPassword]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Student registered successfully', 'roll_number' => $rollNumber];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Verify student login
 */
function verifyStudent($rollNumber, $password) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare('SELECT roll_number, name, password FROM students WHERE roll_number = ?');
        $stmt->execute([$rollNumber]);
        $student = $stmt->fetch();
        
        if ($student && password_verify($password, $student['password'])) {
            return ['success' => true, 'student' => $student];
        } else {
            return ['success' => false, 'message' => 'Invalid roll number or password'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Get student details
 */
function getStudent($rollNumber) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare('SELECT s.*, sp.* FROM students s LEFT JOIN student_profiles sp ON s.roll_number = sp.roll_number WHERE s.roll_number = ?');
        $stmt->execute([$rollNumber]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Create/Update student profile
 */
function createStudentProfile($rollNumber, $dob, $fatherName, $motherName, $sgpa1, $sgpa2, $sgpa3, $photoURL) {
    try {
        $db = getDB();
        
        // Check if profile exists
        $stmt = $db->prepare('SELECT profile_id FROM student_profiles WHERE roll_number = ?');
        $stmt->execute([$rollNumber]);
        $exists = $stmt->fetch();
        
        if ($exists) {
            // Update existing profile
            $stmt = $db->prepare('UPDATE student_profiles SET date_of_birth = ?, father_name = ?, mother_name = ?, sgpa_sem1 = ?, sgpa_sem2 = ?, sgpa_sem3 = ?, photo_url = ? WHERE roll_number = ?');
            $result = $stmt->execute([$dob, $fatherName, $motherName, $sgpa1, $sgpa2, $sgpa3, $photoURL, $rollNumber]);
        } else {
            // Insert new profile
            $stmt = $db->prepare('INSERT INTO student_profiles (roll_number, date_of_birth, father_name, mother_name, sgpa_sem1, sgpa_sem2, sgpa_sem3, photo_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $result = $stmt->execute([$rollNumber, $dob, $fatherName, $motherName, $sgpa1, $sgpa2, $sgpa3, $photoURL]);
        }
        
        if ($result) {
            return ['success' => true, 'message' => 'Profile saved successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to save profile'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// ==================== TEACHER OPERATIONS ====================

/**
 * Register a new teacher
 */
function registerTeacher($name, $teacherId, $email, $phone, $password) {
    try {
        $db = getDB();
        
        // Check if valid teacher ID exists
        $stmt = $db->prepare('SELECT teacher_id FROM valid_teacher_ids WHERE teacher_id = ?');
        $stmt->execute([$teacherId]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Invalid Teacher ID. Valid IDs: SU123, AD124, RU125, DE126, SH127'];
        }
        
        // Check if teacher already registered
        $stmt = $db->prepare('SELECT teacher_id FROM teachers WHERE teacher_id = ?');
        $stmt->execute([$teacherId]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Teacher already registered'];
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_HASH_ALGO, PASSWORD_HASH_OPTIONS);
        
        // Insert new teacher with full details
        $stmt = $db->prepare('INSERT INTO teachers (teacher_id, name, email, phone, password) VALUES (?, ?, ?, ?, ?)');
        $result = $stmt->execute([$teacherId, $name, $email, $phone, $hashedPassword]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Teacher registered successfully', 'teacher_id' => $teacherId];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Verify teacher login
 */
function verifyTeacher($teacherId, $password) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare('SELECT teacher_id, name, password FROM teachers WHERE teacher_id = ?');
        $stmt->execute([$teacherId]);
        $teacher = $stmt->fetch();
        
        if ($teacher && password_verify($password, $teacher['password'])) {
            return ['success' => true, 'teacher' => $teacher];
        } else {
            return ['success' => false, 'message' => 'Invalid teacher ID or password'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Get teacher details
 */
function getTeacher($teacherId) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare('SELECT t.*, tp.* FROM teachers t LEFT JOIN teacher_profiles tp ON t.teacher_id = tp.teacher_id WHERE t.teacher_id = ?');
        $stmt->execute([$teacherId]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Create/Update teacher profile
 */
function createTeacherProfile($teacherId, $dob, $institute, $passingYear, $photoURL) {
    try {
        $db = getDB();
        
        // Check if profile exists
        $stmt = $db->prepare('SELECT profile_id FROM teacher_profiles WHERE teacher_id = ?');
        $stmt->execute([$teacherId]);
        $exists = $stmt->fetch();
        
        if ($exists) {
            // Update existing profile
            $stmt = $db->prepare('UPDATE teacher_profiles SET date_of_birth = ?, institute = ?, passing_year = ?, photo_url = ? WHERE teacher_id = ?');
            $result = $stmt->execute([$dob, $institute, $passingYear, $photoURL, $teacherId]);
        } else {
            // Insert new profile
            $stmt = $db->prepare('INSERT INTO teacher_profiles (teacher_id, date_of_birth, institute, passing_year, photo_url) VALUES (?, ?, ?, ?, ?)');
            $result = $stmt->execute([$teacherId, $dob, $institute, $passingYear, $photoURL]);
        }
        
        if ($result) {
            return ['success' => true, 'message' => 'Profile saved successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to save profile'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// ==================== ATTENDANCE OPERATIONS ====================

/**
 * Save/Update attendance record
 */
function saveAttendance($teacherId, $rollNumber, $subject, $attendanceDate, $status) {
    try {
        $db = getDB();
        
        // Check if attendance already exists
        $stmt = $db->prepare('SELECT attendance_id FROM attendance WHERE teacher_id = ? AND roll_number = ? AND subject = ? AND attendance_date = ?');
        $stmt->execute([$teacherId, $rollNumber, $subject, $attendanceDate]);
        $exists = $stmt->fetch();
        
        if ($exists) {
            // Update existing attendance
            $stmt = $db->prepare('UPDATE attendance SET status = ? WHERE teacher_id = ? AND roll_number = ? AND subject = ? AND attendance_date = ?');
            $result = $stmt->execute([$status, $teacherId, $rollNumber, $subject, $attendanceDate]);
        } else {
            // Insert new attendance
            $stmt = $db->prepare('INSERT INTO attendance (teacher_id, roll_number, subject, attendance_date, status) VALUES (?, ?, ?, ?, ?)');
            $result = $stmt->execute([$teacherId, $rollNumber, $subject, $attendanceDate, $status]);
        }
        
        return ['success' => $result];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Get all attendance records for a teacher and subject
 */
function getAttendanceBySubject($teacherId, $subject) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare('
            SELECT a.*, s.name, s.roll_number 
            FROM attendance a
            LEFT JOIN students s ON a.roll_number = s.roll_number
            WHERE a.teacher_id = ? AND a.subject = ?
            ORDER BY a.attendance_date DESC, s.roll_number ASC
        ');
        $stmt->execute([$teacherId, $subject]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get attendance for a specific student
 */
function getStudentAttendance($rollNumber) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare('
            SELECT * FROM attendance
            WHERE roll_number = ?
            ORDER BY attendance_date DESC
        ');
        $stmt->execute([$rollNumber]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Save subject for teacher
 */
function saveSubject($teacherId, $subjectName) {
    try {
        $db = getDB();
        
        // Check if subject already exists for this teacher
        $stmt = $db->prepare('SELECT subject_id FROM subjects WHERE teacher_id = ? AND subject_name = ?');
        $stmt->execute([$teacherId, $subjectName]);
        if ($stmt->fetch()) {
            return ['success' => true, 'message' => 'Subject already exists'];
        }
        
        // Insert subject
        $stmt = $db->prepare('INSERT INTO subjects (teacher_id, subject_name) VALUES (?, ?)');
        $result = $stmt->execute([$teacherId, $subjectName]);
        
        return ['success' => $result];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Get all subjects for a teacher
 */
function getTeacherSubjects($teacherId) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare('SELECT subject_name FROM subjects WHERE teacher_id = ? ORDER BY subject_name ASC');
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Delete all attendance records for a subject
 */
function deleteSubjectAttendance($teacherId, $subject) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare('DELETE FROM attendance WHERE teacher_id = ? AND subject = ?');
        $result = $stmt->execute([$teacherId, $subject]);
        
        return ['success' => $result];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// ==================== NOTIFICATION OPERATIONS ====================

/**
 * Create notification
 */
function createNotification($teacherId, $studentId, $title, $message, $type = 'general') {
    try {
        $db = getDB();
        
        $stmt = $db->prepare('INSERT INTO notifications (teacher_id, student_id, title, message, notification_type) VALUES (?, ?, ?, ?, ?)');
        $result = $stmt->execute([$teacherId, $studentId, $title, $message, $type]);
        
        return ['success' => $result];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Get notifications for a student
 */
function getStudentNotifications($studentId) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare('SELECT * FROM notifications WHERE student_id = ? ORDER BY created_at DESC');
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Mark notification as read
 */
function markNotificationRead($notificationId) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare('UPDATE notifications SET is_read = TRUE WHERE notification_id = ?');
        return $stmt->execute([$notificationId]);
    } catch (Exception $e) {
        return false;
    }
}

// ==================== SESSION OPERATIONS ====================

/**
 * Create session
 */
function createSession($userId, $userType, $ipAddress = '', $userAgent = '') {
    try {
        $db = getDB();
        
        $sessionToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $db->prepare('INSERT INTO sessions (user_id, user_type, session_token, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, ?, ?)');
        $result = $stmt->execute([$userId, $userType, $sessionToken, $ipAddress, $userAgent, $expiresAt]);
        
        if ($result) {
            return ['success' => true, 'session_token' => $sessionToken];
        } else {
            return ['success' => false];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Verify session token
 */
function verifySessionToken($sessionToken) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare('SELECT * FROM sessions WHERE session_token = ? AND expires_at > NOW()');
        $stmt->execute([$sessionToken]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Log audit event
 */
function logAudit($userId, $userType, $action, $details = '') {
    try {
        $db = getDB();
        
        $stmt = $db->prepare('INSERT INTO audit_logs (user_id, user_type, action, details) VALUES (?, ?, ?, ?)');
        return $stmt->execute([$userId, $userType, $action, $details]);
    } catch (Exception $e) {
        return false;
    }
}

// ==================== ADMIN OPERATIONS ====================

/**
 * Get all students
 */
function getAllStudents() {
    try {
        $db = getDB();
        
        $stmt = $db->query('SELECT s.*, sp.* FROM students s LEFT JOIN student_profiles sp ON s.roll_number = sp.roll_number ORDER BY s.roll_number ASC');
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get all teachers
 */
function getAllTeachers() {
    try {
        $db = getDB();
        
        $stmt = $db->query('SELECT t.*, tp.* FROM teachers t LEFT JOIN teacher_profiles tp ON t.teacher_id = tp.teacher_id ORDER BY t.teacher_id ASC');
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Delete a student and related records (profiles, attendance, remarks)
 */
function deleteStudent($rollNumber) {
    try {
        $db = getDB();
        // delete attendance
        $stmt = $db->prepare('DELETE FROM attendance WHERE roll_number = ?');
        $stmt->execute([$rollNumber]);
        // delete remarks
        $stmt = $db->prepare('DELETE FROM student_remarks WHERE student_roll = ?');
        $stmt->execute([$rollNumber]);
        // delete profile
        $stmt = $db->prepare('DELETE FROM student_profiles WHERE roll_number = ?');
        $stmt->execute([$rollNumber]);
        // delete student
        $stmt = $db->prepare('DELETE FROM students WHERE roll_number = ?');
        $result = $stmt->execute([$rollNumber]);
        return ['success' => (bool)$result];
    } catch (Exception $e) {
        @file_put_contents(__DIR__ . '/../logs/api_errors.log', '[' . date('c') . '] addMessage error: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// ==================== REMARKS OPERATIONS ====================

/**
 * Add a remark for a student by a teacher
 */
function addStudentRemark($teacherId, $studentRoll, $remark) {
    try {
        $db = getDB();
        $stmt = $db->prepare('INSERT INTO student_remarks (student_roll, teacher_id, remark, created_at) VALUES (?, ?, ?, NOW())');
        $result = $stmt->execute([$studentRoll, $teacherId, $remark]);
        return ['success' => (bool)$result];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get all remarks for a student (most recent first)
 */
function getStudentRemarks($studentRoll) {
    try {
        $db = getDB();
        $stmt = $db->prepare('SELECT sr.*, t.name as teacher_name FROM student_remarks sr LEFT JOIN teachers t ON sr.teacher_id = t.teacher_id WHERE sr.student_roll = ? ORDER BY sr.created_at DESC');
        $stmt->execute([$studentRoll]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        @file_put_contents(__DIR__ . '/../logs/api_errors.log', '[' . date('c') . '] getConversationMessages error: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
        return [];
    }
}

/**
 * Get latest remark for a student
 */
function getLatestStudentRemark($studentRoll) {
    try {
        $db = getDB();
        $stmt = $db->prepare('SELECT sr.*, t.name as teacher_name FROM student_remarks sr LEFT JOIN teachers t ON sr.teacher_id = t.teacher_id WHERE sr.student_roll = ? ORDER BY sr.created_at DESC LIMIT 1');
        $stmt->execute([$studentRoll]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

// ==================== MESSAGING OPERATIONS ====================

/**
 * Add a message between users (student <-> teacher)
 */
function addMessage($fromType, $fromId, $toType, $toId, $message, $attachment = null) {
    try {
        $db = getMessageDB();
        $stmt = $db->prepare('INSERT INTO messages (from_type, from_id, to_type, to_id, message, attachment, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $result = $stmt->execute([$fromType, $fromId, $toType, $toId, $message, $attachment]);
        if ($result) {
            return ['success' => true, 'message_id' => $db->lastInsertId(), 'created_at' => date('Y-m-d H:i:s')];
        }
        return ['success' => false];
    } catch (Exception $e) {
        @file_put_contents(__DIR__ . '/../logs/api_errors.log', '[' . date('c') . '] markMessageDeletedForUser error: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Retrieve messages between two participants ordered ascending by created_at
 */
function getConversationMessages($typeA, $idA, $typeB, $idB, $limit = 200) {
    try {
        $db = getMessageDB();
        // Exclude messages that are deleted for everyone or deleted for this viewer
        $sql = 'SELECT m.*, CASE WHEN m.from_type = ? AND m.from_id = ? THEN 1 ELSE 0 END as self_flag
            FROM messages m
            WHERE ((m.from_type = ? AND m.from_id = ? AND m.to_type = ? AND m.to_id = ?) 
               OR (m.from_type = ? AND m.from_id = ? AND m.to_type = ? AND m.to_id = ?))
            AND NOT EXISTS (SELECT 1 FROM message_deletions md WHERE md.message_id = m.message_id AND md.deleted_for_everyone = 1)
            AND NOT EXISTS (SELECT 1 FROM message_deletions md2 WHERE md2.message_id = m.message_id AND md2.deleted_by_type = ? AND md2.deleted_by_id = ?)
            ORDER BY m.created_at ASC
            LIMIT ?';

        $stmt = $db->prepare($sql);
        // Parameter order must match the placeholders in $sql exactly.
        // Placeholders order:
        // 1) CASE WHEN m.from_type = ? AND m.from_id = ?
        // 2) first side of OR: m.from_type = ?, m.from_id = ?, m.to_type = ?, m.to_id = ?
        // 3) second side of OR: m.from_type = ?, m.from_id = ?, m.to_type = ?, m.to_id = ?
        // 4) deletion filter for this viewer: md2.deleted_by_type = ?, md2.deleted_by_id = ?
        // 5) LIMIT ?
        $params = [
            $typeA, $idA,
            $typeA, $idA, $typeB, $idB,
            $typeB, $idB, $typeA, $idA,
            $typeA, $idA,
            (int)$limit
        ];
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Mark a message as deleted for a specific user (hide for that user)
 */
function markMessageDeletedForUser($messageId, $userType, $userId) {
    try {
        $db = getMessageDB();
        $stmt = $db->prepare('INSERT INTO message_deletions (message_id, deleted_by_type, deleted_by_id, deleted_for_everyone, deleted_at) VALUES (?, ?, ?, 0, NOW()) ON DUPLICATE KEY UPDATE deleted_at = NOW()');
        $res = $stmt->execute([$messageId, $userType, $userId]);
        return ['success' => (bool)$res];
    } catch (Exception $e) {
        @file_put_contents(__DIR__ . '/../logs/api_errors.log', '[' . date('c') . '] deleteMessageForEveryone error: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Delete a message for everyone. Only the original sender may perform this.
 */
function deleteMessageForEveryone($messageId, $actorType, $actorId) {
    try {
        $db = getMessageDB();
        // verify actor is the sender
        $stmt = $db->prepare('SELECT from_type, from_id FROM messages WHERE message_id = ? LIMIT 1');
        $stmt->execute([$messageId]);
        $row = $stmt->fetch();
        if (!$row) return ['success' => false, 'message' => 'Message not found'];
        if (!($row['from_type'] === $actorType && strval($row['from_id']) === strval($actorId))) {
            return ['success' => false, 'message' => 'Not authorized to delete for everyone'];
        }
        // insert deletion marker for everyone (deleted_for_everyone=1)
        $stmt2 = $db->prepare('INSERT INTO message_deletions (message_id, deleted_by_type, deleted_by_id, deleted_for_everyone, deleted_at) VALUES (?, ?, ?, 1, NOW()) ON DUPLICATE KEY UPDATE deleted_for_everyone = 1, deleted_at = NOW()');
        $res = $stmt2->execute([$messageId, $actorType, $actorId]);
        return ['success' => (bool)$res];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
?>
