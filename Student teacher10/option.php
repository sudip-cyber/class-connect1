<?php
/**
 * Teacher Options Page - PHP Version
 */

require_once __DIR__ . '/database/config.php';
require_once __DIR__ . '/database/db_operations.php';

session_start();

// Check if teacher is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: log.php');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    logAudit($_SESSION['user_id'], 'teacher', 'logout', 'Teacher logged out');
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Options</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f9ecc1 0%, #f6c9bd 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background: rgb(231, 157, 157);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        h2 {
            color: #ffffff;
            margin-bottom: 30px;
        }

        .info {
            color: #fff;
            margin-bottom: 20px;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            border: none;
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 0.25);
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s ease;
        }

        button:hover {
            background-color: rgba(255, 255, 255, 0.45);
            transform: translateY(-2px);
        }

        .logout-btn {
            background-color: #ff4e4e;
            margin-top: 20px;
        }

        .logout-btn:hover {
            background-color: #e04343;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Teacher Options</h2>
        <div class="info">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
        
        <button onclick="window.location.href='attendance.php'">Manage Attendance</button>
        <button onclick="window.location.href='tdetails.php'">View My Profile</button>
        <button onclick="window.location.href='tprofile2.php'">Edit Profile</button>
        <button onclick="window.location.href='notify.php'">Notifications</button>
        
        <button class="logout-btn" onclick="window.location.href='?logout=1'">Logout</button>
    </div>
</body>
</html>
