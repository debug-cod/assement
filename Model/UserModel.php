<?php

//use the model loader
require_once '../Model/ModelLoader.php';

/**
  UserModel - handles user login, registration, and user data
  Extends BaseModel for common database functions
 */
class UserModel extends BaseModel
{

     // Tell which table this model uses

    protected function getTableName(): string
    {
        return 'users';
    }


     // Find a user by username - used for login

    public function getUserByUsername(string $username)
    {
        $sql = "SELECT id, username, password_hash, role FROM users WHERE username = :username";
        $stmt = $this->executeStatement($sql, [':username' => $username]);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }


    //  Check if password is correct

    public function verifyPassword(string $plainPassword, string $hashedPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }


    //  Hash password for safe storage

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }


    //  Find user by email - for registration and password reset

    public function getUserByEmail(string $email)
    {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->executeStatement($sql, [$email]);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }
}

// Database connection - used by all models
// UserModel.php
function getDbConnection(): PDO {
    // force to use clound enivorment set up
    $host = 'localhost';
    $db   = 'serve'; // the serve database name
    $user = 'chc742';       // the serve manage name
    $pass = 'jjgXuN6lViaeHkP'; // the serve marina db unqiue password

    try {
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    } catch (PDOException $e) {
        // if something gose wrong give message feedback
        error_log("Connection failed: " . $e->getMessage());
        die("Connection failed: Check username/password/DB name.");
    }
}
?>