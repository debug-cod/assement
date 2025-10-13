<?php
// petWatch website - home page
// This file shows the home page of petWatch

// Start session for user login
session_start();

// Set page title variable
$page_title = "petWatch - Find Missing Pets";

// Check if user has weak password warning
if (isset($_SESSION['password_weak']) && $_SESSION['password_weak']) {
    // Show warning message (you can display this in the header or somewhere)
    $show_password_warning = true;
}

// Include the home page view
include 'views/home.phtml';

// End of index.php file
?>