<?php require_once __DIR__ . '/includes/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Store</title>
  <style> body{font-family:'Segoe UI',sans-serif;margin:0;padding:20px} .card{max-width:900px;margin:auto;background:#fff;padding:16px;border-radius:8px} </style>
</head>
<body>

<script src="includes/storage-shim.js"></script>

<div class="card">
  <h2>Store</h2>
  <div id="list"></div>
</div>

<script>
  function render(){
    const users = Object.keys(localStorage).map(k=>{ try{return JSON.parse(localStorage.getItem(k))}catch(e){return null} }).filter(Boolean);
    document.getElementById('list').innerText = 'Profiles: ' + users.length;
  }
  window.onload = render;
</script>

</body>
</html>
