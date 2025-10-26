<?php
/**
 * Manage Pets Controller for petWatch Application
 *
 * This controller allows pet owners to:
 * - View their registered pets
 * - Search through their pets
 * - Delete pets from their account
 * - Paginate through their pet listings
 */

// Start session and check if user is logged in with appropriate role
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Authorization Check
 *
 * Verify that user is logged in and has 'pet_owner' role
 * If not, redirect to login page
 */
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'pet_owner') {
    header("Location: login.php");
    exit();
}

// Set the page title for browser tab and header display
$page_title = "Manage My Pets - petWatch";

// Initialize message variables for user feedback
$error_message = '';
$success_message = '';

// Include required model classes for pet and user data operations
require_once '../Model/PetOwnerModel.php';
require_once '../Model/UserModel.php';

// Create database connection and initialize PetOwnerModel
$db = getDbConnection();
$petOwnerModel = new PetOwnerModel($db);

/**
 * Search Functionality
 * Handles search functionality using the GET method.
 * Retrieves and trims the 'search' query parameter if provided.
 */
$search = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
}

/**
 * Pagination Setup
 *
 * Determine current page and number of items per page for results display
 */
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 9; // Number of pets to display per page

/**
 * Delete Pet Functionality
 *
 * Handle pet deletion when delete_id parameter is provided in URL
 * Only allows deletion if the pet belongs to the current user
 */
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    if ($petOwnerModel->deletePet($delete_id, $_SESSION['user_id'])) {
        $success_message = "Pet deleted successfully!";
    } else {
        $error_message = "Failed to delete pet. Please try again.";
    }
}

/**
 * Data Retrieval
 *
 * Get paginated list of pets belonging to the current user
 * and calculate total pages for pagination display
 */
$userPets = $petOwnerModel->getPetsByOwner($_SESSION['user_id'], $search, $page, $perPage);
$totalPets = $petOwnerModel->getTotalPetsByOwner($_SESSION['user_id'], $search);
$totalPages = ceil($totalPets / $perPage);

// Include the view template to render the manage pets page
include '../views/manage_pets.phtml';
?>
