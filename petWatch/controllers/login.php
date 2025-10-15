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
// NOTE: For simplicity, we are storing the username in the cookie.
// A more secure approach for a real application would use a token-based system.
if (!isset($_SESSION['loggedin']) && isset($_COOKIE['remember_username'])) {
    $username = $_COOKIE['remember_username'];
    // For a simple demo, we just restore the session, assuming the cookie is valid.
    // In a production system, you must validate a unique, secure token from the cookie against the database.
    $user = $userModel->getUserByUsername($username);
    if ($user) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['user_role'] = $user['role'];

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
            // b. Verify the password against the stored hash
            // 'password_verify' is the secure way to check a password against a hash
            if (password_verify($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role']; // Store the role ('user' or 'pet_owner')

                // c. Handle 'Remember Me' (Cookie)
                if ($remember_me) {
                    // Set cookie to remember the username for 30 days
                    // NOTE: Use a unique token + database lookup for a truly secure solution.
                    $cookie_name = 'remember_username';
                    $cookie_value = $user['username'];
                    $expiry_time = time() + (86400 * 30); // 30 days
                    // Set the cookie with HttpOnly to mitigate XSS attacks (security!)
                    setcookie($cookie_name, $cookie_value, [
                        'expires' => $expiry_time,
                        'path' => '/',
                        'httponly' => true,
                        'samesite' => 'Lax' // Recommended for security
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
// We use the existing relative path from your initial file.
include '../views/login.phtml';
?>