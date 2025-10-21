<?php
// controllers/manage_pets.php

// Start session and check if user is logged in
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a pet owner
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'pet_owner') {
    header("Location: login.php");
    exit();
}

$page_title = "Manage My Pets - petWatch";
$error_message = '';
$success_message = '';

// Include models
require_once '../Model/PetOwnerModel.php';
require_once '../Model/UserModel.php';

// Get database connection
$db = getDbConnection();
$petOwnerModel = new PetOwnerModel($db);

// Handle search
$search = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
}

// Handle pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 9;

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    if ($petOwnerModel->deletePet($delete_id, $_SESSION['user_id'])) {
        $success_message = "Pet deleted successfully!";
    } else {
        $error_message = "Failed to delete pet. Please try again.";
    }
}

// Get user's pets with pagination
$userPets = $petOwnerModel->getPetsByOwner($_SESSION['user_id'], $search, $page, $perPage);
$totalPets = $petOwnerModel->getTotalPetsByOwner($_SESSION['user_id'], $search);
$totalPages = ceil($totalPets / $perPage);

// Include view
include '../views/manage_pets.phtml';
?>
