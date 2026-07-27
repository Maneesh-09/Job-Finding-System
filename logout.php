<?php
// Initialize session workspace logs
session_start();

// Unset all active session data matrix variables
$_SESSION = array();

// Destroy session cookies securely if active in parameters
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Completely terminate corporate/administrative workspace sessions
session_destroy();  

// Route target client identity back to gateway interface indexes
header("Location: index.php");
exit;
?>