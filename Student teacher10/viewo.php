<?php
require_once __DIR__ . '/database/config.php';
require_once __DIR__ . '/database/db_operations.php';
session_start();

// Handle logout if requested
if (isset($_GET['logout'])) {
    if (isset($_SESSION)) session_destroy();
    header('Location: log.php');
    exit;
}

$students = getAllStudents();
$isTeacher = (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'teacher');
$currentTeacherId = $isTeacher ? $_SESSION['user_id'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Stored Student Profiles</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 30px;
      background: linear-gradient(to right, #83a4d4, #b6fbff);
    }

    h2 {
      text-align: center;
      color: #fff;
      margin-bottom: 30px;
    }

    #profilesContainer {
      display: flex;
      flex-direction: column;
      gap: 20px;
      max-width: 900px;
      margin: auto;
    }

    .student-card {
      display: flex;
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
      color: #333;
      align-items: center;
    }

    .student-photo {
      width: 130px;
      height: 130px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 25px;
      border: 3px solid white;
    }

    .student-info {
      flex-grow: 1;
    }

    .student-info p {
      margin: 6px 0;
      font-size: 15px;
    }

    .remarks-input {
      display: none;
      margin-top: 10px;
      padding: 8px;
      width: 100%;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    .logout-btn {
      display: block;
      margin: 40px auto 0;
      padding: 12px 25px;
      background-color: #dc3545;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .logout-btn:hover {
      background-color: #b02a37;
    }
  </style>
</head>

<body>

<script src="includes/storage-shim.js"></script>

  <h2>Stored Student Profiles</h2>
  <div class="buttons" style="width:100%;max-width:900px;display:flex;justify-content:flex-end;gap:12px;margin-bottom:12px;">
    <button class="viewo-btn" onclick="goToViewO()">View Other Profiles</button>
    <button class="teachers-btn" onclick="viewTeachers()">Teachers</button>
    <button class="viewo-btn" onclick="location.href='sdetails.php'">My Profile</button>
  </div>
  <div id="profilesContainer">
    <?php if (!empty($students)): ?>
        <?php foreach ($students as $s):
            $photo = !empty($s['photo_url']) ? $s['photo_url'] : 'https://via.placeholder.com/130?text=No+Photo';
            $name = $s['name'] ?? ($s['first_name'] . ' ' . ($s['last_name'] ?? ''));
            $roll = $s['roll_number'] ?? $s['roll_no'] ?? '';
        ?>
        <div class="student-card">
            <img class="student-photo" src="<?php echo htmlspecialchars($photo); ?>" alt="<?php echo htmlspecialchars($name); ?>">
            <div class="student-info">
                <p style="font-weight:700;color:#fff"><?php echo htmlspecialchars($name); ?></p>
                <p style="color:#fff">Roll No.: <?php echo htmlspecialchars($roll); ?></p>
          <?php if ($isTeacher): ?>
            <div style="margin-top:8px">
              <button style="padding:8px 10px;border-radius:6px;border:none;background:#0b5fff;color:#fff;cursor:pointer" onclick="openRemarkModal('<?php echo htmlspecialchars($roll, ENT_QUOTES); ?>','<?php echo htmlspecialchars($name, ENT_QUOTES); ?>')">Add Remark</button>
            </div>
          <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="max-width:900px;margin:auto;color:#fff;text-align:center;padding:20px;background:rgba(0,0,0,0.05);border-radius:8px;">No student profiles found.</div>
    <?php endif; ?>
  </div>
  <button class="logout-btn" onclick="logout()">Logout</button>

  <script>
    // expose current teacher id for JS if available
    var CURRENT_TEACHER_ID = <?php echo json_encode($currentTeacherId); ?>;

    // Remark modal helpers
    function openRemarkModal(roll, name){
      const modalId = 'remarkModal';
      let modal = document.getElementById(modalId);
      if(!modal){
        modal = document.createElement('div'); modal.id = modalId;
        modal.style.position = 'fixed'; modal.style.left='0'; modal.style.top='0'; modal.style.right='0'; modal.style.bottom='0'; modal.style.display='flex'; modal.style.alignItems='center'; modal.style.justifyContent='center'; modal.style.background='rgba(0,0,0,0.45)'; modal.style.zIndex='12000';
        modal.innerHTML = `<div style="background:#fff;padding:16px;border-radius:10px;max-width:640px;width:94%"><div style="display:flex;justify-content:space-between;align-items:center"><strong id="remarkTitle"></strong><button onclick="closeRemarkModal()" style="background:#eee;border:none;padding:6px 8px;border-radius:6px;cursor:pointer">Close</button></div><div style="margin-top:10px"><textarea id="remarkText" style="width:100%;height:120px;padding:8px;border:1px solid #ccc;border-radius:6px"></textarea></div><div style="display:flex;justify-content:flex-end;margin-top:10px"><button onclick="submitRemark()" style="background:#0b5fff;color:#fff;border:none;padding:8px 10px;border-radius:6px">Save Remark</button></div></div>`;
        document.body.appendChild(modal);
      }
      document.getElementById('remarkTitle').textContent = 'Add remark for ' + name + ' (' + roll + ')';
      modal.dataset.roll = roll;
      modal.style.display = 'flex';
      document.getElementById('remarkText').value = '';
    }

    function closeRemarkModal(){ const m=document.getElementById('remarkModal'); if(m) m.style.display='none'; }

    async function submitRemark(){
      const modal = document.getElementById('remarkModal'); if(!modal) return;
      const roll = modal.dataset.roll; const text = document.getElementById('remarkText').value.trim();
      if(!text){ alert('Enter a remark'); return; }
      try{
        const resp = await fetch('api/add_remark.php', { method: 'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ student_roll: roll, remark: text }) });
        const j = await resp.json();
        if(j && j.success){ alert('Remark saved'); closeRemarkModal(); } else { alert('Failed: ' + (j.message||'unknown')); }
      }catch(e){ alert('Network error'); }
    }
    function logout() {
      // destroy server session by redirecting with logout flag
      window.location.href = window.location.pathname + '?logout=1';
    }

    function goToViewO() {
      window.location.href = 'viewo.php';
    }

    function viewTeachers() {
      try { localStorage.setItem('fromTStoreSource', 'viewo'); } catch (e) {}
      window.location.href = 'tstore1.php';
    }
  </script>

</body>
</html>
