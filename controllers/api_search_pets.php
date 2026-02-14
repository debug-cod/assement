<?php
/**
 * AJAX 搜索接口 - 位于 controllers/ 目录下
 * 该接口由 SearchManager 类通过 fetch() 调用
 */

// 1. 引入模型（注意路径：由于在 controllers 文件夹，需用 ../ 找 Model）
require_once '../Model/PetModel.php';
require_once '../Model/UserModel.php'; // 包含 getDbConnection()

// 2. 设置 Header（必须在任何输出之前）
header('Content-Type: application/json');

try {
    // 3. 初始化数据库和模型
    $db = getDbConnection();
    $petModel = new PetModel($db);

    // 4. 获取并清理输入词
    $term = isset($_GET['term']) ? trim($_GET['term']) : '';

    // 5. 逻辑判断：只有超过2个字符才执行搜索（防抖后的后端二次验证）
    if (strlen($term) > 2) {
        // 直接调用 A1 的方法。该方法内部已使用 PDO Prepared Statements，防御 SQL 注入。
        // 我们限制返回 10 条，以保证搜索建议的性能
        $results = $petModel->getPets($term, [], 1, 10);

        // 确保只返回结果数组
        echo json_encode($results);
    } else {
        // 字数不够返回空数组
        echo json_encode([]);
    }

} catch (Exception $e) {
    // 错误处理：如果数据库挂了，返回错误 JSON 而不是让页面崩溃
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error']);
}