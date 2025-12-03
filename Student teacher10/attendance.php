<?php
/**
 * Attendance Management - PHP Version
 * Stores attendance records in database instead of localStorage
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

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    
    if ($action === 'save_attendance') {
        $subject = trim($_POST['subject'] ?? '');
        $rows = json_decode($_POST['rows'] ?? '[]', true);
        $dateColumns = json_decode($_POST['dateColumns'] ?? '[]', true);
        
        if (empty($subject)) {
            echo json_encode(['success' => false, 'message' => 'Subject is required']);
            exit;
        }
        
        // Save subject
        saveSubject($teacherId, $subject);
        
        // Save attendance records
        $successCount = 0;
        foreach ($rows as $row) {
            $rollNumber = $row['roll'] ?? '';
            if (empty($rollNumber)) continue;
            
            foreach ($row['attendance'] as $idx => $att) {
                $date = $att['date'] ?? '';
                $status = $att['value'] ?? '';
                
                if (!empty($date)) {
                    $result = saveAttendance($teacherId, $rollNumber, $subject, $date, $status);
                    if ($result['success']) $successCount++;
                }
            }
        }
        
        logAudit($teacherId, 'teacher', 'save_attendance', "Saved attendance for subject: $subject");
        echo json_encode(['success' => true, 'message' => "Attendance saved ($successCount records)", 'count' => $successCount]);
        exit;
    }
    
    if ($action === 'load_attendance') {
        $subject = trim($_POST['subject'] ?? '');
        
        if (empty($subject)) {
            echo json_encode(['success' => false, 'data' => []]);
            exit;
        }
        
        $records = getAttendanceBySubject($teacherId, $subject);
        $students = getAllStudents();
        
        // Group attendance by student
        $attendanceByStudent = [];
        foreach ($records as $record) {
            $roll = $record['roll_number'];
            if (!isset($attendanceByStudent[$roll])) {
                $attendanceByStudent[$roll] = [];
            }
            $attendanceByStudent[$roll][] = $record;
        }
        
        echo json_encode([
            'success' => true,
            'records' => $records,
            'byStudent' => $attendanceByStudent,
            'students' => $students
        ]);
        exit;
    }
    
    if ($action === 'get_subjects') {
        $subjects = getTeacherSubjects($teacherId);
        echo json_encode(['success' => true, 'subjects' => $subjects]);
        exit;
    }
    
    if ($action === 'delete_subject') {
        $subject = trim($_POST['subject'] ?? '');
        
        if (empty($subject)) {
            echo json_encode(['success' => false, 'message' => 'Subject is required']);
            exit;
        }
        
        $result = deleteSubjectAttendance($teacherId, $subject);
        
        if ($result['success']) {
            logAudit($teacherId, 'teacher', 'delete_attendance', "Deleted attendance for subject: $subject");
            echo json_encode(['success' => true, 'message' => "All attendance records for '$subject' have been deleted"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete records']);
        }
        exit;
    }
}

// Get all students for prefill
$allStudents = getAllStudents();
$subjects = getTeacherSubjects($teacherId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dynamic Attendance Table</title>
    <style>
        :root{
            --bg:#e9f2ff;
            --card:#ffffff;
            --muted:#67809a;
            --accent:#2b6ef6;
            --success:#28a745;
            --danger:#dc3545;
            --warning:#f0ad4e;
            --radius:10px;
            --pad:20px;
        }
        html,body{height:100%;}
        body {
            font-family: Inter, Arial, Helvetica, sans-serif;
            background: linear-gradient(180deg,var(--bg),#f7fbff);
            padding: 28px;
            color: #1f2937;
        }
        .container {
            max-width: 980px;
            margin: 0 auto;
            background: var(--card);
            border-radius: var(--radius);
            padding: calc(var(--pad) + 8px);
            box-shadow: 0 10px 30px rgba(35,50,80,0.08);
            text-align: left;
            border: 1px solid rgba(20,40,80,0.04);
        }
        .top-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}
        h2 { margin: 0; font-size:1.3rem; letter-spacing:0.2px; }
        .subtitle{color:var(--muted);font-size:0.95rem}
        .user-badge{font-size:0.85rem;padding:6px 10px;border-radius:999px;background:#f1f5ff;color:var(--accent);border:1px solid rgba(43,110,246,0.08)}
        .toolbar{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
        .btn { padding: 8px 12px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: transform .08s ease, box-shadow .08s ease; display:inline-flex;align-items:center;gap:8px }
        .btn:active{transform:translateY(1px)}
        .btn:hover{box-shadow:0 6px 14px rgba(43,110,246,0.06)}
        .btn-add { background: var(--success); color: #fff; }
        .btn-add-col { background: var(--warning); color: #1f2937; }
        .btn-del { background: transparent; color: var(--danger); border:1px solid rgba(220,53,69,0.08); padding:6px 8px;border-radius:6px }
        .btn-del:hover{background:rgba(220,53,69,0.04)}
        .btn-del-col { background: transparent; color: var(--accent); border:1px solid rgba(43,110,246,0.08); padding:6px 8px;border-radius:6px }
        .date-header{display:flex;align-items:center;justify-content:center;gap:6px}
        .date-input{padding:6px;border-radius:6px;border:1px solid #e6eefb}
        .subject-input{padding:6px 10px;border-radius:8px;border:1px solid #e6eefb;min-width:200px;margin-right:8px}
        .btn-save { background: var(--accent); color: #fff; margin-top: 10px; }
        .fixed-input { width: 150px; min-width: 120px; max-width: 100%; padding: 6px; border-radius:6px;border:1px solid #e6eefb }
        input[type="text"], input[type="number"] { padding: 6px; border-radius:6px; border:1px solid #e6eefb }
        input[type="date"] { padding: 6px; border-radius:6px; border:1px solid #e6eefb }
        select.attendance-select { width: 64px; padding: 6px; border-radius:6px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 14px; table-layout: fixed; }
        th, td { border-bottom: 1px solid #eef6ff; padding: 10px 8px; text-align: center; overflow:hidden; text-overflow:ellipsis; white-space:nowrap }
        th { background: linear-gradient(180deg,#f8fbff,#ffffff); position:sticky; top:0; z-index:2; }
        thead th{font-weight:700;color:#16324a}
        tbody tr:nth-child(even) { background: #fbfdff; }
        tbody tr:hover{background:#f4fbff}
        .empty-state{padding:28px;text-align:center;color:var(--muted)}
        .message { padding: 12px; border-radius: 6px; margin-bottom: 15px; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        @media (max-width:720px){
            .top-row{flex-direction:column;align-items:stretch}
            .toolbar{justify-content:space-between}
            .subject-input{min-width:140px;width:100%}
            .fixed-input{width:120px}
            table{font-size:0.92rem}
            th,td{padding:8px}
        }
    </style>
</head>
<body>
    <div class="container">
        <div id="messageBox"></div>

        <div class="top-row">
            <div>
                <h2>Attendance Table</h2>
                <div class="subtitle">Database Backed - Teacher: <?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                <div class="btn-group" style="margin-top:10px; gap:8px;">
                    <button class="btn" onclick="location.href='sstore.php'">Students</button>
                    <button class="btn" onclick="location.href='tstore1.php'">Other Teachers</button>
                    <button class="btn" onclick="location.href='attendance.php'">Attendance</button>
                    <button class="btn" onclick="location.href='tdetails.php'">Your Profile</button>
                </div>
            </div>
            <div class="toolbar">
                <div style="display:flex;align-items:center;gap:8px">
                    <input id="subjectInput" class="subject-input" type="text" placeholder="Subject (optional)">
                    <select id="subjectSelect" class="subject-input" style="min-width:160px"></select>
                    <button id="addSubjectBtn" class="btn btn-add-col" onclick="addCurrentSubject()">➕ Add Subject</button>
                    <button id="deleteSubjectBtn" class="btn btn-del" onclick="deleteSubjectRecords()" title="Delete all attendance records for selected subject">🗑 Delete Subject Data</button>
                </div>
                <div id="userBadge" class="user-badge">&nbsp;</div>
                <div style="display:flex;gap:8px">
                    <button class="btn btn-add" onclick="addRow()">➕ Add Row</button>
                    <button class="btn btn-add-col" onclick="addDateColumn()">📅 Add Date</button>
                </div>
            </div>
        </div>

        <table id="attendanceTable">
            <thead>
                <tr id="headerRow">
                    <th>Name</th>
                    <th>Roll No.</th>
                </tr>
            </thead>
            <tbody id="tableBody">
            </tbody>
        </table>
        <button class="btn btn-save" onclick="saveTable()">Save to Database</button>
    </div>

    <script>
        const teacherId = '<?php echo htmlspecialchars($teacherId); ?>';
        let dateColumns = [];

        function showMessage(message, type) {
            const msgBox = document.getElementById('messageBox');
            msgBox.innerHTML = `<div class="message ${type}">${message}</div>`;
            setTimeout(() => msgBox.innerHTML = '', 5000);
        }

        function getStorageKey() {
            try {
                const subjEl = document.getElementById('subjectInput');
                const raw = subjEl ? (subjEl.value || '') : '';
                return "attendance_" + teacherId + "_" + (raw.trim() ? raw.trim() : 'default');
            } catch (e) {
                return "attendance_" + teacherId + "_default";
            }
        }

        function loadSubjects() {
            fetch('attendance.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=get_subjects'
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    populateSubjectSelect(data.subjects);
                }
            });
        }

        function populateSubjectSelect(subjects = []) {
            const sel = document.getElementById('subjectSelect');
            if (!sel) return;
            sel.innerHTML = '<option value="">-- select subject --</option>';
            subjects.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s;
                opt.textContent = s;
                sel.appendChild(opt);
            });
        }

        function addCurrentSubject() {
            const subjEl = document.getElementById('subjectInput');
            if (!subjEl) return;
            const s = subjEl.value.trim();
            if (!s) {
                alert('Enter a subject name to add.');
                return;
            }
            
            fetch('attendance.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=save_subject&subject=' + encodeURIComponent(s)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Subject added: ' + s);
                    loadSubjects();
                    loadTable();
                }
            });
        }

        function deleteSubjectRecords() {
            const subjEl = document.getElementById('subjectInput');
            if (!subjEl) return;
            const s = subjEl.value.trim();
            
            if (!s) {
                alert('Select a subject to delete');
                return;
            }
            
            if (!confirm(`Delete ALL attendance records for subject "${s}"? This cannot be undone.`)) return;
            
            fetch('attendance.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=delete_subject&subject=' + encodeURIComponent(s)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    loadTable();
                } else {
                    showMessage(data.message || 'Delete failed', 'error');
                }
            });
        }

        function attendanceSelectHTML(selected = '') {
            return `<select class="attendance-select">
                <option value=""></option>
                <option value="A" ${selected === 'A' ? 'selected' : ''}>A</option>
                <option value="P" ${selected === 'P' ? 'selected' : ''}>P</option>
            </select>`;
        }

        function addRow(name = '', roll = '', attendance = []) {
            const tbody = document.getElementById('tableBody');
            const row = document.createElement('tr');

            let td1 = document.createElement('td');
            td1.innerHTML = `<input type="text" class="fixed-input" placeholder="Name" value="${name}">`;
            row.appendChild(td1);

            let td2 = document.createElement('td');
            td2.innerHTML = `<input type="text" class="fixed-input" placeholder="Roll No." pattern="\\d*" value="${roll}" oninput="this.value=this.value.replace(/[^\\d]/g,'')">`;
            row.appendChild(td2);

            for(let i = 0; i < dateColumns.length; i++) {
                let td = document.createElement('td');
                let val = '';
                if (attendance && attendance[i]) {
                    const item = attendance[i];
                    val = (typeof item === 'object' && item !== null) ? (item.value || '') : String(item || '');
                }
                td.innerHTML = attendanceSelectHTML(val);
                row.appendChild(td);
            }

            let tdDel = document.createElement('td');
            tdDel.innerHTML = `<button class="btn btn-del" onclick="deleteRow(this)">Delete</button>`;
            row.appendChild(tdDel);

            tbody.appendChild(row);
            updateHeader();
        }

        function deleteRow(btn) {
            if (confirm("Are you sure?")) {
                btn.closest('tr').remove();
            }
        }

        function addDateColumn(dateVal = '') {
            dateColumns.push(dateVal);
            updateHeader();
            const tbody = document.getElementById('tableBody');
            for(let row of tbody.rows) {
                let td = document.createElement('td');
                td.innerHTML = attendanceSelectHTML();
                row.insertBefore(td, row.cells[row.cells.length - 1]);
            }
        }

        function deleteDateColumn(idx) {
            if (typeof idx !== 'number' || idx < 0 || idx >= dateColumns.length) return;
            const delDate = dateColumns[idx] || '';
            if (!confirm(`Delete column${delDate ? ' for ' + delDate : ''}?`)) return;
            dateColumns.splice(idx, 1);
            updateHeader();
            const tbody = document.getElementById('tableBody');
            for(let row of tbody.rows) {
                const cellIndex = idx + 2;
                if (cellIndex >= 0 && cellIndex < row.cells.length) {
                    row.deleteCell(cellIndex);
                }
            }
        }

        function updateHeader() {
            const headerRow = document.getElementById('headerRow');
            while(headerRow.cells.length > 2) {
                headerRow.deleteCell(2);
            }
            dateColumns.forEach((date, idx) => {
                let th = document.createElement('th');
                th.innerHTML = `<div class="date-header">
                    <input class="date-input" type="date" value="${date}" onchange="setDateColumn(${idx}, this.value)">
                    <button class="btn btn-del-col" onclick="deleteDateColumn(${idx})">🗑</button>
                </div>`;
                headerRow.appendChild(th);
            });
            let thDel = document.createElement('th');
            thDel.textContent = '';
            headerRow.appendChild(thDel);
        }

        function setDateColumn(idx, value) {
            dateColumns[idx] = value;
        }

        function saveTable() {
            const rows = document.querySelectorAll('#tableBody tr');
            const subject = document.getElementById('subjectInput').value.trim();
            
            if (!subject) {
                alert('Please enter a subject name');
                return;
            }
            
            let result = [];
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                let obj = {
                    name: cells[0].querySelector('input').value.trim(),
                    roll: cells[1].querySelector('input').value.trim(),
                    attendance: []
                };
                for(let i = 0; i < dateColumns.length; i++) {
                    const select = cells[2 + i].querySelector('select');
                    obj.attendance.push({
                        date: dateColumns[i],
                        value: select ? select.value : ""
                    });
                }
                result.push(obj);
            });

            const formData = new FormData();
            formData.append('action', 'save_attendance');
            formData.append('subject', subject);
            formData.append('rows', JSON.stringify(result));
            formData.append('dateColumns', JSON.stringify(dateColumns));

            fetch('attendance.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                } else {
                    showMessage(data.message || 'Save failed', 'error');
                }
            });
        }

        function loadTable() {
            const subject = document.getElementById('subjectInput').value.trim();
            const students = <?php echo json_encode($allStudents); ?>;
            
            if (!subject) {
                dateColumns = [];
                updateHeader();
                document.getElementById('tableBody').innerHTML = '';
                if (students && students.length > 0) {
                    students.forEach(s => {
                        const rollVal = s.roll_number || '';
                        const nameVal = s.name || '';
                        addRow(nameVal, rollVal, []);
                    });
                } else {
                    addRow();
                }
                return;
            }

            fetch('attendance.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=load_attendance&subject=' + encodeURIComponent(subject)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const byStudent = data.byStudent || {};
                    dateColumns = [];
                    const allDates = new Set();
                    
                    for (let roll in byStudent) {
                        byStudent[roll].forEach(rec => {
                            if (rec.attendance_date) {
                                allDates.add(rec.attendance_date);
                            }
                        });
                    }
                    
                    dateColumns = Array.from(allDates).sort();
                    updateHeader();
                    document.getElementById('tableBody').innerHTML = '';

                    if (students && students.length > 0) {
                        students.forEach(s => {
                            const rollVal = s.roll_number || '';
                            const nameVal = s.name || '';
                            let att = [];
                            if (byStudent[rollVal]) {
                                att = byStudent[rollVal].map(rec => ({
                                    date: rec.attendance_date,
                                    value: rec.status
                                }));
                            }
                            addRow(nameVal, rollVal, att);
                        });
                    }
                }
            });
        }

        document.getElementById('subjectSelect').addEventListener('change', function() {
            const val = this.value;
            document.getElementById('subjectInput').value = val;
            loadTable();
        });

        document.getElementById('subjectInput').addEventListener('blur', function() {
            loadTable();
        });

        // Initialize
        loadSubjects();
        loadTable();
    </script>
</body>
</html>
