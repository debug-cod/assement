<?php
require_once '../Model/PetModel.php';
require_once '../Model/UserModel.php';
session_start();
header('Content-Type: application/json');

// safety check user had to login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(403);
    echo json_encode(["error" => "Please login to report a sighting."]);
    exit;
}

try {
    $db = getDbConnection();

    // Compatibility data retrieval begins
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    // If JSON parsing fails, try retrieving it from a regular $_POST request
    $petId = $data['pet_id'] ?? $_POST['pet_id'] ?? null;
    $comment = $data['comment'] ?? $_POST['comment'] ?? null;
    // Compatibility data retrieval ends

    if (!$petId || empty($comment)) {
        http_response_code(400);
        // if reporting errors, it also outputs the raw data received, making it convenient to view in Network -> Preview.
        echo json_encode([
            "error" => "Missing fields",
            "debug_received_raw" => $rawInput,
            "debug_received_post" => $_POST
        ]);
        exit;
    }

    $sql = "UPDATE sightings SET comment = :comment WHERE pet_id = :petId";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':comment' => $comment,
        ':petId'   => $petId
    ]);

    echo json_encode(["success" => true, "message" => "Updated!"]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}