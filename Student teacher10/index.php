<?php
/**
 * Main Landing Page
 */
session_start();

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] === 'teacher') {
        header('Location: option.php');
    } else {
        header('Location: sdetails.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <style>
        body {
            display: flex;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            align-items: center;
            justify-content: center;
            background-color: #f7d8d8;
            background-image: url('TIT.png');
            background-size: 700px;
            background-repeat: no-repeat;
            background-position: center;
            background-attachment: fixed;
        }
        .container {
            text-align: center;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
        .option {
            display: block;
            margin: 10px;
            padding: 15px 30px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            color: white;
            border-radius: 5px;
            width: 200px;
            transition: background-color 0.3s ease;
        }
        .generate {
            background-color: #4CAF50;
        }
        .generate:hover {
            background-color: #3e8e41;
        }
        .login {
            background-color: #008CBA;
        }
        .login:hover {
            background-color: #007ba7;
        }
        .admin {
            background-color: #008CBA;
        }
        .admin:hover {
            background-color: #007ba7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div style="position: absolute; left: 20px; top: 20px;">
            <button id="settingsBtn" title="Settings" style="background:none;border:none;cursor:pointer;font-size:20px;">⚙️</button>
            <div id="settingsPanel" style="display:none; margin-top:8px;">
                <button class="option admin" style="width:160px; padding:8px 12px; font-size:14px;" onclick="window.location.href='admin.php'">Admin Login</button>
            </div>
        </div>
        <button class="option login" onclick="window.location.href='log.php'">Log In</button>
        <button class="option generate" onclick="window.location.href='generate.php'">Register</button>
    </div>

    <script>
        document.getElementById('settingsBtn').addEventListener('click', function() {
            const panel = document.getElementById('settingsPanel');
            panel.style.display = (panel.style.display === 'none' || panel.style.display === '') ? 'block' : 'none';
        });
    </script>
</body>
</html>
