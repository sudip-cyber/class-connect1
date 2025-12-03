<?php
require_once __DIR__ . '/database/config.php';
require_once __DIR__ . '/database/db_operations.php';
session_start();

// Determine student id: prefer session, fallback to query param
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student') {
    $studentId = $_SESSION['user_id'];
} elseif (!empty($_GET['roll'])) {
    $studentId = $_GET['roll'];
} else {
    header('Location: log.php');
    exit;
}

// Logout handling
if (isset($_GET['logout'])) {
    if (isset($_SESSION['user_id'])) {
        logAudit($_SESSION['user_id'], 'student', 'logout', 'Student logged out');
    }
    session_destroy();
    header('Location: log.php');
    exit;
}

$studentData = getStudent($studentId);
$attendance = getStudentAttendance($studentId);
// latest remark
$latestRemark = getLatestStudentRemark($studentId);

// Build attendance summary by subject
$summary = [];
if (!empty($attendance)) {
    foreach ($attendance as $r) {
        $sub = $r['subject'] ?? 'Unknown';
        if (!isset($summary[$sub])) $summary[$sub] = ['present' => 0, 'total' => 0];
        $summary[$sub]['total']++;
        $st = strtolower(trim($r['status'] ?? ''));
        if ($st === 'present' || $st === 'p' || $st === '1') $summary[$sub]['present']++;
    }
}

// Prepare grouped attendance data (subject -> [{date,status}, ...]) for client-side popup
$attendanceGrouped = [];
if (!empty($attendance)) {
    foreach ($attendance as $r) {
        $sub = $r['subject'] ?? 'Unknown';
        if (!isset($attendanceGrouped[$sub])) $attendanceGrouped[$sub] = [];
        // try common date keys
        $date = $r['date'] ?? $r['att_date'] ?? $r['attendance_date'] ?? $r['taken_on'] ?? $r['ts'] ?? '';
        // normalize timestamp if numeric
        if (is_numeric($date) && (int)$date > 0) {
            $date = date('Y-m-d H:i:s', (int)$date);
        }
        $status = $r['status'] ?? $r['attendance'] ?? $r['present'] ?? '';
        $attendanceGrouped[$sub][] = ['date' => (string)$date, 'status' => (string)$status];
    }
}
// teachers list for chat (server-driven)
$teachersList = function_exists('getAllTeachers') ? getAllTeachers() : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Profile</title>
    <style>
        :root{--bg:#e9edf1;--card:#eef3f7;--muted:#6b7280}
        body{font-family:'Segoe UI',Tahoma, Geneva, Verdana, sans-serif;background:var(--bg);margin:0;padding:36px 18px}
        .wrap{max-width:980px;margin:0 auto}
        h2{font-size:26px;text-align:center;margin-bottom:18px;color:#1f2937}
        .top-actions{display:flex;gap:12px;flex-wrap:wrap;justify-content:center;margin-bottom:28px}
        .top-actions button{padding:10px 18px;border-radius:10px;border:0;cursor:pointer;box-shadow:0 6px 18px rgba(16,24,40,0.08);font-weight:600}
        .act-blue{background:#3b82f6;color:#fff}
        .act-green{background:#10b981;color:#fff}
        .act-gray{background:#6b7280;color:#fff}
        .act-yellow{background:#f59e0b;color:#111}
        .act-red{background:#ef4444;color:#fff}

        .card{display:flex;gap:24px;background:var(--card);padding:28px;border-radius:16px;box-shadow:0 18px 30px rgba(16,24,40,0.08);align-items:center;margin:0 auto 22px;max-width:820px}
        .profile-photo{width:120px;height:120px;border-radius:50%;object-fit:cover;border:6px solid #fff}
        .profile-info{flex:1;color:#111}
        .profile-info h3{margin:0 0 8px;font-size:20px;text-transform:lowercase;text-align:center}
        .profile-info p{margin:6px 0;color:var(--muted)}

        .attendance-summary{background:var(--card);padding:22px;border-radius:16px;box-shadow:0 12px 24px rgba(16,24,40,0.06);max-width:720px;margin:18px auto}
        .attendance-summary h3{text-align:center;margin-top:0}
        .attendance-summary ul{list-style:none;padding:0;margin:12px 0 0}
        .attendance-summary li{padding:8px 0}

        @media(max-width:640px){.card{flex-direction:column;text-align:center}.profile-info{text-align:left}}
        /* Class routine modal */
        .routine-overlay{ position: fixed; left:0;top:0;right:0;bottom:0; display:flex;align-items:center;justify-content:center; background:rgba(0,0,0,0.5); z-index:11000; visibility:hidden; opacity:0; transition:opacity 160ms ease,visibility 160ms; }
        .routine-overlay.open{ visibility:visible; opacity:1; }
        .routine-modal{ background:#fff;border-radius:10px; max-width:900px; width:96%; padding:12px; box-shadow:0 20px 60px rgba(0,0,0,0.35); max-height:90vh; overflow:auto }
        .routine-modal img{ width:100%; height:auto; display:block; border-radius:6px }
        .routine-actions{ display:flex; gap:8px; justify-content:flex-end; margin-top:8px }
        /* Attendance detail modal */
        .att-modal-overlay{ position: fixed; left:0;top:0;right:0;bottom:0; display:flex;align-items:center;justify-content:center; background:rgba(0,0,0,0.45); z-index:10000; visibility:hidden; opacity:0; transition:opacity 160ms ease,visibility 160ms; }
        .att-modal-overlay.open{ visibility:visible; opacity:1; }
        .att-modal{ background:#fff;border-radius:10px; max-width:720px; width:96%; padding:16px; box-shadow:0 14px 40px rgba(0,0,0,0.25); max-height:80vh; overflow:auto }
        .att-modal h4{ margin:0 0 8px 0 }
        .att-modal .att-list{ display:flex; flex-direction:column; gap:6px; margin-top:10px }
        .att-modal .att-row{ display:flex; justify-content:space-between; gap:12px; padding:8px; border-radius:6px; background:#f6f8fb }
    </style>
    <style>
        /* Chat modal popup styles (shared) */
        .modal-overlay-chat {
            position: fixed; left: 0; top: 0; right: 0; bottom: 0;
            display: flex; align-items: center; justify-content: center;
            background: rgba(0,0,0,0.45); z-index: 14000;
            visibility: hidden; opacity: 0; transition: opacity 180ms ease, visibility 180ms;
            pointer-events: none;
        }
        .modal-overlay-chat.open { visibility: visible; opacity: 1; pointer-events: auto; }
        .modal-chat { background: #fff; border-radius: 12px; width: 820px; max-width: 96%; padding: 14px; box-shadow: 0 12px 40px rgba(0,0,0,0.2); transform: translateY(8px) scale(0.98); opacity: 0; transition: transform 220ms cubic-bezier(.2,.9,.3,1), opacity 220ms; }
        .modal-overlay-chat.open .modal-chat { transform: translateY(0) scale(1); opacity: 1; }
        .modal-chat .modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
        .modal-chat .close-btn{background:#eee;border-radius:6px;border:none;padding:6px 10px;cursor:pointer}
    </style>
    <script src="includes/storage-shim.js"></script>
</head>
<body>
<div class="wrap">
    <h2>Your Profile Details</h2>
    <div class="top-actions">
        <button class="act-blue" onclick="window.location.href='viewo.php'">View Other Profiles</button>
        <button class="act-blue" onclick="window.location.href='tstore1.php'">Teachers</button>
        <button id="classRoutineBtn" class="act-gray">Routine</button>
        <button class="act-blue" id="openGroupChatBtn">Message</button>
        <button class="act-blue" onclick="window.location.href='sdetails.php'">My Profile</button>
        <button class="act-green" onclick="window.location.href='sprofile.php'">Edit Profile</button>
        <button class="act-red" onclick="window.location.href='?logout=1'">Logout</button>
    </div>

    <div class="card">
        <?php if (!empty($studentData['photo_url'])): ?>
            <img src="<?php echo htmlspecialchars($studentData['photo_url']); ?>" alt="photo" class="profile-photo">
        <?php else: ?>
            <div class="profile-photo" style="background:#cbd5e1"></div>
        <?php endif; ?>

        <div class="profile-info">
            <h3><?php echo htmlspecialchars($studentData['name'] ?? ($studentData['first_name'] . ' ' . ($studentData['last_name'] ?? ''))); ?></h3>
            <p><strong>Roll Number:</strong> <?php echo htmlspecialchars($studentData['roll_number'] ?? $studentData['roll_no'] ?? ''); ?></p>
            <?php if (!empty($studentData['date_of_birth'])): ?><p><strong>DOB:</strong> <?php echo htmlspecialchars($studentData['date_of_birth']); ?></p><?php endif; ?>
            <?php if (!empty($studentData['father_name'])): ?><p><strong>Father's Name:</strong> <?php echo htmlspecialchars($studentData['father_name']); ?></p><?php endif; ?>
            <?php if (!empty($studentData['mother_name'])): ?><p><strong>Mother's Name:</strong> <?php echo htmlspecialchars($studentData['mother_name']); ?></p><?php endif; ?>
            <?php if (!empty($studentData['sgpa_sem1']) || !empty($studentData['sgpa_sem2']) || !empty($studentData['sgpa_sem3'])): ?>
                <p><strong>SGPA (1st Sem):</strong> <?php echo htmlspecialchars($studentData['sgpa_sem1'] ?? '-'); ?></p>
                <p><strong>SGPA (2nd Sem):</strong> <?php echo htmlspecialchars($studentData['sgpa_sem2'] ?? '-'); ?></p>
                <p><strong>SGPA (3rd Sem):</strong> <?php echo htmlspecialchars($studentData['sgpa_sem3'] ?? '-'); ?></p>
            <?php endif; ?>
            <?php if (!empty($studentData['remarks'])): ?>
                <p><strong>Remarks:</strong> <?php echo htmlspecialchars($studentData['remarks']); ?></p>
            <?php endif; ?>
            <!-- latest remark container (will be updated dynamically via storage events) -->
            <p id="latestRemarkContainer">
                <?php if (!empty($latestRemark) && empty($studentData['remarks'])): ?>
                    <strong>Remark:</strong> <?php echo htmlspecialchars($latestRemark['remark']); ?> <em style="color:#666">(by <?php echo htmlspecialchars($latestRemark['teacher_name'] ?? $latestRemark['teacher_id']); ?>)</em>
                <?php elseif (!empty($latestRemark)): ?>
                    <strong>Latest Remark:</strong> <?php echo htmlspecialchars($latestRemark['remark']); ?> <em style="color:#666">(by <?php echo htmlspecialchars($latestRemark['teacher_name'] ?? $latestRemark['teacher_id']); ?>)</em>
                <?php else: ?>
                    <!-- empty placeholder when no remark available -->
                <?php endif; ?>
            </p>
        </div>
    </div>
        <!-- Class routine modal (shows uploaded or bundled image) -->
        <div id="routineOverlay" class="routine-overlay" onclick="if(event.target===this) closeRoutineModal()">
            <div class="routine-modal" role="dialog" aria-modal="true">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px">
                    <h4 style="margin:0">Class Routine</h4>
                    <div style="display:flex;gap:8px;align-items:center">
                        <input id="routineFileInput" type="file" accept="image/*" style="display:none" />
                        <button onclick="document.getElementById('routineFileInput').click()" style="padding:6px 10px;border-radius:6px;border:none;background:#0b5fff;color:#fff;cursor:pointer">Upload Image</button>
                        <button onclick="closeRoutineModal()" style="background:#eee;border:none;padding:6px 8px;border-radius:6px;cursor:pointer">Close</button>
                    </div>
                </div>
                <div id="routineContent" style="margin-top:12px">
                    <div style="color:#666">No routine image uploaded yet. Click Upload Image to add.</div>
                </div>
                <div class="routine-actions">
                    <button onclick="removeRoutineImage()" style="background:#ff4e4e;color:#fff;border:none;padding:8px 10px;border-radius:6px">Remove</button>
                </div>
            </div>
        </div>

    <div class="attendance-summary">
        <h3>Attendance Summary</h3>
        <?php if (!empty($summary)): ?>
            <ul>
                <?php foreach ($summary as $sub => $stat):
                    $pct = $stat['total'] ? round(100 * $stat['present'] / $stat['total']) : 0;
                ?>
                <li><a href="#" class="subject-link" data-subject="<?php echo htmlspecialchars($sub); ?>" style="color:#2563eb;text-decoration:underline"><?php echo htmlspecialchars($sub); ?></a> &nbsp; <?php echo $stat['present'].' / '.$stat['total'].' ('.$pct.'%)'; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="text-align:center;color:var(--muted)">No attendance records available.</p>
        <?php endif; ?>
    </div>
</div>
</div>
<script>
    // expose current student roll to client JS
    var STUDENT_ROLL = <?php echo json_encode((string)$studentId, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?>;
    // Attendance data for popup (provided by server)
    var attendanceBySubject = <?php echo json_encode($attendanceGrouped, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?> || {};

    // Show attendance popup for a subject
    function showAttendancePopup(subject){
        try{
            if(!subject) return;
            const records = attendanceBySubject[subject] || [];
            // create modal if not exists
            let modal = document.getElementById('attendanceModal');
            if(!modal){
                modal = document.createElement('div'); modal.id='attendanceModal'; modal.className='att-modal-overlay';
                modal.innerHTML = `<div class="att-modal" role="dialog" aria-modal="true">
                    <div style="display:flex;justify-content:space-between;align-items:center">
                      <h4 id="attModalTitle">Attendance details</h4>
                      <button onclick="closeAttendanceModal()" style="background:#eee;border:none;padding:6px 8px;border-radius:6px;cursor:pointer">Close</button>
                    </div>
                    <div id="attModalContent" class="att-list"></div>
                  </div>`;
                document.body.appendChild(modal);
            }
            const title = modal.querySelector('#attModalTitle');
            const content = modal.querySelector('#attModalContent');
            title.textContent = `Dates for ${subject}`;
            if(!records.length){ content.innerHTML = '<div style="color:#666">No date-level records found for this subject and student.</div>'; }
            else{
                // try to sort by date
                try{ records.sort((a,b)=> new Date(a.date) - new Date(b.date)); }catch(e){}
                let out = '';
                records.forEach(it => {
                    const raw = (it.status||'').toString().trim().toUpperCase();
                    const present = (raw === 'P' || raw === 'PRESENT' || raw === '1' || raw === 'YES');
                    const statusText = present ? 'Present' : (raw ? 'Absent' : 'No Data');
                    out += `<div class="att-row"><div>${escapeHtml(it.date||'')}</div><div style="font-weight:700">${escapeHtml(statusText)}</div></div>`;
                });
                content.innerHTML = out;
            }
            // open modal
            setTimeout(()=>{ modal.classList.add('open'); },10);
            modal.classList.add('open');
            modal.onclick = function(e){ if(e.target === modal) closeAttendanceModal(); };
        }catch(e){ console.warn('showAttendancePopup error', e); }
    }

    function closeAttendanceModal(){ try{ const m=document.getElementById('attendanceModal'); if(m) m.classList.remove('open'); setTimeout(()=>{ try{ const mm=document.getElementById('attendanceModal'); if(mm) mm.remove(); }catch(e){} },220); }catch(e){} }

    // attach handlers to subject links
    function initSubjectLinks(){
        try{
            const links = document.querySelectorAll('.subject-link');
            links.forEach(l => {
                l.addEventListener('click', function(e){ e.preventDefault(); const subj = this.dataset.subject; showAttendancePopup(subj); });
            });
        }catch(e){}
    }

    // small HTML escaper used in modal rendering
    function escapeHtml(str){ return String(str).replace(/[&<>"']/g, function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[m]; }); }

    // Routine modal handlers
    function openRoutineModal(){
        try{
            const overlay = document.getElementById('routineOverlay');
            if(!overlay) return;
            renderRoutineImage();
            overlay.classList.add('open');
        }catch(e){console.warn(e)}
    }

    function closeRoutineModal(){ try{ const o=document.getElementById('routineOverlay'); if(o) o.classList.remove('open'); }catch(e){} }

    function renderRoutineImage(){
        try{
            const content = document.getElementById('routineContent');
            const data = localStorage.getItem('classRoutineImage');
            if(!content) return;
            if(!data){
                content.innerHTML = `<img src="routine.jpg" alt="Class Routine" onerror="this.style.display='none';document.getElementById('routineContent').innerHTML='<div style=\"color:#666\">No routine image uploaded yet. Click Upload Image to add.</div>'" />`;
                return;
            }
            content.innerHTML = `<img src="${data}" alt="Class Routine" />`;
        }catch(e){ }
    }

    // file input handler - store as data URL
    try{
        const rf = document.getElementById('routineFileInput');
        if(rf){ rf.addEventListener('change', function(e){ const f = rf.files && rf.files[0]; if(!f) return; const reader = new FileReader(); reader.onload = function(ev){ try{ localStorage.setItem('classRoutineImage', ev.target.result); renderRoutineImage(); }catch(e){ alert('Failed to save image'); } }; reader.readAsDataURL(f); }); }
    }catch(e){}

    function removeRoutineImage(){ try{ localStorage.removeItem('classRoutineImage'); renderRoutineImage(); }catch(e){} }

    // Wire the Routine button
    try{ document.getElementById('classRoutineBtn').addEventListener('click', openRoutineModal); }catch(e){}

    // initialize subject link handlers
    try{ initSubjectLinks(); }catch(e){}

    // Close modal on ESC
    try{ document.addEventListener('keydown', function(e){ if(e.key === 'Escape'){ closeRoutineModal(); } }); }catch(e){}

    // If storage changes (other tab), refresh routine image
    window.addEventListener('storage', function(e){ if(!e.key) return; if(e.key === 'classRoutineImage' || e.key === 'classRoutineImage_update') renderRoutineImage(); });

    // Listen for remark updates for this student (localStorage key: student_latest_remark_{roll})
    (function(){
        function renderRemarkFromPayload(payload){
            try{
                var container = document.getElementById('latestRemarkContainer');
                if(!container) return;
                if(!payload || !payload.remark){
                    // if empty, clear only if container had no server value
                    // do not overwrite server-rendered static remarks; but it's acceptable to update
                    container.innerHTML = '';
                    return;
                }
                var teacher = payload.teacher || '';
                var byText = teacher ? (' <em style="color:#666">(by ' + escapeHtml(teacher) + ')</em>') : '';
                container.innerHTML = '<strong>Latest Remark:</strong> ' + escapeHtml(payload.remark) + byText;
            }catch(e){ console.warn('renderRemarkFromPayload error', e); }
        }

        function tryLoadAndRender(){
            try{
                var key = 'student_latest_remark_' + STUDENT_ROLL;
                var v = localStorage.getItem(key);
                if(!v) return;
                var parsed = JSON.parse(v);
                renderRemarkFromPayload(parsed);
            }catch(e){}
        }

        // storage event (other tabs/windows)
        window.addEventListener('storage', function(ev){
            try{
                if(!ev.key) return;
                var expected = 'student_latest_remark_' + STUDENT_ROLL;
                if(ev.key === expected){
                    var val = ev.newValue;
                    if(!val){ renderRemarkFromPayload(null); return; }
                    var parsed = JSON.parse(val);
                    renderRemarkFromPayload(parsed);
                }
            }catch(e){}
        });

        // On initial load check (covers case where save happened in same tab)
        tryLoadAndRender();
    })();
</script>

<!-- Group Chat Modal -->
<div id="groupChatModal" class="modal-overlay-chat" style="display:none;">
    <div class="modal-chat" role="dialog" aria-modal="true">
        <div class="modal-header">
            <div id="groupChatTitle" style="font-weight:700">All-staff Group Chat</div>
            <div style="display:flex;gap:8px;align-items:center">
                <button class="close-btn" id="closeGroupChatBtn">✕</button>
            </div>
        </div>
        <div style="margin-top:8px;">
            <div id="groupMessagesBox" style="background:#fff;border-radius:10px;padding:12px;height:360px;overflow:auto;border:1px solid #e6eefb"></div>
            <div style="display:flex;gap:8px;margin-top:8px;align-items:center">
                <input id="groupChatInput" type="text" placeholder="Type a message to everyone" style="flex:1;padding:10px;border-radius:8px;border:1px solid #d0e3f0" />
                <input id="groupFileInput" type="file" />
                <button class="save-btn" id="groupSendBtn">Send</button>
            </div>
        </div>
    </div>
</div>

<script>
// Group chat for students (all participants)
(function(){
    const modal = document.getElementById('groupChatModal');
    const openBtn = document.getElementById('openGroupChatBtn');
    const closeBtn = document.getElementById('closeGroupChatBtn');
    const messagesBox = document.getElementById('groupMessagesBox');
    const inputEl = document.getElementById('groupChatInput');
    const fileEl = document.getElementById('groupFileInput');
    const sendBtn = document.getElementById('groupSendBtn');
    const POLL_MS = 1500;
    let poller = null;

    const meType = <?php echo json_encode($_SESSION['user_type'] ?? ''); ?>;
    const meId = <?php echo json_encode($_SESSION['user_id'] ?? ''); ?>;

    function fmtTime(ts){ try{ return new Date(ts).toLocaleString(); }catch(e){ return ts||''; } }

    function escapeHtml(s){ return String(s||'').replace(/[&<>'"]/g,function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[m];}); }

    function renderMessages(arr){ messagesBox.innerHTML=''; arr.forEach(m => {
        const mine = String(m.from_type) === String(meType) && String(m.from_id) === String(meId);
        const row = document.createElement('div'); row.style.display='flex'; row.style.justifyContent = mine ? 'flex-end' : 'flex-start'; row.style.marginBottom='10px';
        const bubble = document.createElement('div'); bubble.style.maxWidth='78%'; bubble.style.padding='10px'; bubble.style.borderRadius='12px'; bubble.style.background = mine ? '#dcf8c6' : '#fff'; bubble.style.boxShadow='0 1px 0 rgba(0,0,0,0.04)';
        const meta = document.createElement('div'); meta.style.fontSize='12px'; meta.style.color='#666'; meta.style.marginBottom='6px'; meta.textContent = (m.from_type||'') + ' · ' + (m.from_id||'') + ' · ' + fmtTime(m.created_at || m.ts || '');
        const text = document.createElement('div'); text.style.whiteSpace='pre-wrap'; text.textContent = m.message || m.content || '';
        bubble.appendChild(meta);
        if(text.textContent) bubble.appendChild(text);
        if(m.attachment){ const a = document.createElement('div'); a.style.marginTop='6px'; const link = document.createElement('a'); link.href = m.attachment; link.target='_blank'; link.textContent = 'Attachment'; a.appendChild(link); bubble.appendChild(a); }
        row.appendChild(bubble); messagesBox.appendChild(row);
    }); messagesBox.scrollTop = messagesBox.scrollHeight; }

    async function fetchMessages(){
        try{
            const res = await fetch('api/get_group_messages.php');
            const text = await res.text();
            let j = null;
            try{ j = JSON.parse(text); } catch(err){ console.warn('get_group_messages non-json response', text); return; }
            if(j && j.success && Array.isArray(j.messages)){
                renderMessages(j.messages);
            } else if (j && !j.success) {
                console.warn('get_group_messages error', j.message || j.detail || j);
            }
        }catch(e){ console.warn('group fetch error', e); }
    }

    function startPolling(){ if(poller) clearInterval(poller); fetchMessages(); poller = setInterval(fetchMessages, POLL_MS); }
    function stopPolling(){ if(poller) clearInterval(poller); poller = null; }

    async function uploadFile(file){
        try{
            const fd = new FormData(); fd.append('file', file);
            const r = await fetch('api/upload_attachment.php', { method: 'POST', body: fd });
            const j = await r.json(); if(j && j.success) return j.url; throw new Error(j && j.message ? j.message : 'Upload failed');
        }catch(e){ console.warn('upload error', e); alert('File upload failed'); return null; }
    }

    async function sendMessage(){
        const txt = (inputEl.value||'').trim(); const file = fileEl.files && fileEl.files[0];
        if(!txt && !file){ alert('Enter a message or attach a file.'); return; }
        sendBtn.disabled = true;
        let attachmentUrl = null;
        if(file){ attachmentUrl = await uploadFile(file); }

        const payload = { to_type: 'group', to_id: 'all', message: txt };
        if(attachmentUrl) payload.attachment = attachmentUrl;

        // optimistic echo
        const temp = { message_id: 'tmp_' + Date.now(), from_type: meType, from_id: meId, message: txt, attachment: attachmentUrl, created_at: (new Date()).toISOString() };
        renderMessages((function(){ const cur = []; messagesBox.querySelectorAll('div').forEach(()=>{}); return []; })());
        // call send endpoint
        try{
            const res = await fetch('api/send_message.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
            const text = await res.text();
            let j = null;
            try{ j = JSON.parse(text); } catch(err){ alert('Server error: ' + text); console.warn('send_message non-json response', text); }
            if(j && !(j && j.success)){
                alert('Send failed: ' + (j && j.message ? j.message : 'Unknown'));
            }
        }catch(e){ alert('Network error'); console.warn(e); }
        inputEl.value = ''; fileEl.value = '';
        sendBtn.disabled = false;
        fetchMessages();
    }

    openBtn && openBtn.addEventListener('click', function(){ modal.style.display='flex'; modal.classList.add('open'); startPolling(); setTimeout(()=>inputEl.focus(),120); });
    closeBtn && closeBtn.addEventListener('click', function(){ modal.classList.remove('open'); modal.style.display='none'; stopPolling(); });
    sendBtn && sendBtn.addEventListener('click', sendMessage);
    inputEl && inputEl.addEventListener('keydown', function(e){ if(e.key === 'Enter'){ e.preventDefault(); sendMessage(); } });
})();
</script>
</body>
</html>
