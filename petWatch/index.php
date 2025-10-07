<?php
// petWatch website - home page
// This file shows the home page of petWatch

// Start session for user login (will be used later)
session_start();

// Set page title
$page_title = "petWatch - Find Missing Pets";

// Include the home page view
include 'views/template/home.phtml';

// End of index.php file
?>