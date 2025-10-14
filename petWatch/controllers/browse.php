<?php
// Browse controller for petWatch
// This file handles pet browsing and searching

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set page title
$page_title = "Browse Pets - petWatch";

// Include model
require_once '../Model/PetModel.php';

// Create model instance
$petModel = new PetModel('../database/petwatch.sqlite');

// Get search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$species = isset($_GET['species']) ? $_GET['species'] : '';
$color = isset($_GET['color']) ? $_GET['color'] : '';
$gender = isset($_GET['gender']) ? $_GET['gender'] : '';
$age = isset($_GET['age']) ? $_GET['age'] : '';
$sightings = isset($_GET['sightings']) ? $_GET['sightings'] : '';
$reward = isset($_GET['reward']) ? $_GET['reward'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'late';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Prepare filters array
$filters = [
    'species' => $species,
    'color' => $color,
    'gender' => $gender,
    'age' => $age,
    'sightings' => $sightings,
    'reward' => $reward,
    'sort' => $sort
];

// Clean empty filters
$filters = array_filter($filters);

// Get pets data
$pets = $petModel->getPets($search, $filters, $page, 10);
$totalPets = $petModel->getTotalPets($search, $filters);
$totalPages = ceil($totalPets / 10);

// Include view
include '../views/browse.phtml';
?>