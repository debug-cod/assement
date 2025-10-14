<?php
// Help controller for petWatch
// This file provides user guidance and instructions

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set page title
$page_title = "Help & User Guide - petWatch";

// Include view
include '../views/help.phtml';
?>
