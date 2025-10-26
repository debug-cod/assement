<?php
/**
 * Logout Controller for petWatch Application
 *
 * This controller handles user logout by:
 * - Clearing "Remember Me" cookies
 * - Destroying session data
 * - Redirecting to home page
 */

// Start session to access current session data
session_start();

/**
 * Clear "Remember Me" Cookie
 *
 * Delete that cookies from hour ago (3600 sec)
 * This ensures users stay logged out even if they return later
 */
if (isset($_COOKIE['remember_username'])) {
    setcookie('remember_username', '', time() - 3600, '/');
}

/**
 * Session Destruction
 *
 * Clear all session variables and destroy the session
 * This completely logs the user out of the application
 */
$_SESSION = array(); // Clear all session data
session_destroy();   // Destroy the session completely

/**
 * Redirect to Home Page
 *
 * After logout, redirect user to the application home page
 * where they will see the public view (not logged in)
 */
header("Location: ../index.php");
exit();
?>