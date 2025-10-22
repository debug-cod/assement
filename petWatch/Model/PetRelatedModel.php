<?php
// Model/PetRelatedModel.php
//use the model loader
require_once '../Model/ModelLoader.php';
/**
 * Abstract PetRelatedModel class
 * Provides common functionality for models that work with pet data
 * This demonstrates intermediate level inheritance
 */

abstract class PetRelatedModel extends BaseModel
{
    /**
     * Common method to get records with pagination
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param string $search Search term
     * @return array Paginated results
     */
    public function getWithPagination(int $page = 1, int $perPage = 10, string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM " . $this->getTableName();
        $params = [];

        // Add search condition if provided
        if (!empty($search)) {
            $searchCondition = $this->buildSearchCondition($this->getSearchableFields(), $search);
            $sql .= " WHERE 1=1" . $searchCondition[0];
            $params = $searchCondition[1];
        }

        // Add pagination
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->executeStatement($sql, $params);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Get searchable fields for this model
     * Child classes must implement this
     * @return array List of searchable field names
     */
    abstract protected function getSearchableFields(): array;

    /**
     * Common method to calculate total pages
     * @param string $search Search term
     * @param int $perPage Items per page
     * @return int Total number of pages
     */
    public function getTotalPages(string $search = '', int $perPage = 10): int
    {
        $sql = "SELECT COUNT(*) as total FROM " . $this->getTableName();
        $params = [];

        if (!empty($search)) {
            $searchCondition = $this->buildSearchCondition($this->getSearchableFields(), $search);
            $sql .= " WHERE 1=1" . $searchCondition[0];
            $params = $searchCondition[1];
        }

        $stmt = $this->executeStatement($sql, $params);
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['total' => 0];
        return (int)ceil($result['total'] / $perPage);
    }
}
?>