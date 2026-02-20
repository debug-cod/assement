<?php
require_once '../Model/PetModel.php';
require_once '../Model/UserModel.php';
header('Content-Type: application/json');

$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : 0;
$lng = isset($_GET['lng']) ? floatval($_GET['lng']) : 0;
$radius = isset($_GET['radius']) ? intval($_GET['radius']) : 50; // 默认 50km

try {
    $db = getDbConnection();

    // extract all the information i need from my database
    // we make selection first
    $sql = "SELECT p.id, p.name, p.gender, p.photo_url, s.latitude, s.longitude 
        FROM pets p
        JOIN sightings s ON p.id = s.pet_id";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $allPets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results = [];
    foreach ($allPets as $pet) {
        // Calculate the distance (semi-versus formula) in the PHP layer.
        $earthRadius = 6371; // KM
        $dLat = deg2rad($pet['latitude'] - $lat);
        $dLon = deg2rad($pet['longitude'] - $lng);

        $a = sin($dLat/2) * sin($dLat/2) +
            cos(deg2rad($lat)) * cos(deg2rad($pet['latitude'])) *
            sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        // 3. fittler the pet around 50KM
        if ($distance <= $radius) {
            $pet['distance'] = $distance;
            $results[] = $pet;
        }
    }

    // 4. only selection first five of them
    usort($results, function($a, $b) {
        return $a['distance'] <=> $b['distance'];
    });
    $results = array_slice($results, 0, 5);

    echo json_encode($results);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}