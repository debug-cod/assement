<?php
require_once '../Model/PetModel.php';
require_once '../Model/UserModel.php';
header('Content-Type: application/json');

try {
    $db = getDbConnection();
    // attempt capture first
    $term = isset($_GET['term']) ? trim($_GET['term']) : '';

    if (strlen($term) > 2) {
        // use JSON to get location
        $sql = "SELECT p.*, s.latitude, s.longitude 
                FROM pets p 
                LEFT JOIN sightings s ON p.id = s.pet_id 
                WHERE p.name LIKE :term 
                GROUP BY p.id LIMIT 10";

        $stmt = $db->prepare($sql);
        $stmt->execute(['term' => "%$term%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($results);
    } else {
        echo json_encode([]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}