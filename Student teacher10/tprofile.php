<?php require_once __DIR__ . '/includes/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Profile</title>
</head>
<body>

<script src="includes/storage-shim.js"></script>

<h2>Teacher Profile</h2>
<div>
  <label>ID</label><input id="id"><br>
  <label>Name</label><input id="name"><br>
  <button onclick="save()">Save</button>
</div>

<script>
  function save(){
    const id = document.getElementById('id').value.trim();
    const name = document.getElementById('name').value.trim();
    localStorage.setItem('teacher_' + id, JSON.stringify({ id: id, name: name }));
    localStorage.setItem('currentUserId', id);
    window.location.href = 'tdetails.php';
  }
</script>

</body>
</html>
