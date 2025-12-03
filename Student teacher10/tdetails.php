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
        <button class="btn-msg" onclick="openServerChatModalTeacher()">Messaging</button>
        <button class="btn-att" onclick="window.location.href='attendance.php'">Attendance</button>

        <!-- secondary row -->
        <button class="btn-profile" onclick="window.location.href='tdetails.php'">Your Profile</button>
        <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
    </div>
    <script>
    // students list for chat
    var STUDENTS = <?php echo json_encode($studentsList, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?> || [];

    (function(){
        // WhatsApp-like direct chat (teacher page) - replaced old dynamic modal with improved behaviour
        var poller = null; var overlay = null;
        function escapeHtml(str){ return String(str||'').replace(/[&<>'\"]/g,function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;',"'":"&#39;"}[m];}); }

        function renderMsgs(container, msgs, meType, meId){ container.innerHTML=''; msgs.forEach(function(m){ var mine = (m.from_type===meType && String(m.from_id)===String(meId)); var wrapper=document.createElement('div'); wrapper.style.display='flex'; wrapper.style.justifyContent = mine ? 'flex-end' : 'flex-start'; wrapper.style.marginBottom='8px'; var bubble=document.createElement('div'); bubble.style.maxWidth='75%'; bubble.style.padding='10px'; bubble.style.borderRadius='10px'; bubble.style.background = mine ? '#dcf8c6' : '#fff'; bubble.innerHTML = '<div style="font-size:12px;color:#666;margin-bottom:6px">'+escapeHtml(m.from_type+' · '+(m.from_id||''))+' · '+escapeHtml(m.created_at||'')+'</div><div style="white-space:pre-wrap">'+escapeHtml(m.message||'')+'</div>'; wrapper.appendChild(bubble); container.appendChild(wrapper); }); container.scrollTop = container.scrollHeight; }

        window.openChatModal = function(partnerType){
            try{
                if(overlay){ overlay.remove(); overlay=null; }
                overlay = document.createElement('div'); overlay.style.position='fixed'; overlay.style.left=0; overlay.style.top=0; overlay.style.right=0; overlay.style.bottom=0; overlay.style.background='rgba(0,0,0,0.45)'; overlay.style.zIndex=14000;
                var modal = document.createElement('div'); modal.style.width='680px'; modal.style.maxWidth='94%'; modal.style.borderRadius='10px'; modal.style.background='#fff'; modal.style.padding='12px'; modal.style.boxShadow='0 20px 60px rgba(0,0,0,0.25)'; modal.style.maxHeight='80vh'; modal.style.overflow='hidden';
                var header = document.createElement('div'); header.style.display='flex'; header.style.justifyContent='space-between'; header.style.alignItems='center'; var h = document.createElement('h4'); h.innerText='Chat with Student'; h.style.margin='0'; header.appendChild(h); var cb = document.createElement('button'); cb.innerText='✕'; cb.style.border='none'; cb.style.background='transparent'; cb.style.cursor='pointer'; cb.onclick = closeModal; header.appendChild(cb); modal.appendChild(header);
                var sel = document.createElement('select'); sel.style.marginTop='8px'; sel.style.padding='8px'; sel.style.borderRadius='6px'; var ph = document.createElement('option'); ph.value=''; ph.text='Select student...'; sel.appendChild(ph);
                STUDENTS.forEach(function(s){ var opt=document.createElement('option'); opt.value = s.roll_number || s.roll_no || s.rollNumber || s.id; opt.text = s.name || opt.value; sel.appendChild(opt); }); modal.appendChild(sel);
                var msgArea = document.createElement('div'); msgArea.style.marginTop='8px'; msgArea.style.height='360px'; msgArea.style.overflow='auto'; msgArea.style.padding='10px'; msgArea.style.border='1px solid #eee'; msgArea.style.borderRadius='8px'; modal.appendChild(msgArea);
                var inputRow = document.createElement('div'); inputRow.style.display='flex'; inputRow.style.gap='8px'; inputRow.style.marginTop='8px'; var input = document.createElement('input'); input.type='text'; input.placeholder='Type a message'; input.style.flex='1'; input.style.padding='10px'; input.style.borderRadius='8px'; var send = document.createElement('button'); send.innerText='Send'; send.style.padding='10px 14px'; send.style.borderRadius='8px'; send.style.background='#0b5fff'; send.style.color='#fff'; send.style.border='none'; send.style.cursor='pointer'; inputRow.appendChild(input); inputRow.appendChild(send); modal.appendChild(inputRow);
                overlay.appendChild(modal); document.body.appendChild(overlay);

                function fetchMsgs(){ var otherId = sel.value; if(!otherId) return; fetch('api/get_messages.php?other_type=student&other_id='+encodeURIComponent(otherId)).then(r=>r.json()).then(function(j){ if(j && j.success){ renderMsgs(msgArea, j.messages||[], '<?php echo addslashes($_SESSION['user_type'] ?? ''); ?>','<?php echo addslashes($_SESSION['user_id'] ?? ''); ?>'); } }); }
                send.onclick = function(){ var txt = input.value.trim(); var other = sel.value; if(!other){ alert('Select a student'); return; } if(!txt) return; send.disabled=true; // optimistic echo
                    var tmp = { message: txt, from_type: '<?php echo addslashes($_SESSION['user_type'] ?? ''); ?>', from_id: '<?php echo addslashes($_SESSION['user_id'] ?? ''); ?>', message_id: 'tmp_'+Date.now(), created_at: (new Date()).toISOString() };
                    renderMsgs(msgArea, (msgArea._cached||[]).concat([tmp]), '<?php echo addslashes($_SESSION['user_type'] ?? ''); ?>','<?php echo addslashes($_SESSION['user_id'] ?? ''); ?>');
                    fetch('api/send_message.php',{ method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ to_type: 'student', to_id: other, message: txt }) }).then(r=>r.json()).then(function(j){ send.disabled=false; if(j && j.success){ input.value=''; fetchMsgs(); } else { alert('Send failed: '+(j && j.message? j.message : '')); } }).catch(function(){ send.disabled=false; alert('Network error'); }); };
                sel.addEventListener('change', function(){ fetchMsgs(); });
                if(poller) clearInterval(poller); poller = setInterval(fetchMsgs,2500); setTimeout(fetchMsgs,300);
                function closeModal(){ try{ if(poller) clearInterval(poller); poller=null; if(overlay){ overlay.remove(); overlay=null; } }catch(e){} }
            }catch(e){ console.warn(e); }
        };
    })();
    </script>

    <!-- Server-backed Chat Modal for Teacher -->
    <div id="serverChatModalT" class="modal-overlay-chat" style="display:none;">
        <div class="modal-chat" role="dialog" aria-modal="true">
            <div class="modal-header">
                <div id="serverChatTitleT" style="font-weight:700">Group Chat</div>
                <div style="display:flex;gap:8px;align-items:center">
                    <button class="close-btn" onclick="closeServerChatModalTeacher()">✕</button>
                </div>
            </div>
            <div style="margin-top:8px;">
                <select id="studentSelectServerT" style="padding:8px;border-radius:6px;margin-bottom:8px;display:block;width:100%">
                    <option value="">-- Select student --</option>
                </select>
                <label style="display:block;margin-bottom:6px"><input id="broadcastAllTServer" type="checkbox" /> Send to all students</label>
                <label style="display:block;margin-bottom:8px"><input id="broadcastAllBothTServer" type="checkbox" /> Send to all teachers & students</label>
                <div id="messagesBoxServerT" style="background:#fff;border-radius:10px;padding:12px;height:320px;overflow:auto;border:1px solid #d0e3fa"></div>
                <div style="display:flex;gap:8px;margin-top:8px;align-items:center">
                    <input id="chatInputServerT" type="text" placeholder="Type a message" style="flex:1;padding:8px;border-radius:8px;border:1px solid #d0e3f0" />
                    <input id="fileInputServerT" type="file" />
                    <button class="save-btn" id="sendServerBtnT">Send</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Server chat for teacher page
        var STUDENTS_SERVER = <?php echo json_encode($studentsList, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?> || [];
        (function(){
            var pollerT = null; var modalT = document.getElementById('serverChatModalT');
            var messagesBox = null; var studentSelect = null; var sendBtn = null; var broadcastAll = null; var broadcastBoth = null;
            function escapeHtml(s){ return String(s||'').replace(/[&<>'"]/g,function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[m];}); }

            window.openServerChatModalTeacher = function(){
                try{
                    if(!modalT) modalT = document.getElementById('serverChatModalT');
                    modalT.style.display='flex'; modalT.classList.add('open');
                    messagesBox = document.getElementById('messagesBoxServerT'); studentSelect = document.getElementById('studentSelectServerT'); sendBtn = document.getElementById('sendServerBtnT'); broadcastAll = document.getElementById('broadcastAllTServer'); broadcastBoth = document.getElementById('broadcastAllBothTServer');
                    studentSelect.innerHTML = '<option value="">-- Select student --</option>';
                    STUDENTS_SERVER.forEach(function(s){ var opt=document.createElement('option'); opt.value = s.roll_number || s.roll_no || s.rollNumber || s.id; opt.text = s.name || opt.value; studentSelect.appendChild(opt); });

                    function fetchMsgs(){
                        if(broadcastAll.checked || broadcastBoth.checked){
                            fetch('api/get_group_messages.php').then(r=>r.json()).then(function(j){ if(j && j.success){ renderMsgs(j.messages||[]); } });
                        } else { var other = studentSelect.value; if(!other) return; fetch('api/get_messages.php?other_type=student&other_id='+encodeURIComponent(other)).then(r=>r.json()).then(function(j){ if(j && j.success){ renderMsgs(j.messages||[]); } }); }
                    }

                    function renderMsgs(msgs){ if(!messagesBox) return; messagesBox.innerHTML=''; msgs.forEach(function(m){ var mine = (m.from_type=== '<?php echo addslashes($_SESSION['user_type'] ?? ''); ?>' && String(m.from_id) === String('<?php echo addslashes($_SESSION['user_id'] ?? ''); ?>')); var div = document.createElement('div'); div.style.marginBottom='10px'; div.dataset.msgId = m.id || m.message_id || ''; var who = (mine? 'You' : (m.from_type+' '+(m.from_id||''))); var time = m.created_at || ''; var inner = '<div style="font-size:12px;color:#666;margin-bottom:4px">'+escapeHtml(who)+' · '+escapeHtml(time)+'</div>'; inner += '<div style="background:'+(mine?'#e6f7ff':'#f1f5f9')+';padding:8px;border-radius:8px;max-width:78%;">'+escapeHtml(m.message||m.content||'')+'</div>'; div.innerHTML = inner; div.onclick = function(e){ e.stopPropagation(); var id = div.dataset.msgId; if(!id) return; var choose = confirm('Delete message? OK=Delete for me, Cancel=Delete for everyone (if sender)'); if(choose){ deleteServerMessageT(id,'me'); } else { if(mine) deleteServerMessageT(id,'everyone'); else alert('Only sender can delete for everyone'); } }; messagesBox.appendChild(div); }); messagesBox.scrollTop = messagesBox.scrollHeight; }

                    if(sendBtn) sendBtn.onclick = function(){ var txt = document.getElementById('chatInputServerT').value.trim(); if(!txt) return; sendBtn.disabled=true; var payload={ message: txt };
                        if(broadcastBoth.checked){ payload.to_type='group'; payload.to_id='all'; }
                        else if(broadcastAll.checked){ payload.to_type='group'; payload.to_id='teachers'; }
                        else { payload.to_type='student'; payload.to_id = studentSelect.value; if(!payload.to_id){ alert('Select a student or choose a broadcast option'); sendBtn.disabled=false; return; } }
                        fetch('api/send_message.php',{ method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) }).then(r=>r.json()).then(function(j){ sendBtn.disabled=false; if(j && j.success){ document.getElementById('chatInputServerT').value=''; fetchMsgs(); } else { alert('Send failed: '+(j && j.message? j.message : '')); } }).catch(function(){ sendBtn.disabled=false; alert('Network error'); }); };

                    if(pollerT) clearInterval(pollerT); pollerT = setInterval(fetchMsgs,2500); setTimeout(fetchMsgs,200);
                }catch(e){ console.warn('openServerChatModalTeacher', e); }
            };

            window.closeServerChatModalTeacher = function(){ try{ if(pollerT) clearInterval(pollerT); pollerT=null; if(modalT){ modalT.classList.remove('open'); modalT.style.display='none'; } }catch(e){} };

            function deleteServerMessageT(messageId, action){ fetch('api/delete_message.php',{ method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ message_id: messageId, action: action }) }).then(r=>r.json()).then(function(j){ if(j && j.success){ try{ if(messagesBox) messagesBox.querySelector('[data-msg-id="'+messageId+'"]')?.remove(); }catch(e){} } else { alert('Delete failed'); } }); }

        })();
    </script>
</body>
</html>
