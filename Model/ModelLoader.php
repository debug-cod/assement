<?php
//create a model-loader make sure everything can be load in different devices
/**
 * ModelLoader - Handles loading all model classes in correct order
 * This ensures proper inheritance chain and avoids class not found errors
 * the reason of having this because I got multiply issue on model loading
 */

class ModelLoader
{
    private static $loaded = false;

    /**
     * Load all model classes in correct dependency order
     */
    public static function loadModels()
    {
        if (self::$loaded) {
            return; // Already loaded
        }

        // Define the correct loading order
        $modelFiles = [
            'BaseModel.php',
            'PetRelatedModel.php',
            'UserModel.php',
            'PetModel.php',
            'SightingModel.php',
            'PetOwnerModel.php'
        ];

        $modelDir = __DIR__ . DIRECTORY_SEPARATOR;

        foreach ($modelFiles as $file) {
            $filePath = $modelDir . $file;
            if (file_exists($filePath)) {
                require_once $filePath;
            } else {
                // Log error but don't stop execution
                error_log("Model file not found: " . $filePath);
            }
        }

        self::$loaded = true;
    }
}

// Auto-load when this file is included
ModelLoader::loadModels();
?>