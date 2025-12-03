<?php
/**
 * Notifications Page - PHP Version
 * Displays class schedule and notifications with database backup
 */

require_once __DIR__ . '/database/config.php';
require_once __DIR__ . '/database/db_operations.php';

session_start();

// Get user notifications if logged in
$notifications = [];
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'student') {
    $notifications = getStudentNotifications($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Class Notifications</title>
    <style>
        :root {
            --bg: #e9f2ff;
            --card: #ffffff;
            --accent: #2b6ef6;
            --success: #28a745;
            --warning: #f0ad4e;
        }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(180deg,var(--bg),#f7fbff);
            margin: 0;
            min-height: 100vh;
            padding: 2rem;
            color: #1f2937;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--card);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(35,50,80,0.08);
        }
        .status {
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .next-class {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            color: #0c4a6e;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background: #f8fafc;
            font-weight: 600;
        }
        .time {
            white-space: nowrap;
            color: #64748b;
        }
        .btn {
            background: var(--accent);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            margin: 1rem 0;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .notification {
            background: #fff;
            border: 1px solid #e2e8f0;
            padding: 1rem;
            margin: 0.5rem 0;
            border-radius: 8px;
            border-left: 4px solid var(--accent);
        }
        .notification.unread {
            background: #f0f9ff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Class Notifications & Schedule</h1>
        
        <?php if (!empty($notifications)): ?>
            <h2>Your Notifications</h2>
            <?php foreach ($notifications as $notif): ?>
                <div class="notification <?php echo $notif['is_read'] ? '' : 'unread'; ?>">
                    <strong><?php echo htmlspecialchars($notif['title']); ?></strong>
                    <p><?php echo htmlspecialchars($notif['message']); ?></p>
                    <small><?php echo $notif['created_at']; ?></small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <button id="enableBtn" class="btn">Enable Notifications</button>
        <div id="status" class="status">Notification system ready</div>
        <div id="nextClass" class="status next-class"></div>
        <div id="schedule"></div>
    </div>

    <script>
        // Class schedule data
        const schedule = {
            Monday: [
                { time: '10:00-11:00', subject: 'Web Technologies', teachers: ['VL'] },
                { time: '11:00-12:00', subject: 'Introduction to e-Governance', teachers: ['VL'] },
                { time: '12:00-13:00', subject: 'Learning Resources', teachers: [] },
                { time: '13:00-14:00', subject: 'FOSS', teachers: ['VL'] },
                { time: '15:00-16:00', subject: 'Networking Lab', teachers: ['DT', 'RS'] },
                { time: '16:00-17:00', subject: 'Networking Lab', teachers: ['DT', 'RS'] }
            ],
            Tuesday: [
                { time: '10:00-11:00', subject: 'Project', teachers: ['RS', 'DT'] },
                { time: '11:00-12:00', subject: 'FOSS', teachers: ['VL'] },
                { time: '12:00-13:00', subject: 'Open Elective I', teachers: ['DT'] },
                { time: '13:00-14:00', subject: 'Open Elective I', teachers: ['DT'] },
                { time: '15:00-16:00', subject: 'Data Science', teachers: ['VL'] },
                { time: '16:00-17:00', subject: 'Data Science', teachers: ['VL'] }
            ],
            Wednesday: [
                { time: '10:00-11:00', subject: 'Web Technologies', teachers: ['VL'] },
                { time: '11:00-12:00', subject: 'Seminar', teachers: ['RS'] },
                { time: '12:00-13:00', subject: 'Learning Resources', teachers: [] },
                { time: '13:00-14:00', subject: 'FOSS', teachers: ['VL'] },
                { time: '15:00-16:00', subject: 'Data Science Lab', teachers: ['VL', 'DT'] },
                { time: '16:00-17:00', subject: 'Data Science Lab', teachers: ['VL', 'DT'] }
            ],
            Thursday: [
                { time: '10:00-11:00', subject: 'Project', teachers: ['RS', 'DT'] },
                { time: '11:00-12:00', subject: 'Open Elective I', teachers: ['DT'] },
                { time: '12:00-13:00', subject: 'FOSS', teachers: ['VL'] },
                { time: '13:00-14:00', subject: 'Web Technologies', teachers: ['VL'] },
                { time: '15:00-16:00', subject: 'Networking Lab', teachers: ['DT', 'RS'] },
                { time: '16:00-17:00', subject: 'Networking Lab', teachers: ['DT', 'RS'] }
            ],
            Friday: [
                { time: '10:00-11:00', subject: 'Introduction to e-Governance', teachers: ['VL'] },
                { time: '11:00-12:00', subject: 'Project', teachers: ['RS', 'DT'] },
                { time: '12:00-13:00', subject: 'Open Elective I', teachers: ['DT'] },
                { time: '13:00-14:00', subject: 'FOSS', teachers: ['VL'] },
                { time: '14:00-15:00', subject: 'Lunch Break', teachers: [] },
                { time: '15:00-16:00', subject: 'Extra Classes', teachers: ['VL', 'DT', 'RS'] }
            ]
        };

        function getNextClass() {
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const now = new Date();
            const currentDay = days[now.getDay()];
            const currentTime = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');

            if (schedule[currentDay]) {
                const todayClasses = schedule[currentDay];
                for (let cls of todayClasses) {
                    if (cls.time > currentTime) {
                        return { day: currentDay, class: cls, time: cls.time };
                    }
                }
            }
            return null;
        }

        function displayNextClass() {
            const next = getNextClass();
            const el = document.getElementById('nextClass');
            if (next) {
                el.innerHTML = `<strong>Next Class:</strong> ${next.class.subject} at ${next.time} (${next.day})`;
            } else {
                el.innerHTML = '<strong>No more classes today</strong>';
            }
        }

        function displaySchedule() {
            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            let html = '<h2>Weekly Schedule</h2>';

            for (let day of days) {
                html += `<h3>${day}</h3>`;
                html += '<table><thead><tr><th>Time</th><th>Subject</th><th>Teachers</th></tr></thead><tbody>';

                for (let cls of schedule[day]) {
                    const teachers = cls.teachers.length > 0 ? cls.teachers.join(', ') : 'N/A';
                    html += `<tr>
                        <td class="time">${cls.time}</td>
                        <td>${cls.subject}</td>
                        <td>${teachers}</td>
                    </tr>`;
                }

                html += '</tbody></table>';
            }

            document.getElementById('schedule').innerHTML = html;
        }

        // Initialize
        displayNextClass();
        displaySchedule();

        // Update next class every minute
        setInterval(displayNextClass, 60000);
    </script>
</body>
</html>
