<?php
// controllers/report_sighting.php

// Start session and check if user is logged in
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a regular user
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$page_title = "Report Sighting - petWatch";
$error_message = '';
$success_message = '';

// Include models
require_once '../Model/SightingModel.php';
require_once '../Model/UserModel.php';

// Get database connection
$db = getDbConnection();
$sightingModel = new SightingModel($db);

// Get lost pets for the dropdown
$lostPets = $sightingModel->getLostPets();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pet_id = isset($_POST['pet_id']) ? (int)$_POST['pet_id'] : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $latitude = isset($_POST['latitude']) ? trim($_POST['latitude']) : '';
    $longitude = isset($_POST['longitude']) ? trim($_POST['longitude']) : '';
    $timestamp = isset($_POST['timestamp']) ? trim($_POST['timestamp']) : '';
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // Validate required fields - 使用简单的验证
    if (empty($pet_id) || empty($latitude) || empty($longitude) || empty($timestamp)) {
        $error_message = "Please fill in all required fields.";
    } else {
        if ($action === 'save') {
            // Add new sighting - 移除了reward参数
            if ($sightingModel->addSighting($pet_id, $_SESSION['user_id'], $comment, $latitude, $longitude, $timestamp, 0)) {
                $success_message = "Sighting reported successfully!";
            } else {
                $error_message = "Failed to report sighting. Please try again.";
            }
        }
        // If action is delete, just clear the form (no database operation)
    }
}

// Include view
include '../views/report_sighting.phtml';
?>