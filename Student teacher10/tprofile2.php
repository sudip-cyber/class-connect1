<?php
/**
 * Create Teacher Profile - PHP Version
 * Stores teacher profile data in database
 */

require_once __DIR__ . '/database/config.php';
require_once __DIR__ . '/database/db_operations.php';

session_start();

// Check if teacher is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: log.php');
    exit;
}

$teacherId = $_SESSION['user_id'];
$response = ['success' => false, 'message' => ''];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? '');
    
    if ($action === 'save_profile') {
        $dob = trim($_POST['dob'] ?? '');
        $institute = trim($_POST['institute'] ?? '');
        $passingYear = trim($_POST['passingYear'] ?? '');
        $photoURL = trim($_POST['photoURL'] ?? '');
        
        // Validation - match HTML
        $errors = [];
        
        if (empty($institute)) $errors[] = 'Institute name is required';
        if (!empty($passingYear) && !preg_match('/^\d{4}$/', $passingYear)) {
            $errors[] = 'Passing year must be 4 digits';
        }
        
        if (empty($errors)) {
            $result = createTeacherProfile($teacherId, $dob, $institute, $passingYear, $photoURL);
            
            if ($result['success']) {
                logAudit($teacherId, 'teacher', 'profile_created', 'Teacher profile created/updated');
                $response = ['success' => true, 'message' => 'Profile saved successfully'];
            } else {
                $response = $result;
            }
        } else {
            $response = ['success' => false, 'message' => implode(', ', $errors)];
        }
        
        // Return JSON for AJAX
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
    <title>Create Teacher Profile</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", sans-serif;
            background: linear-gradient(135deg, #d5fc799c, #b0f3b9);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 105vh;
        }

        .card {
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            color: #555;
            font-weight: 600;
        }

        input[type="text"],
        input[type="date"],
        input[type="file"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
            box-sizing: border-box;
        }

        input:focus {
            border-color: #007BFF;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        button {
            display: block;
            width: 100%;
            margin-top: 25px;
            padding: 12px;
            font-size: 16px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
            font-weight: 600;
        }

        button:hover {
            background-color: #1e7e34;
        }

        .error {
            color: #d32f2f;
            font-size: 0.9em;
            margin-top: 10px;
            padding: 10px;
            background-color: #ffebee;
            border-radius: 5px;
        }

        .message {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 5px;
            display: none;
        }

        .message.show {
            display: block;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .image-preview {
            max-width: 100px;
            margin-top: 10px;
            border-radius: 10px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Create Teacher Profile</h2>
        <div id="messageBox" class="message"></div>

        <form id="profileForm">
            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob">

            <label for="institute">Passing Institute:</label>
            <input type="text" id="institute" name="institute" placeholder="Enter passing institute" required>

            <label for="passingYear">Passing Year:</label>
            <input type="number" id="passingYear" name="passingYear" placeholder="Enter passing year (YYYY)" min="1950" max="2099">

            <label for="photoUpload">Profile Picture:</label>
            <input type="file" id="photoUpload" accept="image/*" onchange="previewImage(event)">
            <img id="imagePreview" class="image-preview" src="" alt="Image Preview">
            <input type="hidden" id="photoURL" name="photoURL">

            <button type="submit">Submit</button>
        </form>
    </div>

    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const imagePreview = document.getElementById('imagePreview');
                const photoURL = document.getElementById('photoURL');
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
                photoURL.value = e.target.result;
            };
            
            if (file) {
                reader.readAsDataURL(file);
            }
        }

        function showMessage(message, type) {
            const msgBox = document.getElementById('messageBox');
            msgBox.textContent = message;
            msgBox.className = 'message show ' + type;
            setTimeout(() => msgBox.classList.remove('show'), 5000);
        }

        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const dob = document.getElementById('dob').value.trim();
            const institute = document.getElementById('institute').value.trim();
            const passingYear = document.getElementById('passingYear').value.trim();
            const photoURL = document.getElementById('photoURL').value.trim();
            
            if (!institute) {
                showMessage('Institute name is required', 'error');
                return;
            }
            
            if (passingYear && (!/^\d{4}$/.test(passingYear) || passingYear < 1950 || passingYear > 2099)) {
                showMessage('Passing year must be a valid year (YYYY)', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'save_profile');
            formData.append('dob', dob);
            formData.append('institute', institute);
            formData.append('passingYear', passingYear);
            formData.append('photoURL', photoURL);
            
            fetch('tprofile2.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Profile saved successfully! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'tdetails.php';
                    }, 2000);
                } else {
                    showMessage(data.message || 'Failed to save profile', 'error');
                }
            })
            .catch(error => {
                showMessage('Error: ' + error.message, 'error');
            });
        });
    </script>
</body>
</html>
