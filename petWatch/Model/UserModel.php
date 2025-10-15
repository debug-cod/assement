<?php
// Model/UserModel.php

/**
 * Handles all user-related database operations for petWatch.
 */
class UserModel
{
    private $db; // The PDO connection object

    // Constructor to receive the database connection
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Retrieves a user from the database by username.
     * @param string $username The username to search for.
     * @return array|false The user record as an associative array, or false if not found.
     */
    public function getUserByUsername(string $username)
    {
        // Use a prepared statement to prevent SQL injection (important for security!)
        $stmt = $this->db->prepare("SELECT id, username, password_hash, role FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        // PDO::FETCH_ASSOC returns the row as an associative array
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- Methods for 'Remember Me' (Cookies) ---
    // You would typically add methods here to handle tokens for secure
    // persistent login, but for a simple assignment, we'll keep the
    // cookie logic primarily in the controller for simplicity, as
    // robust token-based 'Remember Me' is more complex.
}

// Dummy database connection setup for demonstration (replace with your actual PDO setup)
// In a real MVC app, this would be handled by a dedicated Database/DBManager class.
function getDbConnection(): PDO {
    // Note: The assignment specifies using SQLite [cite: 48]
    // The path to your SQLite file: E:\petWatch\database\petwatch.sqlite
    $db_path = __DIR__ . '/../database/petwatch.sqlite';
    try {
        $db = new PDO("sqlite:$db_path");
        // Set error mode to throw exceptions
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        // In a production app, you'd log this, not display it.
        die("Database connection failed: " . $e->getMessage());
    }
}