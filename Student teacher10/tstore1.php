<?php
/**
 * Teachers Store Page - Lists all teachers
 */

require_once __DIR__ . '/database/config.php';
require_once __DIR__ . '/database/db_operations.php';

session_start();

// Get all teachers
$teachers = getAllTeachers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Other Teacher Profiles</title>
    <style>
        :root{ --bg:#cfe9ff; --card-bg:#fff; --muted:#6b7280 }
        body{font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;margin:0;background:var(--bg);padding:40px}
        .wrap{max-width:1100px;margin:0 auto}
        h2{ text-align:center;margin:6px 0 18px;font-size:28px }
        .actions{display:flex;justify-content:center;gap:12px;flex-wrap:wrap;margin-bottom:20px}
        .actions button{border:0;padding:8px 14px;border-radius:8px;cursor:pointer;font-weight:600}
        .actions .students{background:#28a745;color:#fff}
        .actions .teachers{background:#6c757d;color:#fff}
        .actions .attendance{background:#ffc107;color:#222}
        .actions .profile{background:#6c757d;color:#fff}
        .cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:28px;align-items:start}
        .card{background:var(--card-bg);border-radius:12px;padding:28px;text-align:center;box-shadow:0 10px 25px rgba(0,0,0,0.08);min-height:260px}
        .avatar{width:130px;height:130px;border-radius:50%;object-fit:cover;border:6px solid #e6f0ff;margin:0 auto 18px;display:block}
        .name{font-weight:700;text-transform:uppercase;margin-bottom:8px}
        .meta{color:var(--muted);font-size:14px;margin:4px 0}
        .no-data{color:#555;text-align:center;padding:40px;background:var(--card-bg);border-radius:12px}
        .back-x{position:fixed;right:18px;top:18px;width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:transparent;color:#fff;font-weight:700}
        @media(max-width:600px){body{padding:18px}.card{padding:18px}.avatar{width:110px;height:110px}}
    </style>
</head>
<body>

<a class="back-x" href="index.php">⟵</a>
<div class="wrap">
    <h2>Other Teacher Profiles</h2>

    <div class="actions">
        <button class="students" onclick="window.location.href='sstore.php'">Students</button>
        <button class="teachers" onclick="try{localStorage.setItem('fromTStoreSource','tstore1')}catch(e){};window.location.href='tstore1.php'">Other Teachers</button>
        <button class="attendance" onclick="window.location.href='attendance.php'">Attendance</button>
        <button class="profile" onclick="window.location.href='tdetails.php'">Your Profile</button>
    </div>

    <?php if (!empty($teachers)): ?>
        <div class="cards">
            <?php foreach ($teachers as $teacher):
                $photo = !empty($teacher['photo_url']) ? $teacher['photo_url'] : 'https://via.placeholder.com/150?text=No+Photo';
                $name = $teacher['name'] ?? '';
                $dob = $teacher['date_of_birth'] ?? ($teacher['dob'] ?? '');
                $institute = $teacher['institute'] ?? '';
                $year = $teacher['passing_year'] ?? '';
            ?>
            <div class="card">
                <img src="<?php echo htmlspecialchars($photo); ?>" alt="<?php echo htmlspecialchars($name); ?>" class="avatar">
                <div class="name"><?php echo htmlspecialchars($name); ?></div>
                <?php if ($dob): ?><div class="meta"><strong>DOB:</strong> <?php echo htmlspecialchars($dob); ?></div><?php endif; ?>
                <?php if ($institute): ?><div class="meta"><strong>Institute:</strong> <?php echo htmlspecialchars($institute); ?></div><?php endif; ?>
                <?php if ($year): ?><div class="meta"><strong>Year:</strong> <?php echo htmlspecialchars($year); ?></div><?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-data">No teachers registered yet.</div>
    <?php endif; ?>

</div>
</body>
</html>
