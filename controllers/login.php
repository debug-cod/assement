<?php
/**
 * Login Controller for petWatch Application
 *
 * This controller handles user authentication, including:
 * - Form-based login with username/password
 * - "Remember Me" functionality using cookies
 * - Session management for authenticated users
 */

// Start session if not already started to maintain user state
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set the page title for browser tab and header display
$page_title = "Login - petWatch";

// Initialize error message variable for user feedback
$error_message = '';

// Include required model from DB
require_once '../Model/UserModel.php';

// Create database connection and initialize UserModel
$db = getDbConnection();
$userModel = new UserModel($db);

/**
 * "Remember Me" Cookie Check
 *
 * If user is not logged in and have vaild cookies call "remember_username",
 * automatically store them in using the stored username.
 */
if (!isset($_SESSION['loggedin']) && isset($_COOKIE['remember_username'])) {
    $username = $_COOKIE['remember_username'];
    $user = $userModel->getUserByUsername($username);

    if ($user) {
        // Set session variables for authenticated user
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_id'] = $user['id'];

        // Redirect to home page after successful auto-login
        header("Location: ../index.php");
        exit();
    }

    // Clear invalid or expired cookie
    setcookie('remember_username', '', time() - 3600, '/');
}

/**
 * Form Submission Handling
 *
 * Process login form when user submits username and password.
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve data from input
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember_me = isset($_POST['remember_me']); // Check if "Remember Me" was selected

    // make sure that both fields are filled
    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        // Retrieve user data from database by username
        $user = $userModel->getUserByUsername($username);

        if ($user) {
            // Verify password using secure password_verify
            if ($userModel->verifyPassword($password, $user['password_hash'])) {
                // Login successful - set session variables
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_id'] = $user['id'];

                /**
                 * "Remember Me" Functionality
                 *
                 * If user selected "Remember Me", set a persistent cookie
                 * that will automatically log them in on future visits.
                 */
                if ($remember_me) {
                    $cookie_name = 'remember_username';
                    $cookie_value = $user['username'];
                    $expiry_time = time() + (86400 * 30); // 30 days from now

                    setcookie($cookie_name, $cookie_value, [
                        'expires' => $expiry_time,
                        'path' => '/',
                        'httponly' => true,    // Prevent JavaScript access for security
                        'samesite' => 'Lax'    // CSRF protection
                    ]);
                }

                // Redirect to home page after successful login
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

// Include the view template to render the login form
include '../views/login.phtml';
?>