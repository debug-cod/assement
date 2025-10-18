<?php
// controllers/view_sightings.php

// Start session and check if user is logged in
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a regular user
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$page_title = "My Sightings - petWatch";
$error_message = '';
$success_message = '';

// Include models
require_once '../Model/SightingModel.php';
require_once '../Model/UserModel.php';

// Get database connection
$db = getDbConnection();
$sightingModel = new SightingModel($db);

// Handle search
$search = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    if ($sightingModel->deleteSighting($delete_id, $_SESSION['user_id'])) {
        $success_message = "Sighting deleted successfully!";
    } else {
        $error_message = "Failed to delete sighting. Please try again.";
    }
}

// Get user's sightings
$userSightings = $sightingModel->getSightingsByUser($_SESSION['user_id'], $search);

// Include view
include '../views/view_sightings.phtml';
?>