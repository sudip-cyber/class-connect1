<?php
/**
 * Student Store Page - Placeholder
 * Lists all students in the system
 */

require_once __DIR__ . '/database/config.php';
require_once __DIR__ . '/database/db_operations.php';

session_start();

// Get all students
$students = getAllStudents();

// prepare map of student details + latest remark for client-side modal
$studentMap = [];
foreach ($students as $st) {
    $roll = $st['roll_number'] ?? $st['roll_no'] ?? '';
    if (!$roll) continue;
    $name = $st['name'] ?? trim(($st['first_name'] ?? '') . ' ' . ($st['last_name'] ?? ''));
    $photo = $st['photo_url'] ?? ($st['photo'] ?? 'placeholder.jpg');
    $dob = $st['date_of_birth'] ?? $st['dob'] ?? '';
    $father = $st['father_name'] ?? '';
    $mother = $st['mother_name'] ?? '';
    $latest = getLatestStudentRemark($roll);
    $studentMap[$roll] = ['name'=>$name,'photo'=>$photo,'dob'=>$dob,'father'=>$father,'mother'=>$mother,'latest_remark'=>($latest['remark'] ?? ''),'latest_by'=>($latest['teacher_name'] ?? $latest['teacher_id'] ?? '')];
}
    // detect teacher session for UI
    $isTeacher = (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'teacher');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students List</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e6eefc 0%, #cde7f9 100%);
            margin: 0;
            padding: 32px 20px;
            color: #222;
        }
        .container {
            max-width: 980px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 18px 22px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        .top-bar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}
        .top-bar h2{margin:0;font-size:22px}
        .nav-buttons{display:flex;gap:10px}
        .btn{padding:8px 12px;border-radius:8px;border:0;background:#6b7280;color:#fff;cursor:pointer}
        .btn-green{background:#10b981}
        .btn-yellow{background:#f59e0b;color:#111}

        .table-wrap{background:transparent;padding:6px}
        .styled-table{width:100%;border-collapse:collapse;background:#fff;border-radius:8px;overflow:hidden}
        .styled-table thead th{background:#f1f5f9;color:#111;padding:14px;text-align:left}
        .styled-table tbody tr{border-bottom:1px solid #eef2f7}
        .styled-table tbody td{padding:16px;background:#fff}

        .small-btn{padding:6px 10px;border-radius:6px;border:0;background:#f3f4f6;color:#111;cursor:pointer;margin-right:8px}
        .small-btn.red{background:#ef4444;color:#fff}

        .back-btn{display:inline-block;margin-bottom:8px;padding:8px 14px;background:#3b82f6;color:#fff;border-radius:8px;text-decoration:none}

        .empty{text-align:center;padding:24px;color:#666}

        .logout-btn{background:#ef4444;color:#fff;border:none;padding:12px 22px;border-radius:10px;cursor:pointer}
    </style>
</head>
<body>
    <div class="container">
        <div class="top-bar">
            <h2>Students Profiles</h2>
            <div class="nav-buttons">
                <button class="btn" onclick="location.href='sstore.php'">Students</button>
                    <button class="btn" onclick="location.href='tstore1.php'">Other Teachers</button>
                    <button class="btn" onclick="location.href='attendance.php'">Attendance</button>
                    <button class="btn" onclick="location.href='tdetails.php'">Your Profile</button>
            </div>
        </div>

        <div class="table-wrap">
        <?php if (!empty($students)): ?>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Roll No.</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student):
                        $name = htmlspecialchars($student['name'] ?? ($student['first_name'] . ' ' . ($student['last_name'] ?? '')));
                        $roll = htmlspecialchars($student['roll_number'] ?? $student['roll_no'] ?? '');
                    ?>
                    <tr>
                        <td><?php echo $name; ?></td>
                        <td><?php echo $roll; ?></td>
                        <td>
                            <button class="small-btn" onclick="openStudentModal('<?php echo $roll; ?>')">View more</button>
                            
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty">No students registered yet.</div>
        <?php endif; ?>
        </div>
    </div>
    <div style="text-align:center;margin-top:26px">
        <button class="logout-btn" onclick="logout()">Logout</button>
    </div>
    <!-- Student profile modal will be created dynamically -->
    <script>
        // client-side map of students (populated from PHP)
        var STUDENT_MAP = <?php echo json_encode($studentMap, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?> || {};
        var IS_TEACHER = <?php echo json_encode($isTeacher ? true : false); ?>;

        function logout(){ window.location.href='logout.php'; }

        function confirmDelete(roll){
            if(!confirm('Delete student ' + roll + '? This action cannot be undone.')) return;
            fetch('api/delete_student.php', {
                method: 'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ roll: roll })
            }).then(r=>r.json()).then(j=>{ if(j && j.success){ alert('Deleted'); window.location.reload(); } else { alert('Delete failed: ' + (j.message||'unknown')); } }).catch(e=>{ alert('Network error'); });
        }

        // Open student profile modal populated from STUDENT_MAP
        function openStudentModal(roll){
            var data = STUDENT_MAP[roll];
            if(!data){ alert('Student not found'); return; }

            // remove existing modal if any
            var existing = document.getElementById('studentProfileModal');
            if(existing) existing.remove();

            var modal = document.createElement('div');
            modal.id = 'studentProfileModal';
            modal.style.position = 'fixed'; modal.style.left='0'; modal.style.top='0'; modal.style.right='0'; modal.style.bottom='0';
            modal.style.display='flex'; modal.style.alignItems='center'; modal.style.justifyContent='center'; modal.style.background='rgba(0,0,0,0.45)'; modal.style.zIndex='13000';

            var photoUrl = data.photo || 'placeholder.jpg';
            var html = `
                <div style="background:#fff;border-radius:10px;max-width:760px;width:92%;padding:18px;display:flex;gap:18px;box-shadow:0 18px 60px rgba(0,0,0,0.25)">
                  <div style="width:140px;flex-shrink:0;text-align:center">
                    <img src="${escapeHtml(photoUrl)}" style="width:120px;height:120px;border-radius:10px;object-fit:cover;border:6px solid #fff"/>
                  </div>
                  <div style="flex:1">
                    <div style="display:flex;justify-content:space-between;align-items:start">
                      <div>
                        <h3 style="margin:0">${escapeHtml(data.name||'')}</h3>
                        <div style="color:#333;margin-top:8px"><strong>Roll No:</strong> ${escapeHtml(roll)}</div>
                        <div style="color:#333;margin-top:6px"><strong>DOB:</strong> ${escapeHtml(data.dob||'')}</div>
                        <div style="color:#333;margin-top:6px"><strong>Father's Name:</strong> ${escapeHtml(data.father||'')}</div>
                        <div style="color:#333;margin-top:6px"><strong>Mother's Name:</strong> ${escapeHtml(data.mother||'')}</div>
                      </div>
                      <button onclick="closeStudentModal()" style="background:transparent;border:none;font-size:22px;cursor:pointer">✕</button>
                    </div>
                    <div style="margin-top:12px">
                      <label style="font-weight:600;display:block;margin-bottom:6px">Remarks</label>
                      <textarea id="studentRemarkInput" style="width:100%;height:120px;padding:10px;border:1px solid #e6eef8;border-radius:6px">${escapeHtml(data.latest_remark||'')}</textarea>
                      <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:10px">
                        ${IS_TEACHER ? `<button onclick="saveRemark('${escapeJs(roll)}')" style="background:#0b5fff;color:#fff;border:none;padding:8px 12px;border-radius:6px;cursor:pointer">Save</button>` : ''}
                        <button onclick="closeStudentModal()" style="background:#eee;border:none;padding:8px 12px;border-radius:6px;cursor:pointer">Close</button>
                      </div>
                    </div>
                  </div>
                </div>`;

            modal.innerHTML = html;
            document.body.appendChild(modal);
        }

        function closeStudentModal(){ var m=document.getElementById('studentProfileModal'); if(m) m.remove(); }

        function saveRemark(roll){
            var txt = document.getElementById('studentRemarkInput').value.trim();
            if(!txt){ alert('Enter a remark'); return; }
            fetch('api/add_remark.php',{ method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ student_roll: roll, remark: txt })})
            .then(r=>r.json()).then(j=>{
                    if(j && j.success){
                        alert('Remark saved');
                        // update local map
                        if(STUDENT_MAP[roll]) STUDENT_MAP[roll].latest_remark = txt;
                        // write to localStorage so other tabs/pages (like sdetails.php) can pick up the change
                        try{
                            var payload = { remark: txt, teacher: (j.teacher_name || j.teacher || j.teacher_id || ''), ts: (new Date()).toISOString() };
                            localStorage.setItem('student_latest_remark_' + roll, JSON.stringify(payload));
                        }catch(e){ /* ignore localStorage errors */ }
                    } else { alert('Save failed: ' + (j.message||'unknown')); }
            }).catch(e=>{ alert('Network error'); });
        }

        // HTML escaper and helper to safely embed values
        function escapeHtml(str){ if(!str && str!==0) return ''; return String(str).replace(/[&<>\"']/g, function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;',"'":"&#39;"}[m]}); }
        function escapeJs(s){ return String(s).replace(/['\\]/g,'\\$&'); }

    </script>
</body>
</html>
