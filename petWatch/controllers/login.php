<?php
// Login controller for petWatch
// This file handles user login using MVC and database/security principles

// 1. Session and Setup
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$page_title = "Login - petWatch";
$error_message = '';

// Include the Model and the database connection
require_once '../Model/UserModel.php';
$db = getDbConnection(); // Assume this function returns a PDO object (from UserModel.php for simplicity)
$userModel = new UserModel($db);

// 2. Check for 'Remember Me' Cookie for Auto-Login
if (!isset($_SESSION['loggedin']) && isset($_COOKIE['remember_username'])) {
    $username = $_COOKIE['remember_username'];
    $user = $userModel->getUserByUsername($username);
    if ($user) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_id'] = $user['id']; // Store user ID for future use

        // Redirect to home page
        header("Location: ../index.php");
        exit();
    }
    // If the cookie is invalid, delete it
    setcookie('remember_username', '', time() - 3600, '/');
}

// 3. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember_me = isset($_POST['remember_me']); // Check if "Remember Me" was checked

    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        // a. Retrieve user from the database
        $user = $userModel->getUserByUsername($username);

        if ($user) {
            // b. Verify the password - using plain text comparison since passwords are stored in plain text
            // In a real application, we would use password_hash and password_verify for security
            if ($password === $user['password_hash']) {
                // Login successful
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_id'] = $user['id']; // Store user ID for future use

                // c. Handle 'Remember Me' (Cookie)
                if ($remember_me) {
                    // Set cookie to remember the username for 30 days
                    $cookie_name = 'remember_username';
                    $cookie_value = $user['username'];
                    $expiry_time = time() + (86400 * 30); // 30 days
                    setcookie($cookie_name, $cookie_value, [
                        'expires' => $expiry_time,
                        'path' => '/',
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);
                }

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
}

// 4. Include login view
include '../views/login.phtml';
?>