<?php
// controllers/report_pet.php

// Start session and check if user is logged in
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a pet owner
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'pet_owner') {
    header("Location: login.php");
    exit();
}

$page_title = "Report New Pet - petWatch";
$error_message = '';
$success_message = '';

// Include models
require_once '../Model/PetOwnerModel.php';
require_once '../Model/UserModel.php';

// Get database connection
$db = getDbConnection();
$petOwnerModel = new PetOwnerModel($db);

// Check if this is for modification
$edit_mode = false;
$pet_data = null;
if (isset($_GET['edit_id'])) {
    $edit_mode = true;
    $pet_id = (int)$_GET['edit_id'];
    $pet_data = $petOwnerModel->getPetById($pet_id, $_SESSION['user_id']);

    if (!$pet_data) {
        $error_message = "Pet not found or you don't have permission to edit it.";
        $edit_mode = false;
    } else {
        $page_title = "Edit Pet - petWatch";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $species = isset($_POST['species']) ? trim($_POST['species']) : '';
    $breed = isset($_POST['breed']) ? trim($_POST['breed']) : '';
    $color = isset($_POST['color']) ? trim($_POST['color']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $age = isset($_POST['age']) ? (float)$_POST['age'] : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // Handle file upload for photo
    $photo_url = 'default-pet.png'; // default image

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../images/pet-image/';

        // Ensure the directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Generate unique filename
        $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo_name = 'pet_' . uniqid() . '.' . $file_extension;
        $photo_path = $upload_dir . $photo_name;

        // Check if file is an image
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($file_extension), $allowed_types)) {
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
                $photo_url = $photo_name;
            } else {
                $error_message = "Failed to upload photo. Please try again.";
            }
        } else {
            $error_message = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    } elseif ($edit_mode && $pet_data) {
        // Keep existing photo if editing and no new photo uploaded
        $photo_url = $pet_data['photo_url'];
    }

    // Validate required fields
    if (empty($name) || empty($species) || empty($breed) || empty($color) || empty($status) || empty($description) || empty($gender)) {
        $error_message = "Please fill in all required fields.";
    } else {
        if ($action === 'save') {
            if ($edit_mode && $pet_data) {
                // Update existing pet
                if ($petOwnerModel->updatePet($pet_data['id'], $_SESSION['user_id'], $name, $species, $breed, $color, $photo_url, $status, $description, $gender, $age)) {
                    $success_message = "Pet updated successfully!";
                } else {
                    $error_message = "Failed to update pet. Please try again.";
                }
            } else {
                // Add new pet
                if ($petOwnerModel->addPet($name, $species, $breed, $color, $photo_url, $status, $description, $_SESSION['user_id'], $gender, $age)) {
                    $success_message = "Pet reported successfully!";
                } else {
                    $error_message = "Failed to report pet. Please try again.";
                }
            }
        }
        // If action is clear, just reset the form
    }
}

// Include view
include '../views/report_pet.phtml';
?>