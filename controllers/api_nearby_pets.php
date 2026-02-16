<?php
// controllers/api_nearby_pets.php
require_once '../Model/PetModel.php';
require_once '../Model/UserModel.php';
header('Content-Type: application/json');

$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : 0;
$lng = isset($_GET['lng']) ? floatval($_GET['lng']) : 0;
$radius = isset($_GET['radius']) ? intval($_GET['radius']) : 50; // 默认 50km

try {
    $db = getDbConnection();

    // 1. 先把所有有位置信息的宠物取出来
    // SQLite 不支持 acos，我们直接在 SQL 里选出字段，稍后在 PHP 处理距离
    $sql = "SELECT p.name, p.gender, p.photo_url, s.latitude, s.longitude
            FROM pets p
            JOIN sightings s ON p.id = s.pet_id";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $allPets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results = [];
    foreach ($allPets as $pet) {
        // 2. 在 PHP 层计算距离 (半正矢公式)
        $earthRadius = 6371; // 公里
        $dLat = deg2rad($pet['latitude'] - $lat);
        $dLon = deg2rad($pet['longitude'] - $lng);

        $a = sin($dLat/2) * sin($dLat/2) +
            cos(deg2rad($lat)) * cos(deg2rad($pet['latitude'])) *
            sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        // 3. 过滤出 50km 以内的宠物
        if ($distance <= $radius) {
            $pet['distance'] = $distance;
            $results[] = $pet;
        }
    }

    // 4. 按距离排序并只取前 5 个
    usort($results, function($a, $b) {
        return $a['distance'] <=> $b['distance'];
    });
    $results = array_slice($results, 0, 5);

    echo json_encode($results);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}