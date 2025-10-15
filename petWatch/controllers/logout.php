<?php
// Logout controller for petWatch
// This file handles user logout

// Start session
session_start();

// 1. Clear 'Remember Me' Cookie
// Delete the persistent login cookie by setting its expiration time in the past
if (isset($_COOKIE['remember_username'])) {
    setcookie('remember_username', '', time() - 3600, '/');
}

// 2. Clear all session variables and destroy the session
$_SESSION = array();
session_destroy();

// 3. Redirect to home page
header("Location: ../index.php");
exit();
?>