<?php
// Logout controller for petWatch
// This file handles user logout

// Start session
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to home page
header("Location: ../index.php");
exit();
?>