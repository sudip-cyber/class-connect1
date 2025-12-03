<?php
/**
 * Student Profile Options - PHP Version
 * Allows student to choose between creating new or accessing existing profile
 */

require_once __DIR__ . '/database/config.php';
require_once __DIR__ . '/database/db_operations.php';

session_start();

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: log.php');
    exit;
}

$rollNumber = $_SESSION['user_id'];
$studentData = getStudent($rollNumber);
$hasProfile = $studentData && isset($studentData['profile_id']) && $studentData['profile_id'];
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
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        h2 {
            color: #ffffff;
            margin-bottom: 30px;
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

        .disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .disabled:hover {
            transform: none;
        }

        .info {
            color: #fff;
            font-size: 14px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Select Profile Option</h2>
    <button onclick="<?php echo $hasProfile ? 'alert(\"Profile already exists!\")' : 'window.location.href=\"sprofile.php\"'; ?>" <?php echo $hasProfile ? 'class="disabled" disabled' : ''; ?>>Create New Profile</button>
    <button onclick="window.location.href='sdetails.php'">Access Existing Profile</button>
    <?php if ($hasProfile): ?>
        <div class="info">✓ You already have a profile created</div>
    <?php else: ?>
        <div class="info">ℹ Create your profile first</div>
    <?php endif; ?>
</div>

</body>
</html>
