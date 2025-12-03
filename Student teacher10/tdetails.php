<?php
/**
 * Teacher Details Page - PHP Version
 * Displays teacher profile from database
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
$teacherData = getTeacher($teacherId);

if (!$teacherData) {
    header('Location: tprofile2.php');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    logAudit($_SESSION['user_id'], 'teacher', 'logout', 'Teacher logged out');
    session_destroy();
    header('Location: index.php');
    exit;
}
// students list for chat
$studentsList = getAllStudents();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Details</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #e0e5ec;
            margin: 0;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .card {
            background: #e0e5ec;
            border-radius: 20px;
            box-shadow: 8px 8px 15px #a3b1c6, -8px -8px 15px #ffffff;
            padding: 30px;
            width: 100%;
            max-width: 800px;
            display: flex;
            gap: 30px;
            align-items: center;
            margin-bottom: 30px;
        }

        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #d1d9e6;
            background: #fff;
        }

        .profile-info p {
            margin: 8px 0;
            font-size: 15px;
            color: #333;
        }

        .buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        /* Button color variants to match the design */
        .btn-edit { background:#0d6efd; color:#fff; }
        .btn-students { background:#28a745; color:#fff; }
        .btn-teachers { background:#6c757d; color:#fff; }
        .btn-msg { background:#6c757d; color:#fff; }
        .btn-att { background:#ffc107; color:#222; }
        .btn-profile { background:#6c757d; color:#fff; }

        button {
            border: none;
            padding: 12px 22px;
            border-radius: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 4px 4px 8px #a3b1c6, -4px -4px 8px #ffffff;
        }

        .primary-btn {
            background-color: #4285f4;
            color: white;
        }

        .primary-btn:hover {
            background-color: #3367d6;
        }

        .logout-btn {
            background-color: #ff4e4e;
            color: white;
        }

        .logout-btn:hover {
            background-color: #e04343;
        }

        @media (max-width: 600px) {
            .card {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .buttons {
                flex-direction: column;
            }
        }
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
        .modal-chat { background: #fff; border-radius: 12px; width: 720px; max-width: 96%; padding: 14px; box-shadow: 0 12px 40px rgba(0,0,0,0.2); transform: translateY(8px) scale(0.98); opacity: 0; transition: transform 220ms cubic-bezier(.2,.9,.3,1), opacity 220ms; }
        .modal-overlay-chat.open .modal-chat { transform: translateY(0) scale(1); opacity: 1; }
        .modal-chat .modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
        .modal-chat .close-btn{background:#eee;border-radius:6px;border:none;padding:6px 10px;cursor:pointer}
    </style>
</head>
<body>
    <h2>Teacher Profile</h2>

    <div class="card">
        <?php if ($teacherData && $teacherData['photo_url']): ?>
            <img src="<?php echo htmlspecialchars($teacherData['photo_url']); ?>" alt="Profile Photo" class="profile-photo">
        <?php else: ?>
            <div class="profile-photo" style="display:flex;align-items:center;justify-content:center;background:#ccc;color:#999;">No Photo</div>
        <?php endif; ?>

        <div class="profile-info">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($teacherData['name'] ?? ''); ?></p>
            <p><strong>Teacher ID:</strong> <?php echo htmlspecialchars($teacherData['teacher_id'] ?? ''); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($teacherData['email'] ?? 'Not provided'); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($teacherData['phone'] ?? 'Not provided'); ?></p>
            <?php if ($teacherData['date_of_birth']): ?>
                <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($teacherData['date_of_birth']); ?></p>
            <?php endif; ?>
            <?php if ($teacherData['institute']): ?>
                <p><strong>Institute:</strong> <?php echo htmlspecialchars($teacherData['institute']); ?></p>
            <?php endif; ?>
            <?php if ($teacherData['passing_year']): ?>
                <p><strong>Passing Year:</strong> <?php echo htmlspecialchars($teacherData['passing_year']); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="buttons">
        <button class="btn-edit" onclick="window.location.href='tprofile.php'">Edit</button>
        <button class="btn-students" onclick="window.location.href='sstore.php'">Students</button>
        <button class="btn-teachers" onclick="try{localStorage.setItem('fromTStoreSource','tdetails')}catch(e){};window.location.href='tstore1.php'">Other Teachers</button>
        <button class="btn-msg" id="openGroupChatBtnT">Message</button>
        <button class="btn-att" onclick="window.location.href='attendance.php'">Attendance</button>

        <!-- secondary row -->
        <button class="btn-profile" onclick="window.location.href='tdetails.php'">Your Profile</button>
        <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
    </div>
    <!-- Group Chat Modal for teacher -->
    <div id="groupChatModalT" class="modal-overlay-chat" style="display:none;">
        <div class="modal-chat" role="dialog" aria-modal="true">
            <div class="modal-header">
                <div id="groupChatTitleT" style="font-weight:700">All-staff Group Chat</div>
                <div style="display:flex;gap:8px;align-items:center">
                    <button class="close-btn" id="closeGroupChatBtnT">✕</button>
                </div>
            </div>
            <div style="margin-top:8px;">
                <div id="groupMessagesBoxT" style="background:#fff;border-radius:10px;padding:12px;height:360px;overflow:auto;border:1px solid #d0e3fa"></div>
                <div style="display:flex;gap:8px;margin-top:8px;align-items:center">
                    <input id="groupChatInputT" type="text" placeholder="Type a message to everyone" style="flex:1;padding:8px;border-radius:8px;border:1px solid #d0e3f0" />
                    <input id="groupFileInputT" type="file" />
                    <button class="save-btn" id="groupSendBtnT">Send</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Group chat for teachers (all participants)
    (function(){
        const openBtn = document.getElementById('openGroupChatBtnT');
        const modal = document.getElementById('groupChatModalT');
        const closeBtn = document.getElementById('closeGroupChatBtnT');
        const messagesBox = document.getElementById('groupMessagesBoxT');
        const inputEl = document.getElementById('groupChatInputT');
        const fileEl = document.getElementById('groupFileInputT');
        const sendBtn = document.getElementById('groupSendBtnT');
        const POLL_MS = 2000;
        let poller = null;

        const meType = <?php echo json_encode($_SESSION['user_type'] ?? ''); ?>;
        const meId = <?php echo json_encode($_SESSION['user_id'] ?? ''); ?>;

        function fmtTime(ts){ try{ return new Date(ts).toLocaleString(); }catch(e){ return ts||''; } }
        function escapeHtml(s){ return String(s||'').replace(/[&<>'"]/g,function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[m];}); }

        function renderMessages(arr){ messagesBox.innerHTML=''; arr.forEach(m => {
            const mine = String(m.from_type) === String(meType) && String(m.from_id) === String(meId);
            const row = document.createElement('div'); row.style.display='flex'; row.style.justifyContent = mine ? 'flex-end' : 'flex-start'; row.style.marginBottom='10px';
            const bubble = document.createElement('div'); bubble.style.maxWidth='78%'; bubble.style.padding='10px'; bubble.style.borderRadius='12px'; bubble.style.background = mine ? '#e6f7ff' : '#fff'; bubble.style.boxShadow='0 1px 0 rgba(0,0,0,0.04)';
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
                if(j && j.success && Array.isArray(j.messages)) renderMessages(j.messages);
                else if (j && !j.success) console.warn('get_group_messages error', j.message || j.detail || j);
            }catch(e){ console.warn('group fetch error', e); }
        }

        function startPolling(){ if(poller) clearInterval(poller); fetchMessages(); poller = setInterval(fetchMessages, POLL_MS); }
        function stopPolling(){ if(poller) clearInterval(poller); poller = null; }

        async function uploadFile(file){
            try{
                const fd = new FormData(); fd.append('file', file);
                const r = await fetch('api/upload_attachment.php', { method: 'POST', body: fd });
                const text = await r.text();
                let j = null;
                try{ j = JSON.parse(text); } catch(err){ console.warn('upload_attachment non-json response', text); throw new Error('Upload failed: ' + text); }
                if(j && j.success) return j.url;
                throw new Error(j && j.message ? j.message : 'Upload failed');
            }catch(e){ console.warn('upload error', e); alert('File upload failed: ' + (e.message||'')); return null; }
        }

        async function sendMessage(){
            const txt = (inputEl.value||'').trim(); const file = fileEl.files && fileEl.files[0];
            if(!txt && !file){ alert('Enter a message or attach a file.'); return; }
            sendBtn.disabled = true; let attachmentUrl = null;
            if(file) attachmentUrl = await uploadFile(file);
            const payload = { to_type: 'group', to_id: 'all', message: txt };
            if(attachmentUrl) payload.attachment = attachmentUrl;
            try{
                const res = await fetch('api/send_message.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
                const text = await res.text();
                let j = null;
                try{ j = JSON.parse(text); } catch(err){ alert('Server error: ' + text); console.warn('send_message non-json response', text); }
                if(j && !(j && j.success)) alert('Send failed: ' + (j && j.message ? j.message : 'Unknown'));
            }catch(e){ alert('Network error'); console.warn(e); }
            inputEl.value=''; fileEl.value=''; sendBtn.disabled=false; fetchMessages();
        }

        openBtn && openBtn.addEventListener('click', function(){ modal.style.display='flex'; modal.classList.add('open'); startPolling(); setTimeout(()=>inputEl.focus(),120); });
        closeBtn && closeBtn.addEventListener('click', function(){ modal.classList.remove('open'); modal.style.display='none'; stopPolling(); });
        sendBtn && sendBtn.addEventListener('click', sendMessage);
        inputEl && inputEl.addEventListener('keydown', function(e){ if(e.key === 'Enter'){ e.preventDefault(); sendMessage(); } });
    })();
    </script>
</body>
</html>
