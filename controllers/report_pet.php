<?php
/**
 * Report Pet Controller for petWatch Application
 *
 * This controller allows pet owners to:
 * - Report new lost/found pets
 * - Edit existing pet information
 * - Upload pet photos
 * - Manage pet details and status
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
$page_title = "Report New Pet - petWatch";

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
 * Edit Mode Detection
 *
 * Check if we're in edit mode (editing existing pet) or create mode (adding new pet)
 * Retrieve existing pet data if in edit mode
 */
$edit_mode = false;
$pet_data = null;
if (isset($_GET['edit_id'])) {
    $edit_mode = true;
    $pet_id = (int)$_GET['edit_id'];

    // Retrieve pet data, ensuring the pet belongs to the current user
    $pet_data = $petOwnerModel->getPetById($pet_id, $_SESSION['user_id']);

    if (!$pet_data) {
        $error_message = "Pet not found or you don't have permission to edit it.";
        $edit_mode = false;
    } else {
        $page_title = "Edit Pet - petWatch"; // Update title for edit mode
    }
}

/**
 * Form Submission Handling
 *
 * Process pet data when form is submitted (both new pets and edits)
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize form inputs
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $species = isset($_POST['species']) ? trim($_POST['species']) : '';
    $breed = isset($_POST['breed']) ? trim($_POST['breed']) : '';
    $color = isset($_POST['color']) ? trim($_POST['color']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $age = isset($_POST['age']) ? (float)$_POST['age'] : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : ''; // Form action (save/clear)

    /**
     * Photo Upload Handling
     *
     * Process pet photo upload with validation and file type checking
     * Uses default image if no photo is uploaded
     */
    $photo_url = 'default-pet.png'; // Default pet image

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../images/pet-image/';

        // Create upload directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Generate unique filename to prevent overwriting
        $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo_name = 'pet_' . uniqid() . '.' . $file_extension;
        $photo_path = $upload_dir . $photo_name;

        // Validate file type and move uploaded file
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
        // Keep existing photo when editing and no new photo uploaded
        $photo_url = $pet_data['photo_url'];
    }

    // Validate that all required fields are filled
    if (empty($name) || empty($species) || empty($breed) || empty($color) || empty($status) || empty($description) || empty($gender)) {
        $error_message = "Please fill in all required fields.";
    } else {
        if ($action === 'save') {
            if ($edit_mode && $pet_data) {
                // Update existing pet record
                if ($petOwnerModel->updatePet($pet_data['id'], $_SESSION['user_id'], $name, $species, $breed, $color, $photo_url, $status, $description, $gender, $age)) {
                    $success_message = "Pet updated successfully!";
                } else {
                    $error_message = "Failed to update pet. Please try again.";
                }
            } else {
                // Create new pet record
                if ($petOwnerModel->addPet($name, $species, $breed, $color, $photo_url, $status, $description, $_SESSION['user_id'], $gender, $age)) {
                    $success_message = "Pet reported successfully!";
                } else {
                    $error_message = "Failed to report pet. Please try again.";
                }
            }
        }
        // If action is 'clear', simply reset the form (no database operation)
    }
}

// Include the view template to render the pet reporting/editing form
include '../views/report_pet.phtml';
?>