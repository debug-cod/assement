<?php

  //Help page - shows user guide and instructions


// Start session for user login state
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$page_title = "Help & User Guide - petWatch";

// Show the help page
include '../views/help.phtml';
?>