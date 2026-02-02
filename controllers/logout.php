<?php

  //Logout page - logs user out and clears session


// Start session to clear it
session_start();

// Clear remember me cookie
if (isset($_COOKIE['remember_username'])) {
    setcookie('remember_username', '', time() - 3600, '/');
}

// Clear session data
$_SESSION = array();
session_destroy();

// Go to home page
header("Location: ../index.php");
exit();
?>