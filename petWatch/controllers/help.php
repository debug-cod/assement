<?php
/**
 * Help Controller for petWatch Application
 *
 * This controller handles the display of user guidance and instructions.
 * It serves as a resource for users to learn how to use the petWatch platform.
 */

// Start session if not already started to maintain user state
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set the page title for browser tab and header display
$page_title = "Help & User Guide - petWatch";

// Include the view template to render the help page content
include '../views/help.phtml';
?>
