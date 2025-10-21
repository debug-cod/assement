<?php
// update_passwords.php - One-time script to hash existing passwords
require_once 'Model/UserModel.php';

$db = getDbConnection();
$userModel = new UserModel($db);

try {
    // Get all users
    $stmt = $db->query("SELECT id, password_hash FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $updatedCount = 0;

    foreach ($users as $user) {
        // Only hash if password is not already hashed
        // password_verify will return false for plain text passwords
        if (!password_verify('test', $user['password_hash'])) {
            $hashedPassword = password_hash($user['password_hash'], PASSWORD_DEFAULT);

            // Update the password in database
            $updateStmt = $db->prepare("UPDATE users SET password_hash = :password WHERE id = :id");
            $updateStmt->bindParam(':password', $hashedPassword);
            $updateStmt->bindParam(':id', $user['id']);
            $updateStmt->execute();

            $updatedCount++;
        }
    }

    echo "Successfully updated $updatedCount passwords to hashed format.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>