<?php
/**
 * Fixed Photo Updater - Completely independent script
 * No dependency on ModelLoader to avoid circular references
 */

echo "<h1>Pet Photo Updater - Fixed Version</h1>";
echo "<pre>";

try {
    // 1. 直接创建数据库连接，不通过模型
    $db_path = __DIR__ . '/database/petwatch.sqlite';
    echo "Database path: $db_path\n";

    if (!file_exists($db_path)) {
        throw new Exception("Database file not found: $db_path");
    }

    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Database connected successfully\n\n";

    // 2. 获取所有宠物
    $stmt = $db->query("SELECT id, name, species, breed, photo_url FROM pets");
    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($pets) . " pets to update\n\n";

    // 3. 确保图片目录存在
    $image_dir = __DIR__ . '/images/pet-image/';
    if (!is_dir($image_dir)) {
        mkdir($image_dir, 0755, true);
        echo "Created image directory: $image_dir\n";
    }

    $updated_count = 0;
    $error_count = 0;

    // 4. 为每个宠物更新照片
    foreach ($pets as $pet) {
        echo "Processing: {$pet['name']} ({$pet['species']})... ";

        try {
            // 生成新的照片URL
            $new_photo_url = generatePhotoUrl($pet);

            // 更新数据库
            $update_stmt = $db->prepare("UPDATE pets SET photo_url = ? WHERE id = ?");
            $result = $update_stmt->execute([$new_photo_url, $pet['id']]);

            if ($result) {
                echo "✓ Updated to: $new_photo_url\n";
                $updated_count++;
            } else {
                echo "✗ Database update failed\n";
                $error_count++;
            }

        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
            $error_count++;
        }
    }

    echo "\n=== UPDATE COMPLETE ===\n";
    echo "Successfully updated: $updated_count pets\n";
    echo "Errors: $error_count pets\n";
    echo "Total processed: " . count($pets) . " pets\n";

} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";

/**
 * 生成照片URL - 使用占位图片服务
 */
function generatePhotoUrl($pet) {
    $species = strtolower($pet['species']);
    $pet_id = $pet['id'];

    // 使用稳定的占位图片服务
    $placeholders = [
        'dog' => 'https://placedog.net/300/200',
        'cat' => 'https://placekitten.com/300/200',
        'bird' => 'https://loremflickr.com/300/200/bird',
        'rabbit' => 'https://loremflickr.com/300/200/rabbit',
        'hamster' => 'https://loremflickr.com/300/200/hamster'
    ];

    // 如果是已知物种，使用特定图片，否则使用通用动物图片
    if (isset($placeholders[$species])) {
        $image_url = $placeholders[$species];
    } else {
        $image_url = "https://loremflickr.com/300/200/" . urlencode($species) . ",animal";
    }

    // 生成文件名
    $file_extension = 'jpg';
    $filename = 'pet_' . uniqid() . '_' . $pet_id . '.' . $file_extension;

    return $filename;
}
?>