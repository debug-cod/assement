<?php
// Model/UserModel.php

//use the model loader
require_once '../Model/ModelLoader.php';

/**
 * UserModel class - Handles all user-related database operations
 * Extends BaseModel to inherit common database functionality
 * This demonstrates single inheritance in PHP
 */
class UserModel extends BaseModel
{
    /**
     * Get the table name for this model
     * @return string Table name
     */
    protected function getTableName(): string
    {
        return 'users';
    }

    /**
     * Retrieves a user from the database by username
     * @param string $username The username to search for
     * @return array|false The user record as associative array, or false if not found
     */
    public function getUserByUsername(string $username)
    {
        $sql = "SELECT id, username, password_hash, role FROM users WHERE username = :username";
        $stmt = $this->executeStatement($sql, [':username' => $username]);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }

    /**
     * Verify password using password_verify - secure password comparison
     * @param string $plainPassword Plain text password from user input
     * @param string $hashedPassword Hashed password from database
     * @return bool True if password matches, false otherwise
     */
    public function verifyPassword(string $plainPassword, string $hashedPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }

    /**
     * Hash password using password_hash - secure password hashing
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Get user by email - additional user lookup method
     * @param string $email User's email address
     * @return array|false User record or false if not found
     */
    public function getUserByEmail(string $email)
    {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->executeStatement($sql, [$email]);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }
}

// Database connection function remains outside the class
function getDbConnection(): PDO {
    $db_path = __DIR__ . '/../database/petwatch.sqlite';
    try {
        $db = new PDO("sqlite:$db_path");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}
?>