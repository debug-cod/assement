<?php
/**
 * Browse Pets Controller for petWatch Application
 *
 * This controller handles the display and filtering of pet listings.
 * Users can search, filter by various criteria, and paginate through pet results.
 */

// Start session if not already started to maintain user state
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set the page title for browser tab and header display
$page_title = "Browse Pets - petWatch";

// Include required model classes for data operations
require_once '../Model/PetModel.php';
require_once '../Model/UserModel.php'; // Provides database connection functionality

// Create database connection and initialize PetModel for pet data operations
$db = getDbConnection();
$petModel = new PetModel($db); // PetModel now accepts PDO connection instead of file path

// Use the GET method to retrieve all filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';        // Text search term
$species = isset($_GET['species']) ? $_GET['species'] : '';     // Filter by animal species
$color = isset($_GET['color']) ? $_GET['color'] : '';           // Filter by pet color
$gender = isset($_GET['gender']) ? $_GET['gender'] : '';        // Filter by pet gender
$age = isset($_GET['age']) ? $_GET['age'] : '';                 // Filter by pet age
$sightings = isset($_GET['sightings']) ? $_GET['sightings'] : ''; // Filter by number of sightings
$reward = isset($_GET['reward']) ? $_GET['reward'] : '';        // Filter by reward status
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'late';          // Sort parameter (default: latest)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;          // Current page for pagination

// Prepare filters array to consolidate all filter parameters
$filters = [
    'species' => $species,
    'color' => $color,
    'gender' => $gender,
    'age' => $age,
    'sightings' => $sightings,
    'reward' => $reward,
    'sort' => $sort
];

// Remove empty filter values
$filters = array_filter($filters);

// Retrieve paginated pet data based on search criteria and filters
$pets = $petModel->getPets($search, $filters, $page, 10); // Get 10 pets per page

// Get total count of pets matching the criteria for pagination calculation
$totalPets = $petModel->getTotalPets($search, $filters);

// Calculate total number of pages needed for pagination
$totalPages = ceil($totalPets / 10);

// Include the view template to render the browse pets page
include '../views/browse.phtml';
?>