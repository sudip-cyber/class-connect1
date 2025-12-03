<?php
require_once __DIR__ . '/../database/config.php';
$db = null;
try{
    $db = getDB();
    $sql = file_get_contents(__DIR__ . '/../database/init_messages.sql');
    $db->exec($sql);
    echo "Created/verified messages table\n";
}catch(Exception $e){
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
return 0;
?>