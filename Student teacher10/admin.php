<?php
/**
 * Admin Page - PHP Version
 * Simple admin panel to view system data
 */

require_once __DIR__ . '/database/config.php';
require_once __DIR__ . '/database/db_operations.php';

session_start();

// Simple password protection for admin
$adminPassword = 'admin123'; // Change this in production!
$isAdmin = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_password'])) {
    if ($_POST['admin_password'] === $adminPassword) {
        $_SESSION['is_admin'] = true;
        $isAdmin = true;
    } else {
        $error = 'Invalid admin password';
    }
}

if (isset($_SESSION['is_admin'])) {
    $isAdmin = true;
}

// Handle logout
if (isset($_GET['logout'])) {
    unset($_SESSION['is_admin']);
    $isAdmin = false;
}

// Get statistics
if ($isAdmin) {
    $students = getAllStudents();
    $teachers = getAllTeachers();
    $db = getDB();
    
    $stmt = $db->query('SELECT COUNT(*) as count FROM attendance');
    $attendanceCount = $stmt->fetch()['count'];
    
    $stmt = $db->query('SELECT COUNT(*) as count FROM notifications');
    $notificationCount = $stmt->fetch()['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }
        h2 {
            text-align: center;
            color: #1e3c72;
            margin-bottom: 30px;
        }
        .login-form {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 8px;
        }
        .login-form input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .login-form button {
            width: 100%;
            padding: 10px;
            background: #1e3c72;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .login-form button:hover {
            background: #2a5298;
        }
        .error {
            color: #d32f2f;
            text-align: center;
            margin: 10px 0;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 2em;
        }
        .stat-card p {
            margin: 10px 0 0 0;
        }
        .table-section {
            margin: 30px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background: #1e3c72;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .back-btn, .logout-btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 5px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }
        .back-btn {
            background: #4CAF50;
            color: white;
        }
        .logout-btn {
            background: #f44336;
            color: white;
        }
        .back-btn:hover {
            background: #388e3c;
        }
        .logout-btn:hover {
            background: #da190b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Admin Panel</h2>

        <?php if (!$isAdmin): ?>
            <div class="login-form">
                <h3 style="text-align: center;">Admin Login</h3>
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="password" name="admin_password" placeholder="Enter admin password" required>
                    <button type="submit">Login</button>
                </form>
            </div>
        <?php else: ?>
            <div style="text-align: center; margin-bottom: 20px;">
                <a href="index.php" class="back-btn">Back to Home</a>
                <a href="?logout=1" class="logout-btn">Logout</a>
            </div>

            <!-- Statistics -->
            <div class="stats">
                <div class="stat-card">
                    <h3><?php echo count($students); ?></h3>
                    <p>Total Students</p>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <h3><?php echo count($teachers); ?></h3>
                    <p>Total Teachers</p>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <h3><?php echo $attendanceCount; ?></h3>
                    <p>Attendance Records</p>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <h3><?php echo $notificationCount; ?></h3>
                    <p>Notifications</p>
                </div>
            </div>

            <!-- Students Table -->
            <div class="table-section">
                <h3>Registered Students</h3>
                <?php if (!empty($students)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Roll Number</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Profile Complete</th>
                                <th>Registered On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($students, 0, 10) as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($student['roll_number'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo ($student['profile_id'] ? '✓' : '✗'); ?></td>
                                    <td><?php echo htmlspecialchars($student['created_at'] ?? 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (count($students) > 10): ?>
                        <p style="color: #999;">Showing 10 of <?php echo count($students); ?> students</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p>No students registered yet.</p>
                <?php endif; ?>
            </div>

            <!-- Teachers Table -->
            <div class="table-section">
                <h3>Registered Teachers</h3>
                <?php if (!empty($teachers)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Teacher ID</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Institute</th>
                                <th>Profile Complete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teachers as $teacher): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($teacher['name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($teacher['teacher_id'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($teacher['email'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($teacher['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($teacher['institute'] ?? 'N/A'); ?></td>
                                    <td><?php echo ($teacher['profile_id'] ? '✓' : '✗'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No teachers registered yet.</p>
                <?php endif; ?>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <a href="index.php" class="back-btn">Back to Home</a>
                <a href="?logout=1" class="logout-btn">Logout</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
