<?php
/**
 * AJAX 搜索接口 - 只负责给前端发 JSON
 * API for Live Search - returns JSON only
 */
require_once '../Model/PetModel.php';
require_once '../Model/UserModel.php'; // 里面有 getDbConnection

header('Content-Type: application/json'); // 告诉浏览器这是 JSON 格式

$db = getDbConnection();
$petModel = new PetModel($db);

// 获取用户输入的关键词 (Get search term from URL)
$searchTerm = isset($_GET['term']) ? trim($_GET['term']) : '';

$results = [];

if (!empty($searchTerm)) {
    // 调用你现有的 getPets 方法，我们只取前 10 条建议
    // Calling your existing getPets method, limiting to 10 suggestions
    $results = $petModel->getPets($searchTerm, [], 1, 10);
}

// 核心：把 PHP 数组转成 JS 能看懂的 JSON
// Crucial: encode data to JSON for JS
echo json_encode($results);