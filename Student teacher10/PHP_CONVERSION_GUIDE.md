# PHP Conversion Guide - Match HTML/JS Logic Exactly

## Core Principle
Each PHP file should replicate the **exact logic** of its corresponding HTML file, but store/retrieve data from the **database** instead of **localStorage**.

---

## File-by-File Conversion Mapping

### 1. sprofile.php ↔ sprofile.html

**HTML Logic:**
```javascript
// 1. Check if logged in via localStorage
if (!localStorage.getItem('currentUserId')) → redirect to log.html

// 2. Get current user from session
const userId = localStorage.getItem('currentUserId');

// 3. Validate roll number matches logged-in ID
if (userId !== rollNo) → error

// 4. Collect form data
const photoURL = document.getElementById('imagePreview').src; // Base64 image

// 5. Save to localStorage
localStorage.setItem('profiles', JSON.stringify(existingProfiles));
localStorage.setItem(`user_${rollNo}`, JSON.stringify(userData));

// 6. Redirect
window.location.href = 'sdetails.html';
```

**PHP Equivalent:**
```php
// 1. Check if logged in via SESSION
if (!isset($_SESSION['user_id'])) → redirect to log.php

// 2. Get current user
$userId = $_SESSION['user_id'];

// 3. Validate roll number matches
if ($userId !== $rollNo) → error

// 4. Collect POST data (same as HTML form fields)

// 5. Save to database
createStudentProfile($rollNo, $dob, $fatherName, $motherName, $sgpa1, $sgpa2, $sgpa3, $photoURL);

// 6. Redirect
header('Location: sdetails.php');
```

**Key Updates Needed:**
- ✅ Add AJAX form submission (fetch() API)
- ✅ Show message boxes (success/error) instead of alert()
- ✅ Return JSON response for AJAX
- ✅ Image preview using FileReader
- ✅ All validation rules from HTML

---

### 2. sdetails.php ↔ sdetails.html

**HTML Logic:**
```javascript
// 1. Get student data from localStorage
const userData = JSON.parse(localStorage.getItem(`user_${currentUserId}`));

// 2. Get attendance records from localStorage
const attendanceRecords = JSON.parse(localStorage.getItem('attendanceTableData_...'));

// 3. Display profile card with photo, name, details
// 4. Display attendance table
// 5. Add navigation buttons (Edit Profile, View Orders, Teachers, Logout)
```

**PHP Equivalent:**
```php
// 1. Get student data from database
$student = getStudent($rollNumber);  // Joins with student_profiles

// 2. Get attendance records from database
$attendance = getStudentAttendance($rollNumber);

// 3. Display same card layout
// 4. Display attendance table from DB
// 5. Add same buttons
```

**Key Updates Needed:**
- ✅ Fetch profile with LEFT JOIN to get profile data
- ✅ Fetch attendance with proper joins
- ✅ Display profile photo from database (from photo_url field)
- ✅ Show all 8-9 profile fields exactly as HTML
- ✅ Add Edit Profile button that goes to sprofile.php

---

### 3. attendance.php ↔ attendance.html

**HTML Logic (Complex):**
```javascript
// 1. Get teacher ID from localStorage
let teacherId = localStorage.getItem('currentUserId');

// 2. Load students from global registry
const students = JSON.parse(localStorage.getItem('studentsData'));

// 3. Load subject list
function getSubjectsList() {
    return JSON.parse(localStorage.getItem(`subjects_${teacherId}`) || '[]');
}

// 4. Load attendance for selected subject
function loadTable() {
    const data = localStorage.getItem(getStorageKey());  // teacher_id + subject
    // Restore rows, date columns, attendance marks (P/A/empty)
}

// 5. Add Row - fetch student data, create cells
function addRow(name, roll, attendanceArr) {
    // Create row with student name, roll, attendance dropdowns
}

// 6. Add Date Column - new date input
function addDateColumn() {
    const date = prompt('Enter date');
    dateColumns.push(date);
    updateHeader();
}

// 7. Save - collect all data and save to localStorage
function saveTable() {
    localStorage.setItem(getStorageKey(), JSON.stringify({
        subject: subject,
        dateColumns: dateColumns,
        rows: result
    }));
}

// 8. Delete Subject - remove all records for that subject
function deleteSubjectRecords() {
    Object.keys(localStorage).filter(k => k.startsWith('attendanceTableData_')).forEach(k => {
        if (getSubject(k) === currentSubject) localStorage.removeItem(k);
    });
}
```

**PHP Equivalent:**
```php
// 1. Get teacher ID from SESSION
$teacherId = $_SESSION['user_id'];

// 2. Load students from database
$students = getAllStudents();

// 3. Load subject list from database
function getTeacherSubjects($teacherId) {
    return db query "SELECT subject_name FROM subjects WHERE teacher_id = ? ...";
}

// 4. Load attendance for selected subject (AJAX)
// Handle: action=load_attendance, subject parameter
// Return JSON with rows, dates, attendance records

// 5. Add Row (AJAX)
// Handle: action=add_row, subject parameter
// Return student list for dropdown

// 6. Add Date Column (AJAX)
// Handle: action=add_date, subject, date parameter
// Store in frontend and update display

// 7. Save (AJAX)
// Handle: action=save_attendance, subject, rows data
// Loop through and call saveAttendance() for each record

// 8. Delete Subject (AJAX)
// Handle: action=delete_subject, subject parameter
// Call deleteSubjectAttendance($teacherId, $subject)
```

**Key Updates Needed:**
- ✅ Implement multiple AJAX endpoints for each action
- ✅ Fetch students from getAllStudents() (not localStorage)
- ✅ Save attendance records one by one to database
- ✅ Load attendance from database and rebuild table UI
- ✅ Subject management CRUD via database
- ✅ Match HTML table layout exactly
- ✅ Date columns, student rows, attendance dropdowns (P/A/empty)

---

### 4. tprofile2.php ↔ tprofile.html (if exists) or tprofile2.html

**HTML Logic:**
```javascript
// 1. Check if logged in
if (!localStorage.getItem('currentUserId')) → redirect

// 2. Get current teacher ID
const teacherId = localStorage.getItem('currentUserId');

// 3. Collect form data
const profileData = {
    teacherId: teacherId,
    institute: ...,
    passingYear: ...,
    photoURL: (base64 from image preview)
};

// 4. Save to localStorage
localStorage.setItem(`teacher_${teacherId}`, JSON.stringify(profileData));

// 5. Redirect
window.location.href = 'tdetails.html';
```

**PHP Equivalent:**
```php
// 1. Check session
if (!isset($_SESSION['user_id'])) → redirect

// 2. Get teacher ID
$teacherId = $_SESSION['user_id'];

// 3. Collect POST data

// 4. Save to database
createTeacherProfile($teacherId, $dob, $institute, $passingYear, $photoURL);

// 5. Redirect
header('Location: tdetails.php');
```

**Key Updates Needed:**
- ✅ AJAX form submission with JSON response
- ✅ Message boxes (success/error)
- ✅ Image preview
- ✅ Validation for all fields
- ✅ Save profile picture as base64 in database

---

### 5. tdetails.php ↔ tdetails.html

**HTML Logic:**
```javascript
// 1. Get teacher profile from localStorage
const teacherData = JSON.parse(localStorage.getItem(`teacher_${teacherId}`));

// 2. Display card with all fields
// - DOB, Institute, Passing Year, Photo

// 3. Show navigation buttons
// - Attendance, Your Profile (this page), Edit Profile, Options, Logout
```

**PHP Equivalent:**
```php
// 1. Get teacher profile from database
$teacher = getTeacher($teacherId);  // Joins with teacher_profiles

// 2. Display same card layout with all fields

// 3. Show same buttons
```

**Key Updates Needed:**
- ✅ Fetch teacher profile with profile data
- ✅ Display all fields from teacher and teacher_profiles tables
- ✅ Show profile photo from database
- ✅ Add Edit Profile button

---

### 6. notify.php ↔ notify.html

**HTML Logic:**
```javascript
// 1. Check if logged in
if (!localStorage.getItem('currentUserId')) → show message

// 2. Load notifications from localStorage
const notifications = JSON.parse(localStorage.getItem('notifications_' + userId));

// 3. Display notifications list
// 4. Show weekly schedule grid (Mon-Fri, Time slots)
// 5. Display next class info
```

**PHP Equivalent:**
```php
// 1. Check session

// 2. Load notifications from database
if (isLoggedIn) {
    $notifications = getStudentNotifications($_SESSION['user_id']);
}

// 3. Display notifications from DB
// 4. Display schedule from exam_schedule table
// 5. Calculate next class
```

**Key Updates Needed:**
- ✅ Fetch notifications from database
- ✅ Fetch exam schedule from database
- ✅ Display weekly grid layout from HTML
- ✅ Calculate next class time

---

### 7. option.php ↔ option.html or teacher menu

**HTML Logic:**
```javascript
// 1. Check if teacher logged in

// 2. Display menu buttons:
// - Attendance
// - Profile
// - Edit Profile
// - Notifications
// - Logout

// 3. Redirect to respective pages
```

**PHP Equivalent:**
```php
// 1. Check session['user_type'] === 'teacher'

// 2. Display same buttons/menu

// 3. Links to attendance.php, tdetails.php, tprofile2.php, notify.php, logout
```

**Key Updates Needed:**
- ✅ Session validation
- ✅ Proper button links/redirects
- ✅ Logout functionality

---

### 8. log.php ↔ log.html

**HTML Logic:**
```javascript
// 1. Show role selector (Student/Teacher)

// 2. On login click:
//    a) Get form data
//    b) Find in students/teachers array from localStorage
//    c) Check password match
//    d) If match:
//       - Save currentUserId to localStorage
//       - Check if profile exists
//       - If no profile → redirect to sprofile.html/tprofile.html
//       - If profile exists → redirect to sdetails.html/tdetails.html

// 3. Email/phone optional fields
```

**PHP Equivalent:**
```php
// 1. Show role selector

// 2. On login POST:
//    a) Get form data
//    b) Call verifyStudent() or verifyTeacher()
//    c) If match:
//       - Create session via $_SESSION
//       - Check if profile exists (query student_profiles/teacher_profiles)
//       - If no profile → redirect to sprofile.php/tprofile2.php
//       - If profile exists → redirect to sdetails.php/tdetails.php

// 3. Same optional fields
```

**Key Updates Needed:**
- ✅ Database credential verification
- ✅ Password hashing check (password_verify)
- ✅ Session creation
- ✅ Profile existence check
- ✅ Smart redirect based on profile status
- ✅ AJAX form submission with messages

---

## AJAX Response Format (All PHP Files)

All AJAX endpoints should return JSON:

```php
// Success
{
    "success": true,
    "message": "Action completed",
    "data": {...}  // Optional
}

// Error
{
    "success": false,
    "message": "Error description"
}
```

---

## localStorage vs Database Mapping

| Feature | HTML (localStorage) | PHP (Database) |
|---------|-------------------|-----------------|
| User session | currentUserId in localStorage | $_SESSION['user_id'] |
| Student data | students table in localStorage | students table in DB |
| Teacher data | teachers table in localStorage | teachers table in DB |
| Profiles | localStorage `user_#{id}` | student_profiles / teacher_profiles tables |
| Attendance | localStorage `attendanceTableData_#{id}_#{subject}` | attendance table |
| Subjects | localStorage `subjects_#{id}` | subjects table |
| Notifications | localStorage `notifications_#{id}` | notifications table |

---

## Testing Checklist

For each PHP file:

- [ ] Login creates session properly
- [ ] Data saved to database (not localStorage)
- [ ] AJAX form submission works
- [ ] Success/error messages display
- [ ] Redirect happens after save
- [ ] Can retrieve and display data from database
- [ ] All validation rules match HTML version
- [ ] Image previews display
- [ ] Navigation buttons work
- [ ] Session timeouts work

---

## Key Differences to Remember

1. **No localStorage** - Everything goes to database
2. **Server-side validation** - Happens in PHP (also keep client-side)
3. **Sessions** - Use $_SESSION, not localStorage
4. **Redirects** - Use header() not window.location.href
5. **Images** - Store as base64 URLs or file paths in database
6. **AJAX** - Return JSON not HTML fragments
7. **Timing** - No localStorage.setItem() timing to worry about

