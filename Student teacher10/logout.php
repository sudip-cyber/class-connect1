<?php
// Logout script: destroy session, clear cookies, redirect to index.php
session_start();
// Optionally log audit here if needed
// Clear session array
$_SESSION = [];
// If session uses cookies, remove the cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
// Destroy the session
session_destroy();
// Redirect to index
header('Location: index.php');
exit;
?>