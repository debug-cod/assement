<?php

  //Login page - handles user login with remember me feature


// Start session for user login state
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$page_title = "Login - petWatch";
$error_message = '';

// Include user model for login
require_once '../Model/UserModel.php';

// Setup database and user model
$db = getDbConnection();
$userModel = new UserModel($db);

// Check if user has remember me cookie
if (!isset($_SESSION['loggedin']) && isset($_COOKIE['remember_username'])) {
    $username = $_COOKIE['remember_username'];
    $user = $userModel->getUserByUsername($username);

    if ($user) {
        // Auto-login user from cookie
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_id'] = $user['id'];

        // Go to home page
        header("Location: ../index.php");
        exit();
    }

    // Clear bad cookie
    setcookie('remember_username', '', time() - 3600, '/');
}

// Handle login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember_me = isset($_POST['remember_me']);

    // Check if fields are filled
    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        // Check if user exists
        $user = $userModel->getUserByUsername($username);

        if ($user) {
            // Check password
            if ($userModel->verifyPassword($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_id'] = $user['id'];

                // Set remember me cookie if wanted
                if ($remember_me) {
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

                // Go to home page
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

// Show login page
include '../views/login.phtml';
?>