<?php
require_once '../Model/PetModel.php';
require_once '../Model/UserModel.php';
session_start();
header('Content-Type: application/json');

// 1. 安全检查：必须登录
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(403);
    echo json_encode(["error" => "Please login to report a sighting."]);
    exit;
}

try {
    $db = getDbConnection();

    // --- 兼容性获取数据开始 ---
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    // 如果 JSON 解析失败，尝试从普通的 $_POST 获取（防止有些浏览器传参方式不同）
    $petId = $data['pet_id'] ?? $_POST['pet_id'] ?? null;
    $comment = $data['comment'] ?? $_POST['comment'] ?? null;
    // --- 兼容性获取数据结束 ---

    if (!$petId || empty($comment)) {
        http_response_code(400);
        // 报错的同时，把接收到的原始数据吐出来，方便你在 Network -> Preview 里看
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