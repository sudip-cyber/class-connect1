<?php
/**
 * Login Form - PHP Version
 * Handles student and teacher login with database authentication
 */

require_once __DIR__ . '/database/config.php';
require_once __DIR__ . '/database/db_operations.php';

session_start();

$loginError = '';

    // Handle login form submission - auto-detect teacher vs student
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId = trim($_POST['userId'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($userId) || empty($password)) {
            $loginError = 'Please enter both ID and password';
        } else {
            // Try teacher first
            $resultT = verifyTeacher($userId, $password);
            if (!empty($resultT) && !empty($resultT['success'])) {
                // Teacher login
                $_SESSION['user_id'] = $resultT['teacher']['teacher_id'];
                $_SESSION['user_type'] = 'teacher';
                $_SESSION['user_name'] = $resultT['teacher']['name'] ?? '';

                logAudit($_SESSION['user_id'], 'teacher', 'login', 'Teacher logged in');

                $profile = getTeacher($_SESSION['user_id']);
                if ($profile && isset($profile['profile_id']) && $profile['profile_id']) {
                    header('Location: tdetails.php');
                } else {
                    header('Location: tprofile2.php');
                }
                exit;
            }

            // Try student
            $resultS = verifyStudent($userId, $password);
            if (!empty($resultS) && !empty($resultS['success'])) {
                $_SESSION['user_id'] = $resultS['student']['roll_number'];
                $_SESSION['user_type'] = 'student';
                $_SESSION['user_name'] = $resultS['student']['name'] ?? '';

                logAudit($_SESSION['user_id'], 'student', 'login', 'Student logged in');

                $profile = getStudent($_SESSION['user_id']);
                if ($profile && isset($profile['profile_id']) && $profile['profile_id']) {
                    header('Location: sdetails.php');
                } else {
                    header('Location: sprofile.php');
                }
                exit;
            }

            // If we reach here, neither matched
            $loginError = 'Invalid ID or Password';
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background: #ffffff;
            padding: 40px 30px;
            position: relative;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            width: 90%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #444;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }

        input:focus, select:focus {
            border-color: #7b2ff7;
            outline: none;
            box-shadow: 0 0 5px rgba(123, 47, 247, 0.3);
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #7b2ff7;
            color: rgb(0, 0, 0);
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }

        button:hover {
            background-color: #6221d2;
            transform: scale(1.02);
        }

        .error {
            color: red;
            font-size: 0.9em;
            text-align: center;
            margin-top: 15px;
            padding: 10px;
            background-color: #ffe6e6;
            border-radius: 5px;
        }

        .message-box {
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            display: none;
            animation: slideIn 0.3s ease;
        }

        .message-box.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            display: block;
        }

        .message-box.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #closeBtn {
            position: absolute;
            left: 200px;
            top: 12px;
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 25px 20px;
            }

            input, button {
                font-size: 15px;
            }

            #closeBtn {
                left: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <button id="closeBtn" title="Close">✕</button>
        <h2>Login</h2>

        <form method="POST">
                <div class="form-group">
                    <label for="userId" id="idLabel">Enter ID (Roll Number or Teacher ID):</label>
                    <input type="text" id="userId" name="userId" required>
                </div>

            <div class="form-group">
                <label for="password">Enter Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Login</button>

            <?php if (!empty($loginError)): ?>
                <div class="error"><?php echo htmlspecialchars($loginError); ?></div>
            <?php endif; ?>
        </form>
    </div>

    <script>
        document.getElementById('closeBtn').addEventListener('click', function() {
            window.location.href = 'index.php';
        });
    </script>
</body>
</html>
