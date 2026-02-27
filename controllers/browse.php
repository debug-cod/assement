<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

require_once '../Model/PetModel.php';
require_once '../Model/UserModel.php';

$db = getDbConnection();
$petModel = new PetModel($db);

// Extract parameters
$search = $_GET['search'] ?? '';
$species = $_GET['species'] ?? '';
$color = $_GET['color'] ?? '';
$gender = $_GET['gender'] ?? '';
$sort = $_GET['sort'] ?? 'late';
$page = (int)($_GET['page'] ?? 1);
$perPage = 6; // Set to a reasonable number for your layout

$filters = [
    'species' => $species,
    'color' => $color,
    'gender' => $gender,
    'sort' => $sort
];

// Fetch data
$pets = $petModel->getPets($search, $filters, $page, $perPage);
$totalPets = $petModel->getTotalPets($search, $filters);
$totalPages = ceil($totalPets / $perPage);

$page_title = "Browse Pets - petWatch";

// IMPORTANT: Ensure the view has access to these variables
include '../views/browse.phtml';