<?php require_once __DIR__ . '/includes/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Store</title>
</head>
<body>

<script src="includes/storage-shim.js"></script>

<h2>Teacher Store</h2>
<div id="out"></div>

<script>
  function render(){
    const from = localStorage.getItem('fromTStoreSource') || 'unknown';
    document.getElementById('out').innerText = 'Opened from: ' + from;
  }
  window.onload = render;
</script>

</body>
</html>
