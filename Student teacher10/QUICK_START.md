# Quick Start Guide - 5 Minutes to Setup

## ⚡ Super Quick Setup

### Step 1: Start Services (1 minute)
```
1. Open XAMPP Control Panel
2. Click "Start" next to Apache
3. Click "Start" next to MySQL
```

### Step 2: Create Database (2 minutes)
```
1. Open http://localhost/phpmyadmin
2. Click "New" → Type "student_teacher_db" → Click "Create"
3. Click the new database
4. Click "Import" tab
5. Choose File → Select: database/schema.sql
6. Click "Go"
✓ Database created with all tables!
```

### Step 3: Configure (30 seconds)
```
1. Open: database/config.php
2. Check these values:
   - DB_HOST = 'localhost'
   - DB_USER = 'root'
   - DB_PASS = '' (empty)
   - DB_NAME = 'student_teacher_db'
3. Save (usually no changes needed)
```

### Step 4: Deploy (30 seconds)
```
Copy entire folder to: C:\xampp\htdocs\student-teacher
OR create symbolic link using PowerShell (as admin):
  New-Item -ItemType SymbolicLink -Name "student-teacher" -Target "C:\wamp64\www\Student teacher10" -Path "C:\xampp\htdocs"
```

### Step 5: Launch (30 seconds)
```
Open: http://localhost/student-teacher/index.php
✓ You're done!
```

---

## 🔓 Test with Pre-made Accounts

### Teacher Login:
- **ID:** SU123
- **Password:** password123

### Student Registration:
1. Click "Register"
2. Select "Student"
3. Roll: 2363050001 (must be 2363050001-2363050040)
4. Email: test@test.com
5. Phone: 9876543210
6. Password: test123
7. Submit

---

## 📍 Where Things Are

| Function | File | URL |
|----------|------|-----|
| Home Page | index.php | /index.php |
| Login | log.php | /log.php |
| Register | generate.php | /generate.php |
| Attendance | attendance.php | /attendance.php |
| Admin | admin.php | /admin.php |
| Database Config | database/config.php | (not accessible via web) |
| Database Schema | database/schema.sql | (not accessible via web) |

---

## 🎯 Test Attendance (Quick Demo)

1. **Login as teacher:** SU123 / password123
2. **Go to:** Manage Attendance
3. **Enter subject:** "Math"
4. **Click:** Add Date → Select today
5. **Enter:** Student name and roll
6. **Select:** P (Present) or A (Absent)
7. **Click:** Save to Database ✓

---

## 🔒 Admin Panel

- **URL:** http://localhost/student-teacher/admin.php
- **Password:** admin123
- **Info:** View stats, students, teachers

---

## 📊 Database Tables

```
students          → All registered students
teachers          → All registered teachers
student_profiles  → Student detailed info
teacher_profiles  → Teacher detailed info
attendance        → All attendance records
subjects          → Subjects per teacher
notifications     → System notifications
sessions          → User sessions
audit_logs        → Who did what when
```

---

## 🚨 If Something Goes Wrong

### Database not connecting?
- Open XAMPP → MySQL should be green
- In phpmyadmin, check student_teacher_db exists
- In config.php, verify DB credentials

### Page blank?
- Check browser console (F12 → Console tab)
- Check XAMPP logs folder
- Verify Apache is running

### Can't login?
- Verify you registered first
- Check you're using correct ID and password
- Try "SU123" / "password123" (test teacher)
- Clear browser cookies

### Attendance not saving?
- Make sure you're logged in as teacher
- Make sure you entered a subject
- Check browser console for errors
- Try in Firefox instead

---

## 📱 Available User Types

### Teachers (Pre-made):
- SU123, AD124, RU125, DE126, SH127
- All use: password123

### Students (You Create):
- Roll: 2363050001-2363050040
- Email: any
- Phone: 10 digits
- Password: your choice

---

## 🎓 What Works Now

✅ Register students/teachers  
✅ Secure login with passwords  
✅ Create student & teacher profiles  
✅ Manage attendance by subject  
✅ View profile & attendance history  
✅ Admin dashboard  
✅ All data saved to MySQL database  

---

## 📚 Full Documentation

For detailed setup, troubleshooting, and advanced features:
- **See: README_PHP_DB.md**
- **See: IMPLEMENTATION_SUMMARY.md**

---

## 🔄 If You Need to Reset Everything

```bash
# Stop XAMPP
# Delete database in phpmyadmin
# Re-import database/schema.sql
# Clear browser cookies
# Refresh page
```

---

**That's it! You're ready to use the system.** 🚀

