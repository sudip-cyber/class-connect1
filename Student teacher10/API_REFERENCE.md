# API Reference - Database Functions

## Overview
All database operations are defined in `database/db_operations.php` and available globally after including the file.

```php
require_once __DIR__ . '/database/config.php';
require_once __DIR__ . '/database/db_operations.php';
```

---

## Student Operations

### registerStudent()
Register a new student in the system.

**Parameters:**
- `$name` (string) - Full name of student
- `$rollNumber` (string) - 10-digit roll number
- `$email` (string) - Email address
- `$phone` (string) - 10-digit phone number
- `$password` (string) - Password (minimum 6 characters)

**Returns:** Array with keys
- `success` (boolean) - True if registered
- `message` (string) - Result message
- `roll_number` (string) - Student's roll number (on success)

**Example:**
```php
$result = registerStudent("John Doe", "2363050001", "john@example.com", "9876543210", "password123");
if ($result['success']) {
    echo "Student registered: " . $result['roll_number'];
}
```

---

### verifyStudent()
Verify student login credentials.

**Parameters:**
- `$rollNumber` (string) - Roll number
- `$password` (string) - Password

**Returns:** Array with keys
- `success` (boolean) - True if credentials valid
- `student` (array) - Student data if successful
  - `roll_number`
  - `name`
  - `password` (hashed)
- `message` (string) - Error message if failed

**Example:**
```php
$result = verifyStudent("2363050001", "password123");
if ($result['success']) {
    $_SESSION['user_id'] = $result['student']['roll_number'];
}
```

---

### getStudent()
Get complete student information including profile.

**Parameters:**
- `$rollNumber` (string) - Roll number

**Returns:** Array with all student and profile data, or null if not found

**Keys in returned array:**
- Student table fields: `roll_number`, `name`, `email`, `phone`, `created_at`
- Profile table fields: `profile_id`, `date_of_birth`, `father_name`, `mother_name`, `sgpa_sem1`, `sgpa_sem2`, `sgpa_sem3`, `photo_url`

**Example:**
```php
$student = getStudent("2363050001");
echo $student['name'] . " - " . $student['sgpa_sem1'];
```

---

### createStudentProfile()
Create or update a student's profile.

**Parameters:**
- `$rollNumber` (string) - Roll number
- `$dob` (string) - Date of birth (YYYY-MM-DD)
- `$fatherName` (string) - Father's name
- `$motherName` (string) - Mother's name
- `$sgpa1` (float) - 1st semester SGPA
- `$sgpa2` (float) - 2nd semester SGPA
- `$sgpa3` (float) - 3rd semester SGPA
- `$photoURL` (string) - Photo data URL or image URL

**Returns:** Array with keys
- `success` (boolean) - True if saved
- `message` (string) - Result message

**Example:**
```php
$result = createStudentProfile(
    "2363050001",
    "2003-05-15",
    "Ram Kumar",
    "Priya Kumar",
    8.5,
    8.2,
    8.1,
    "data:image/jpeg;base64,..."
);
```

---

## Teacher Operations

### registerTeacher()
Register a new teacher in the system.

**Parameters:**
- `$name` (string) - Full name
- `$teacherId` (string) - Teacher ID (must be in valid list)
- `$email` (string) - Email address
- `$phone` (string) - Phone number
- `$password` (string) - Password

**Returns:** Array with success status and message

**Example:**
```php
$result = registerTeacher("Dr. Smith", "SU123", "smith@example.com", "9876543210", "password123");
```

---

### verifyTeacher()
Verify teacher login credentials.

**Parameters:**
- `$teacherId` (string) - Teacher ID
- `$password` (string) - Password

**Returns:** Array with success, teacher data, or error message

**Example:**
```php
$result = verifyTeacher("SU123", "password123");
if ($result['success']) {
    $_SESSION['user_id'] = $result['teacher']['teacher_id'];
}
```

---

### getTeacher()
Get complete teacher information including profile.

**Parameters:**
- `$teacherId` (string) - Teacher ID

**Returns:** Array with teacher and profile data

**Example:**
```php
$teacher = getTeacher("SU123");
echo $teacher['name'] . " - " . $teacher['institute'];
```

---

### createTeacherProfile()
Create or update teacher profile.

**Parameters:**
- `$teacherId` (string) - Teacher ID
- `$dob` (string) - Date of birth (YYYY-MM-DD)
- `$institute` (string) - Educational institute
- `$passingYear` (string) - Passing year (YYYY)
- `$photoURL` (string) - Photo URL

**Returns:** Array with success status and message

**Example:**
```php
$result = createTeacherProfile(
    "SU123",
    "1980-03-20",
    "Delhi University",
    "2005",
    "data:image/jpeg;base64,..."
);
```

---

## Attendance Operations

### saveAttendance()
Save or update attendance record.

**Parameters:**
- `$teacherId` (string) - Teacher's ID
- `$rollNumber` (string) - Student's roll number
- `$subject` (string) - Subject name
- `$attendanceDate` (string) - Date (YYYY-MM-DD)
- `$status` (string) - 'P' for present, 'A' for absent, '' for blank

**Returns:** Array with success status

**Example:**
```php
$result = saveAttendance("SU123", "2363050001", "Math", "2025-12-03", "P");
```

---

### getAttendanceBySubject()
Get all attendance records for a subject.

**Parameters:**
- `$teacherId` (string) - Teacher's ID
- `$subject` (string) - Subject name

**Returns:** Array of attendance records with student names

**Example:**
```php
$records = getAttendanceBySubject("SU123", "Math");
foreach ($records as $record) {
    echo $record['name'] . ": " . $record['status'];
}
```

---

### getStudentAttendance()
Get all attendance records for a student.

**Parameters:**
- `$rollNumber` (string) - Roll number

**Returns:** Array of attendance records sorted by date (newest first)

**Example:**
```php
$attendance = getStudentAttendance("2363050001");
foreach ($attendance as $record) {
    echo $record['subject'] . " (" . $record['attendance_date'] . "): " . $record['status'];
}
```

---

### saveSubject()
Add a subject for a teacher.

**Parameters:**
- `$teacherId` (string) - Teacher's ID
- `$subjectName` (string) - Subject name

**Returns:** Array with success status

**Example:**
```php
$result = saveSubject("SU123", "Mathematics");
```

---

### getTeacherSubjects()
Get all subjects for a teacher.

**Parameters:**
- `$teacherId` (string) - Teacher's ID

**Returns:** Array of subject names

**Example:**
```php
$subjects = getTeacherSubjects("SU123");
// Returns: ["Mathematics", "Physics", "Chemistry"]
```

---

### deleteSubjectAttendance()
Delete all attendance records for a subject.

**Parameters:**
- `$teacherId` (string) - Teacher's ID
- `$subject` (string) - Subject name

**Returns:** Array with success status

**Example:**
```php
$result = deleteSubjectAttendance("SU123", "Mathematics");
if ($result['success']) {
    echo "Attendance deleted";
}
```

---

## Notification Operations

### createNotification()
Create a new notification.

**Parameters:**
- `$teacherId` (string) - Teacher ID (nullable)
- `$studentId` (string) - Student roll number (nullable)
- `$title` (string) - Notification title
- `$message` (string) - Notification message
- `$type` (string, optional) - Notification type (default: 'general')

**Returns:** Array with success status

**Example:**
```php
$result = createNotification(
    "SU123",
    "2363050001",
    "Attendance Update",
    "Your attendance for Math class has been recorded",
    "attendance"
);
```

---

### getStudentNotifications()
Get all notifications for a student.

**Parameters:**
- `$studentId` (string) - Student roll number

**Returns:** Array of notifications sorted by date (newest first)

**Example:**
```php
$notifications = getStudentNotifications("2363050001");
foreach ($notifications as $notif) {
    echo $notif['title'] . ": " . $notif['message'];
}
```

---

### markNotificationRead()
Mark notification as read.

**Parameters:**
- `$notificationId` (int) - Notification ID

**Returns:** Boolean - true if updated

**Example:**
```php
$success = markNotificationRead(123);
```

---

## Admin Operations

### getAllStudents()
Get all students with their profiles.

**Parameters:** None

**Returns:** Array of all student records with profiles

**Example:**
```php
$students = getAllStudents();
echo "Total students: " . count($students);
foreach ($students as $student) {
    echo $student['name'] . " - " . $student['roll_number'];
}
```

---

### getAllTeachers()
Get all teachers with their profiles.

**Parameters:** None

**Returns:** Array of all teacher records with profiles

**Example:**
```php
$teachers = getAllTeachers();
foreach ($teachers as $teacher) {
    echo $teacher['name'] . " (" . $teacher['teacher_id'] . ")";
}
```

---

## Session Operations

### createSession()
Create a user session.

**Parameters:**
- `$userId` (string) - User ID
- `$userType` (string) - 'student' or 'teacher'
- `$ipAddress` (string, optional) - User's IP address
- `$userAgent` (string, optional) - Browser user agent

**Returns:** Array with success status and session token

**Example:**
```php
$result = createSession("2363050001", "student", $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
if ($result['success']) {
    echo "Session token: " . $result['session_token'];
}
```

---

### verifySessionToken()
Verify a session token.

**Parameters:**
- `$sessionToken` (string) - Session token

**Returns:** Array with session data if valid, null if invalid

**Example:**
```php
$session = verifySessionToken("abc123def456...");
if ($session) {
    echo "User: " . $session['user_id'];
} else {
    echo "Session expired";
}
```

---

## Audit Operations

### logAudit()
Log a user action.

**Parameters:**
- `$userId` (string) - User ID
- `$userType` (string) - 'student' or 'teacher'
- `$action` (string) - Action name (e.g., 'login', 'register', 'save_attendance')
- `$details` (string, optional) - Additional details

**Returns:** Boolean - true if logged

**Example:**
```php
logAudit("2363050001", "student", "login", "Logged in from IP 192.168.1.1");
logAudit("SU123", "teacher", "save_attendance", "Saved 30 attendance records for Math");
```

---

## Database Connection

### getDB()
Get the PDO database connection instance.

**Parameters:** None

**Returns:** PDO connection object

**Example:**
```php
$db = getDB();
$stmt = $db->prepare("SELECT * FROM students");
$stmt->execute();
$students = $stmt->fetchAll();
```

---

## Error Handling Pattern

Most functions return arrays with this pattern:

```php
[
    'success' => true/false,
    'message' => 'Description of what happened',
    'data' => [...] // Only if applicable
]
```

**Consistent Error Handling:**
```php
$result = registerStudent(...);
if ($result['success']) {
    // Success - redirect or show message
    header('Location: success.php');
} else {
    // Error - show message
    echo "Error: " . $result['message'];
}
```

---

## Database Schema Reference

For raw SQL queries, the available tables are:

- **students** (roll_number, name, email, phone, password, created_at)
- **teachers** (teacher_id, name, email, phone, password, created_at)
- **student_profiles** (profile_id, roll_number, date_of_birth, father_name, mother_name, sgpa_sem1, sgpa_sem2, sgpa_sem3, photo_url, updated_at)
- **teacher_profiles** (profile_id, teacher_id, date_of_birth, institute, passing_year, photo_url, updated_at)
- **attendance** (attendance_id, teacher_id, roll_number, subject, attendance_date, status, created_at, updated_at)
- **subjects** (subject_id, teacher_id, subject_name, created_at)
- **notifications** (notification_id, teacher_id, student_id, title, message, notification_type, is_read, created_at)
- **sessions** (session_id, user_id, user_type, session_token, login_time, last_activity, expires_at, ip_address, user_agent)
- **audit_logs** (log_id, user_id, user_type, action, details, created_at)

---

**Last Updated:** December 2025  
**PHP Version Required:** 7.4+  
**Database:** MySQL 5.7+

