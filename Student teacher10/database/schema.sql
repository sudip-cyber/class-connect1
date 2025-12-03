-- Student Teacher Management System Database Schema

-- Create Teachers Table
CREATE TABLE IF NOT EXISTS teachers (
    teacher_id VARCHAR(10) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Students Table
CREATE TABLE IF NOT EXISTS students (
    roll_number VARCHAR(10) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Student Profiles Table
CREATE TABLE IF NOT EXISTS student_profiles (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,
    roll_number VARCHAR(10) NOT NULL UNIQUE,
    date_of_birth DATE,
    father_name VARCHAR(100),
    mother_name VARCHAR(100),
    sgpa_sem1 DECIMAL(5, 2),
    sgpa_sem2 DECIMAL(5, 2),
    sgpa_sem3 DECIMAL(5, 2),
    profile_picture LONGBLOB,
    photo_url LONGTEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (roll_number) REFERENCES students(roll_number) ON DELETE CASCADE
);

-- Create Teacher Profiles Table
CREATE TABLE IF NOT EXISTS teacher_profiles (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id VARCHAR(10) NOT NULL UNIQUE,
    date_of_birth DATE,
    institute VARCHAR(150),
    passing_year YEAR,
    profile_picture LONGBLOB,
    photo_url LONGTEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE
);

-- Create Attendance Table
CREATE TABLE IF NOT EXISTS attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id VARCHAR(10) NOT NULL,
    roll_number VARCHAR(10) NOT NULL,
    subject VARCHAR(100),
    attendance_date DATE,
    status ENUM('P', 'A', '') DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE,
    FOREIGN KEY (roll_number) REFERENCES students(roll_number) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (teacher_id, roll_number, subject, attendance_date)
);

-- Create Subject Table
CREATE TABLE IF NOT EXISTS subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id VARCHAR(10) NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE,
    UNIQUE KEY unique_teacher_subject (teacher_id, subject_name)
);

-- Create Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id VARCHAR(10),
    student_id VARCHAR(10),
    title VARCHAR(200),
    message LONGTEXT,
    notification_type VARCHAR(50),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE SET NULL,
    FOREIGN KEY (student_id) REFERENCES students(roll_number) ON DELETE SET NULL
);

-- Create Exam Schedule Table
CREATE TABLE IF NOT EXISTS exam_schedule (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(100) NOT NULL,
    exam_date DATE NOT NULL,
    exam_time TIME NOT NULL,
    exam_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Sessions Table for user login sessions
CREATE TABLE IF NOT EXISTS sessions (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(20) NOT NULL,
    user_type ENUM('student', 'teacher') NOT NULL,
    session_token VARCHAR(255) UNIQUE,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255)
);

-- Create Audit Log Table
CREATE TABLE IF NOT EXISTS audit_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(20),
    user_type ENUM('student', 'teacher'),
    action VARCHAR(100),
    details LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Valid Teacher IDs Table (for validation only)
CREATE TABLE IF NOT EXISTS valid_teacher_ids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id VARCHAR(10) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert valid teacher IDs (pre-defined list)
INSERT IGNORE INTO valid_teacher_ids (teacher_id) VALUES
('SU123'),
('AD124'),
('RU125'),
('DE126'),
('SH127');
