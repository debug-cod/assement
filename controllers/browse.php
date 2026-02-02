<?php
/**
Browse Pets page - shows all pets with search and filters
Used for browsing and searching lost/found pets
*/

// Start session for user login state
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$page_title = "Browse Pets - petWatch";

// Include models for database operations
require_once '../Model/PetModel.php';
require_once '../Model/UserModel.php';

// Setup database and pet model
$db = getDbConnection();
$petModel = new PetModel($db);

// Get all filter parameters from URL
$search = isset($_GET['search']) ? $_GET['search'] : '';
$species = isset($_GET['species']) ? $_GET['species'] : '';
$color = isset($_GET['color']) ? $_GET['color'] : '';
$gender = isset($_GET['gender']) ? $_GET['gender'] : '';
$age = isset($_GET['age']) ? $_GET['age'] : '';
$sightings = isset($_GET['sightings']) ? $_GET['sightings'] : '';
$reward = isset($_GET['reward']) ? $_GET['reward'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'late';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Put all filters in one array
$filters = [
    'species' => $species,
    'color' => $color,
    'gender' => $gender,
    'age' => $age,
    'sightings' => $sightings,
    'reward' => $reward,
    'sort' => $sort
];

// Remove empty filters
$filters = array_filter($filters);

// Get pets with search and filters
$pets = $petModel->getPets($search, $filters, $page, 10);

// Get total count for pagination
$totalPets = $petModel->getTotalPets($search, $filters);
$totalPages = ceil($totalPets / 10);

// Show the browse page
include '../views/browse.phtml';
?>