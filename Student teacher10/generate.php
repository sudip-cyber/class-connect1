<?php
/**
 * Registration Form - PHP Version
 * Handles student and teacher registration with database storage
 */

require_once __DIR__ . '/database/config.php';
require_once __DIR__ . '/database/db_operations.php';

$response = ['success' => false, 'message' => ''];

// Handle AJAX registration requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'register_student') {
        $name = trim($_POST['name'] ?? '');
        $rollNumber = trim($_POST['rollNumber'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        // Validation
        if (empty($name) || empty($rollNumber) || empty($email) || empty($phone) || empty($password)) {
            $response = ['success' => false, 'message' => 'All fields are required'];
        } elseif (!preg_match('/^\d{10}$/', $rollNumber)) {
            $response = ['success' => false, 'message' => 'Roll number must be exactly 10 digits'];
        } elseif ($rollNumber < 2363050001 || $rollNumber > 2363050040) {
            $response = ['success' => false, 'message' => 'Roll number must be between 2363050001 and 2363050040'];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response = ['success' => false, 'message' => 'Invalid email address'];
        } elseif (!preg_match('/^\d{10}$/', $phone)) {
            $response = ['success' => false, 'message' => 'Phone number must be exactly 10 digits'];
        } elseif (strlen($password) < 6) {
            $response = ['success' => false, 'message' => 'Password must be at least 6 characters long'];
        } else {
            $response = registerStudent($name, $rollNumber, $email, $phone, $password);
            if ($response['success']) {
                logAudit($rollNumber, 'student', 'registration', 'Student registered');
            }
        }
    } 
    elseif ($action === 'register_teacher') {
        $name = trim($_POST['name'] ?? '');
        $teacherId = strtoupper(trim($_POST['teacherId'] ?? ''));
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        $validTeacherIds = ['SU123', 'AD124', 'RU125', 'DE126', 'SH127'];
        
        // Validation
        if (empty($name) || empty($teacherId) || empty($password)) {
            $response = ['success' => false, 'message' => 'All fields are required'];
        } elseif (!in_array($teacherId, $validTeacherIds)) {
            $response = ['success' => false, 'message' => 'Invalid Teacher ID. Valid IDs: ' . implode(', ', $validTeacherIds)];
        } elseif (strlen($password) < 6) {
            $response = ['success' => false, 'message' => 'Password must be at least 6 characters long'];
        } else {
            $response = registerTeacher($name, $teacherId, $email, $phone, $password);
            if ($response['success']) {
                logAudit($teacherId, 'teacher', 'registration', 'Teacher registered');
            }
        }
    }
    
    // Return JSON response for AJAX requests
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #74ebd5, #acb6e5);
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .container {
            background-color: #ffffff;
            padding: 40px;
            position: relative;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 500px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #333;
        }

        input, select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
            transition: 0.3s ease;
            box-sizing: border-box;
        }

        input:focus, select:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 8px rgba(74, 144, 226, 0.3);
            outline: none;
        }

        .error {
            color: #e74c3c;
            font-size: 0.85em;
            margin-top: 5px;
        }

        .success {
            color: #27ae60;
            font-size: 0.85em;
            margin-top: 5px;
        }

        .role-section {
            display: none;
        }

        .active {
            display: block;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #4a90e2;
            border: none;
            color: rgb(0, 0, 0);
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #357ab8;
        }

        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        #closeBtn {
            position: absolute;
            left: 250px;
            top: 16px;
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
        }

        .message-box {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            display: none;
        }

        .message-box.show {
            display: block;
        }

        .message-box.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message-box.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 600px) {
            .container {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <button id="closeBtn" title="Close">✕</button>
        <h2>Registration Form</h2>

        <div id="messageBox" class="message-box"></div>

        <div class="form-group">
            <label>Select Role:</label>
            <select id="roleSelect" onchange="showRoleForm()">
                <option value="">Select Role</option>
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
            </select>
        </div>

        <!-- Student Form -->
        <div id="studentForm" class="role-section">
            <form id="studentFormElement" onsubmit="submitStudentForm(event)">
                <div class="form-group">
                    <label>Full Name:</label>
                    <input type="text" id="studentName" required>
                    <div class="error" id="nameError"></div>
                </div>
                <div class="form-group">
                    <label>University Roll Number:</label>
                    <input type="text" id="studentRoll" required>
                    <div class="error" id="rollError"></div>
                </div>
                <div class="form-group">
                    <label>Email Address:</label>
                    <input type="email" id="studentEmail" required>
                    <div class="error" id="emailError"></div>
                </div>
                <div class="form-group">
                    <label>Phone Number:</label>
                    <input type="text" id="studentPhone" placeholder="10 digits" required>
                    <div class="error" id="phoneError"></div>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" id="studentPassword" required>
                    <div class="error" id="passwordError"></div>
                </div>
                <button type="submit">Submit</button>
            </form>
        </div>

        <!-- Teacher Form -->
        <div id="teacherForm" class="role-section">
            <form id="teacherFormElement" onsubmit="submitTeacherForm(event)">
                <div class="form-group">
                    <label>Full Name:</label>
                    <input type="text" id="teacherName" required>
                    <div class="error" id="teacherNameError"></div>
                </div>
                <div class="form-group">
                    <label>Teacher ID:</label>
                    <input type="text" id="teacherId" title="Enter a Valid ID" required>
                    <div class="error" id="teacherIdError"></div>
                </div>
                <div class="form-group">
                    <label>Email Address:</label>
                    <input type="email" id="teacherEmail">
                    <div class="error" id="teacherEmailError"></div>
                </div>
                <div class="form-group">
                    <label>Phone Number:</label>
                    <input type="text" id="teacherPhone" placeholder="10 digits">
                    <div class="error" id="teacherPhoneError"></div>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" id="teacherPassword" required>
                    <div class="error" id="teacherPasswordError"></div>
                </div>
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>

    <script>
        function showRoleForm() {
            const role = document.getElementById('roleSelect').value;
            document.querySelectorAll('.role-section').forEach(div => {
                div.classList.remove('active');
            });
            if (role) {
                document.getElementById(role + 'Form').classList.add('active');
                clearErrors();
                hideMessage();
            }
        }

        function clearErrors() {
            document.querySelectorAll('.error').forEach(el => {
                el.textContent = '';
            });
        }

        function showMessage(message, type) {
            const msgBox = document.getElementById('messageBox');
            msgBox.textContent = message;
            msgBox.className = 'message-box show ' + type;
            setTimeout(() => msgBox.classList.remove('show'), 5000);
        }

        function hideMessage() {
            document.getElementById('messageBox').classList.remove('show');
        }

        function submitStudentForm(event) {
            event.preventDefault();
            clearErrors();

            const name = document.getElementById('studentName').value.trim();
            const rollNumber = document.getElementById('studentRoll').value.trim();
            const email = document.getElementById('studentEmail').value.trim();
            const phone = document.getElementById('studentPhone').value.trim();
            const password = document.getElementById('studentPassword').value.trim();

            let isValid = true;

            if (!name) {
                document.getElementById('nameError').textContent = 'Please enter student name';
                isValid = false;
            }

            if (!/^\d{10}$/.test(rollNumber)) {
                document.getElementById('rollError').textContent = 'Roll number must be exactly 10 digits';
                isValid = false;
            } else {
                const num = parseInt(rollNumber);
                if (num < 2363050001 || num > 2363050040) {
                    document.getElementById('rollError').textContent = 'Roll number must be between 2363050001 and 2363050040';
                    isValid = false;
                }
            }

            if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
                document.getElementById('emailError').textContent = 'Please enter a valid email address';
                isValid = false;
            }

            if (!/^\d{10}$/.test(phone)) {
                document.getElementById('phoneError').textContent = 'Phone number must be exactly 10 digits';
                isValid = false;
            }

            if (password.length < 6) {
                document.getElementById('passwordError').textContent = 'Password must be at least 6 characters long';
                isValid = false;
            }

            if (isValid) {
                const formData = new FormData();
                formData.append('action', 'register_student');
                formData.append('name', name);
                formData.append('rollNumber', rollNumber);
                formData.append('email', email);
                formData.append('phone', phone);
                formData.append('password', password);
                formData.append('ajax', '1');

                fetch('generate.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('Registration successful! Redirecting to login...', 'success');
                        setTimeout(() => {
                            window.location.href = 'log.php';
                        }, 2000);
                    } else {
                        showMessage(data.message || 'Registration failed', 'error');
                    }
                })
                .catch(error => {
                    showMessage('An error occurred: ' + error.message, 'error');
                });
            }
        }

        function submitTeacherForm(event) {
            event.preventDefault();
            clearErrors();

            const name = document.getElementById('teacherName').value.trim();
            const teacherId = document.getElementById('teacherId').value.toUpperCase().trim();
            const email = document.getElementById('teacherEmail').value.trim();
            const phone = document.getElementById('teacherPhone').value.trim();
            const password = document.getElementById('teacherPassword').value.trim();

            let isValid = true;

            if (!name) {
                document.getElementById('teacherNameError').textContent = 'Please enter teacher name';
                isValid = false;
            }

            if (!teacherId) {
                document.getElementById('teacherIdError').textContent = 'Please enter teacher ID';
                isValid = false;
            }

            if (password.length < 6) {
                document.getElementById('teacherPasswordError').textContent = 'Password must be at least 6 characters long';
                isValid = false;
            }

            if (isValid) {
                const formData = new FormData();
                formData.append('action', 'register_teacher');
                formData.append('name', name);
                formData.append('teacherId', teacherId);
                formData.append('email', email);
                formData.append('phone', phone);
                formData.append('password', password);
                formData.append('ajax', '1');

                fetch('generate.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('Registration successful! Redirecting to login...', 'success');
                        setTimeout(() => {
                            window.location.href = 'log.php';
                        }, 2000);
                    } else {
                        showMessage(data.message || 'Registration failed', 'error');
                    }
                })
                .catch(error => {
                    showMessage('An error occurred: ' + error.message, 'error');
                });
            }
        }

        document.getElementById('closeBtn').addEventListener('click', function() {
            window.location.href = 'index.php';
        });
    </script>
</body>
</html>
