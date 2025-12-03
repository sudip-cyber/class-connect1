# Student Teacher Management System - Setup Guide

## Prerequisites
1. Install XAMPP (includes Apache, MySQL, and PHP)
   - Download from: https://www.apachefriends.org/download.html
   - Install with default options

## Setup Steps

1. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

2. **Set up the Database**
   - Open your web browser and go to: http://localhost/phpmyadmin
   - Create a new database:
     - Click "New" in the left sidebar
     - Enter database name: `student_teacher_db`
     - Click "Create"
   - Import the schema:
     - Select the newly created database
     - Click "Import" in the top menu
     - Choose the file: `database/schema.sql`
     - Click "Go" to import

3. **Configure Database Connection**
   - Open `database/config.php`
   - Update the following values:
     ```php
     define('DB_USER', 'root');  // Your MySQL username
     define('DB_PASS', '');      // Your MySQL password
     ```

4. **Deploy the Application**
   - Copy the entire project folder to:
     - Windows: `C:\xampp\htdocs\student-teacher`
   - Or create a symbolic link:
     ```
     mklink /D "C:\xampp\htdocs\student-teacher" "PATH_TO_YOUR_PROJECT"
     ```

5. **Access the Application**
   - Open your web browser
   - Go to: http://localhost/student-teacher
   - You should see the login page

## Default Login Credentials

### Teachers:
- ID: T001, Name: Dr. Vinod Kumar
- ID: T002, Name: Prof. Deepak Tomar
- ID: T003, Name: Dr. Rajesh Singh

## Features
- Student Attendance Management
- Class Schedule
- Notifications
- Student and Teacher Profiles
- Performance Tracking

## File Structure
```
├── database/
│   ├── config.php      # Database configuration
│   ├── db_operations.php # Database operations
│   └── schema.sql      # Database schema
├── *.html              # Application pages
└── README.md          # This file
```

## Troubleshooting

1. **Database Connection Issues**
   - Verify MySQL is running in XAMPP Control Panel
   - Check database credentials in config.php
   - Ensure database exists and is properly imported

2. **Page Not Found**
   - Verify Apache is running in XAMPP Control Panel
   - Check file permissions
   - Ensure files are in correct location

3. **Blank Page**
   - Check PHP error logs in XAMPP/logs
   - Enable error reporting in PHP
   - Verify all required PHP extensions are enabled