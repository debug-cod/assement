<?php
// Login controller for petWatch
// This file handles user login

// Start session to track login status
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set page title
$page_title = "Login - petWatch";

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Simple user validation (for testing without database)
    $valid_users = [
        'A' => 'P@ssw0rd123!Xy', // Strong password for user A
        'C' => '123456' // Weak password for user C
    ];

    // Check if username exists
    if (array_key_exists($username, $valid_users)) {
        // Check if password matches
        if ($password === $valid_users[$username]) {
            // Login successful
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;

            // Redirect to home page
            header("Location: ../index.php");
            exit();
        } else {
            $error_message = "Wrong password. Please try again.";
        }
    } else {
        $error_message = "Username not found.";
    }
}


// Include login view
include '../views/login.phtml';
?>