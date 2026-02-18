<?php
require_once '../Model/PetModel.php';
require_once '../Model/UserModel.php';
session_start();
header('Content-Type: application/json');

// safety check users must be login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(403);
    echo json_encode(["error" => "Please login to report a sighting."]);
    exit;
}

try {
    $db = getDbConnection();

    // 获取 POST 数据
    $data = json_decode(file_get_contents('php://input'), true);
    $petId = $data['pet_id'] ?? null;
    $comment = trim($data['comment'] ?? '');

    if (!$petId || empty($comment)) {
        throw new Exception("Missing pet ID or description.");
    }

    // Update the 'description' column in the sightings table
    $sql = "UPDATE sightings SET comment = :comment WHERE pet_id = :petId";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        'comment' => $comment,
        'petId' => $petId
    ]);

    echo json_encode(["success" => true, "message" => "Sighting updated!"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}