````markdown
# Student Teacher Management System - Complete Setup Guide

## Overview
This is a comprehensive Student-Teacher Management System that integrates HTML, CSS, JavaScript frontend with PHP backend and MySQL database for persistent data storage. No more localStorage - everything is saved to the database!

## Prerequisites
1. **XAMPP** (includes Apache, MySQL, and PHP)
   - Download from: https://www.apachefriends.org/download.html
   - Install with default options

2. **Code Editor** (VS Code, Sublime Text, etc.)

## Setup Steps

### Step 1: Start XAMPP Services
1. Open XAMPP Control Panel
2. Click "Start" for Apache service
3. Click "Start" for MySQL service
4. Wait for both to show as running (green)

### Step 2: Create the Database

#### Method A: Using phpMyAdmin (GUI)
1. Open your web browser and go to: http://localhost/phpmyadmin
2. In the left sidebar, click "New"
3. Enter database name: `student_teacher_db`
4. Click "Create"
5. Select the newly created database
6. Click the "Import" tab
7. Click "Choose File" and select: `database/schema.sql` from your project folder
8. Click "Go" to import

#### Method B: Using MySQL Command Line
```bash
mysql -u root -p
CREATE DATABASE student_teacher_db;
USE student_teacher_db;
SOURCE C:\path\to\Student teacher10\database\schema.sql;
```

### Step 3: Verify Database Connection
1. Check `database/config.php` file
2. Ensure these settings match your XAMPP configuration:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');       // Leave empty if no password
   define('DB_NAME', 'student_teacher_db');
   ```
3. Save if any changes were made

### Step 4: Deploy the Application

#### Option A: Copy to XAMPP htdocs
1. Copy the entire project folder to: `C:\xampp\htdocs\student-teacher`
2. Navigate to: http://localhost/student-teacher/index.php

#### Option B: Use Symbolic Link (Recommended for development)
```powershell
# Run PowerShell as Administrator
cd C:\xampp\htdocs
New-Item -ItemType SymbolicLink -Name "student-teacher" -Target "C:\wamp64\www\Student teacher10"
```

Then access: http://localhost/student-teacher/index.php

### Step 5: Access the Application
- Open your web browser
- Go to: http://localhost/student-teacher/index.php
- You should see the landing page with Login and Register buttons

## Default Login Credentials

### Pre-inserted Teachers:
- **ID:** SU123, **Password:** password123
- **ID:** AD124, **Password:** password123
- **ID:** RU125, **Password:** password123
- **ID:** DE126, **Password:** password123
- **ID:** SH127, **Password:** password123

### Register New Users:
You can register new students and teachers through the Register page. Students must use roll numbers between 2363050001-2363050040.

## File Structure

```
Student teacher10/
├── database/
│   ├── config.php           # Database configuration
│   ├── db_operations.php    # CRUD operations
│   └── schema.sql           # Database schema
├── index.php                # Landing page
├── log.php                  # Login (Student & Teacher)
├── generate.php             # Registration (Student & Teacher)
├── option.php               # Teacher options menu
├── attendance.php           # Attendance management (DB-backed)
├── sprofile.php             # Student profile creation
├── sdetails.php             # Student profile view
├── tprofile2.php            # Teacher profile creation
├── tdetails.php             # Teacher profile view
├── notify.php               # Notifications & schedule
├── sstore.php               # Student listing
├── tstore.html              # Teacher listing
├── admin.html               # Admin page
└── README.md                # This file
```

## Database Schema

### Tables Created:
1. **teachers** - Teacher registration and authentication
2. **students** - Student registration and authentication
3. **student_profiles** - Detailed student information (profile pic, academics, family)
4. **teacher_profiles** - Detailed teacher information (institute, qualifications)
5. **attendance** - Attendance records with dates and status
6. **subjects** - Subjects per teacher
7. **notifications** - System notifications for students
8. **sessions** - User session management
9. **exam_schedule** - Exam schedule information
10. **audit_logs** - Action logging for security

## Key Features

### For Students:
✅ Registration with roll number validation
✅ Secure login with password hashing
✅ Profile creation with photo upload
✅ View personal attendance records
✅ Receive notifications
✅ Access class schedule
✅ View academic progress (SGPA)

### For Teachers:
✅ Registration with authorized teacher IDs
✅ Secure login with password hashing
✅ Profile creation with qualifications
✅ Manage attendance by subject
✅ View student details
✅ Send notifications to students
✅ Multiple subject support
✅ Data export capability

### For Admin:
✅ View all users (students & teachers)
✅ System statistics
✅ User management

## PHP Conversions Made

### All Data Now Uses Database Instead of localStorage:

| Page | Old Storage | New Storage |
|------|-------------|-------------|
| Registration (generate.php) | localStorage | students/teachers table |
| Login (log.php) | localStorage | Database with password hashing |
| Student Profile (sprofile.php) | localStorage | student_profiles table |
| Student Details (sdetails.php) | localStorage | student_profiles table |
| Attendance (attendance.php) | localStorage | attendance table |
| Teacher Profile (tprofile2.php) | localStorage | teacher_profiles table |
| Teacher Details (tdetails.php) | localStorage | teacher_profiles table |

## Troubleshooting

### Issue: "Database Connection Error"
**Solution:**
1. Verify MySQL is running in XAMPP Control Panel
2. Check database credentials in `database/config.php`
3. Ensure `student_teacher_db` database exists
4. Verify schema was imported correctly

### Issue: "Page Not Found (404)"
**Solution:**
1. Verify Apache is running
2. Check file is in correct location
3. Ensure URL is correct: http://localhost/student-teacher/filename.php
4. Clear browser cache

### Issue: "Blank Page" or "Warning: Division by zero"
**Solution:**
1. Check PHP error logs in XAMPP/logs/php_error.log
2. Enable error reporting in PHP
3. Verify all required PHP extensions are enabled
4. Check database connection

### Issue: "Session timeout"
**Solution:**
- Session timeout is set to 1 hour (3600 seconds)
- Modify in `database/config.php` if needed
- Change `define('SESSION_TIMEOUT', 3600);`

### Issue: "Login fails with correct credentials"
**Solution:**
1. Ensure password was hashed correctly during registration
2. Check that user_type (student/teacher) matches in login
3. Verify database has the user record
4. Clear browser cookies/cache

## Security Features Implemented

✅ **Password Hashing** - Using PHP's password_hash() function
✅ **Session Management** - Server-side sessions instead of localStorage
✅ **Input Validation** - All inputs validated and sanitized
✅ **SQL Injection Prevention** - Using prepared statements
✅ **XSS Prevention** - Using htmlspecialchars() for output
✅ **CSRF Protection** - Session-based validation
✅ **Audit Logging** - All user actions logged

## API Endpoints (AJAX)

### Attendance API
```javascript
POST attendance.php
- action: 'save_attendance' - Save attendance records
- action: 'load_attendance' - Load attendance for subject
- action: 'get_subjects' - Get teacher's subjects
- action: 'delete_subject' - Delete subject attendance
```

## Common Tasks

### To Register a New Student:
1. Click "Register" on home page
2. Select "Student" role
3. Enter roll number (2363050001-2363050040)
4. Enter details
5. Click "Submit"
6. Login with your roll number

### To Add Attendance:
1. Login as teacher
2. Click "Manage Attendance"
3. Enter subject name
4. Add date columns
5. Select attendance status (P/A)
6. Click "Save to Database"

### To View Student Profile:
1. Login as student
2. Go to "View My Profile"
3. View attendance records
4. See academic progress

### To Check Notifications:
1. Click "Notifications" menu
2. View class schedule
3. See next upcoming class
4. Enable browser notifications (optional)

## Maintenance

### To Backup Database:
```bash
mysqldump -u root -p student_teacher_db > backup.sql
```

### To Restore Database:
```bash
mysql -u root -p student_teacher_db < backup.sql
```

### To Clear All Data (Keep Tables):
```sql
TRUNCATE TABLE attendance;
TRUNCATE TABLE notifications;
TRUNCATE TABLE subjects;
```

## Performance Tips

1. **Index Optimization** - Database is pre-indexed for common queries
2. **Session Cleanup** - Sessions expire after 1 hour
3. **Query Optimization** - Prepared statements prevent slowdowns
4. **File Management** - Photo URLs stored in database (not files)

## Support & Documentation

### For Issues:
1. Check error logs at: `XAMPP/apache/logs/error.log`
2. Check PHP logs at: `XAMPP/php/logs/php_error.log`
3. Enable DEBUG_MODE in config.php for detailed errors

### For Customization:
1. Modify database schema in `database/schema.sql`
2. Add new tables/fields as needed
3. Create new PHP files following the same pattern
4. Update `database/db_operations.php` with new functions

## Technical Stack

- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Server:** Apache (via XAMPP)
- **Security:** Password hashing, prepared statements, sessions

## Version History

- **v2.0** (Current) - PHP + MySQL Database Integration
- **v1.0** - HTML/CSS/JavaScript with localStorage

---

**Last Updated:** December 2025
**Created for:** Educational Management System
````
