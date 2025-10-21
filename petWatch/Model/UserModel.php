<?php
// Model/UserModel.php

class UserModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Retrieves a user from the database by username.
     */
    public function getUserByUsername(string $username)
    {
        $stmt = $this->db->prepare("SELECT id, username, password_hash, role FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * NEW METHOD: Verify password using password_verify
     */
    public function verifyPassword(string $plainPassword, string $hashedPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }

    /**
     * NEW METHOD: Hash password using password_hash
     * (For future use if you need to create/update users)
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

// Database connection function remains the same
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