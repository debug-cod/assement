<?php
// Login controller for petWatch

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$page_title = "Login - petWatch";
$error_message = '';

require_once '../Model/UserModel.php';
$db = getDbConnection();
$userModel = new UserModel($db);

// Check for 'Remember Me' Cookie
if (!isset($_SESSION['loggedin']) && isset($_COOKIE['remember_username'])) {
    $username = $_COOKIE['remember_username'];
    $user = $userModel->getUserByUsername($username);
    if ($user) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_id'] = $user['id'];
        header("Location: ../index.php");
        exit();
    }
    setcookie('remember_username', '', time() - 3600, '/');
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember_me = isset($_POST['remember_me']);

    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        $user = $userModel->getUserByUsername($username);

        if ($user) {
            // CHANGED: Use password_verify instead of plain text comparison
            if ($userModel->verifyPassword($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_id'] = $user['id'];

                // Handle 'Remember Me'
                if ($remember_me) {
                    $cookie_name = 'remember_username';
                    $cookie_value = $user['username'];
                    $expiry_time = time() + (86400 * 30);
                    setcookie($cookie_name, $cookie_value, [
                        'expires' => $expiry_time,
                        'path' => '/',
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);
                }

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

include '../views/login.phtml';
?>