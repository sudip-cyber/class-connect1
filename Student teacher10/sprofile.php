<?php
/**
 * Create Student Profile - PHP Version
 * Stores profile data in database with AJAX form submission
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
$response = ['success' => false, 'message' => ''];

// Handle AJAX form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isAjax = isset($_POST['ajax']) && $_POST['ajax'] === '1';
    
    $name = trim($_POST['name'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $fatherName = trim($_POST['fatherName'] ?? '');
    $motherName = trim($_POST['motherName'] ?? '');
    $sgpa1 = trim($_POST['sgpa1'] ?? '');
    $sgpa2 = trim($_POST['sgpa2'] ?? '');
    $sgpa3 = trim($_POST['sgpa3'] ?? '');
    $photoURL = trim($_POST['photoURL'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($fatherName)) $errors[] = 'Father\'s name is required';
    if (empty($motherName)) $errors[] = 'Mother\'s name is required';
    if (!empty($sgpa1) && (!is_numeric($sgpa1) || $sgpa1 < 0 || $sgpa1 > 10)) $errors[] = 'Invalid SGPA for semester 1 (0-10)';
    if (!empty($sgpa2) && (!is_numeric($sgpa2) || $sgpa2 < 0 || $sgpa2 > 10)) $errors[] = 'Invalid SGPA for semester 2 (0-10)';
    if (!empty($sgpa3) && (!is_numeric($sgpa3) || $sgpa3 < 0 || $sgpa3 > 10)) $errors[] = 'Invalid SGPA for semester 3 (0-10)';
    
    if (empty($errors)) {
        // Convert empty strings to null for optional fields
        $sgpa1 = empty($sgpa1) ? null : floatval($sgpa1);
        $sgpa2 = empty($sgpa2) ? null : floatval($sgpa2);
        $sgpa3 = empty($sgpa3) ? null : floatval($sgpa3);
        
        $result = createStudentProfile($rollNumber, $dob, $fatherName, $motherName, $sgpa1, $sgpa2, $sgpa3, $photoURL);
        
        if ($result['success']) {
            logAudit($rollNumber, 'student', 'create_profile', 'Student profile created');
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Profile created successfully!']);
                exit;
            } else {
                header('Location: sdetails.php');
                exit;
            }
        } else {
            $response = $result;
        }
    } else {
        $response = ['success' => false, 'message' => implode(', ', $errors)];
    }
    
    if ($isAjax) {
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
    <title>Create Profile</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(120deg, #c2e9fb 0%, #a1c4fd 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .form-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 600px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }

        label {
            font-weight: bold;
            display: block;
            margin: 15px 0 5px;
            color: #444;
        }

        input, select {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: #007BFF;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            margin-top: 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #388e3c;
        }

        .image-preview {
            display: block;
            max-width: 100px;
            margin-top: 10px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .error {
            color: #d32f2f;
            font-size: 0.9em;
            margin-top: 10px;
            padding: 10px;
            background-color: #ffebee;
            border-radius: 5px;
        }

        .message-box {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            max-width: 300px;
            animation: slideIn 0.3s ease, slideOut 0.3s ease 2.5s forwards;
            z-index: 10000;
        }

        .message-box.success {
            background-color: #4CAF50;
        }

        .message-box.error {
            background-color: #d32f2f;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(400px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(400px);
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Create Your Profile</h2>

        <?php if (!$response['success'] && !empty($response['message'])): ?>
            <div class="error"><?php echo htmlspecialchars($response['message']); ?></div>
        <?php endif; ?>

        <form id="profileForm">
            <label for="name">Name:</label>
            <input type="text" id="name" placeholder="Enter your name" required>

            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" required>

            <label for="fatherName">Father's Name:</label>
            <input type="text" id="fatherName" placeholder="Enter father's name" required pattern="[a-zA-Z ]+">

            <label for="motherName">Mother's Name:</label>
            <input type="text" id="motherName" placeholder="Enter mother's name" required pattern="[a-zA-Z ]+">

            <label for="sgpa1">1st Semester SGPA:</label>
            <input type="number" step="0.01" id="sgpa1" placeholder="Enter SGPA for 1st semester" min="0" max="10">

            <label for="sgpa2">2nd Semester SGPA:</label>
            <input type="number" step="0.01" id="sgpa2" placeholder="Enter SGPA for 2nd semester" min="0" max="10">

            <label for="sgpa3">3rd Semester SGPA:</label>
            <input type="number" step="0.01" id="sgpa3" placeholder="Enter SGPA for 3rd semester" min="0" max="10">

            <label for="photoUpload">Upload Profile Picture:</label>
            <input type="file" id="photoUpload" accept="image/*" onchange="previewImage(event)">
            <img id="imagePreview" class="image-preview" src="" alt="Image Preview" style="display:none;">

            <input type="hidden" id="photoURL">

            <button type="submit">Submit</button>
        </form>
    </div>

    <script>
        function showMessage(msg, type) {
            const box = document.createElement('div');
            box.className = 'message-box ' + type;
            box.textContent = msg;
            document.body.appendChild(box);
            setTimeout(() => box.remove(), 3000);
        }

        function previewImage(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const imagePreview = document.getElementById('imagePreview');
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
                document.getElementById('photoURL').value = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('name', document.getElementById('name').value.trim());
            formData.append('dob', document.getElementById('dob').value);
            formData.append('fatherName', document.getElementById('fatherName').value.trim());
            formData.append('motherName', document.getElementById('motherName').value.trim());
            formData.append('sgpa1', document.getElementById('sgpa1').value);
            formData.append('sgpa2', document.getElementById('sgpa2').value);
            formData.append('sgpa3', document.getElementById('sgpa3').value);
            formData.append('photoURL', document.getElementById('photoURL').value);

            fetch('sprofile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Profile saved successfully! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'sdetails.php';
                    }, 2000);
                } else {
                    showMessage(data.message || 'Failed to save profile', 'error');
                }
            })
            .catch(err => {
                showMessage('Error: ' + err.message, 'error');
            });
        });
    </script>
</body>
</html>
