<?php
require_once '../Model/ModelLoader.php';
/**
  Abstract PetRelatedModel class
  Used for models that work with pet data
  Has common pagination and search functions
 */

abstract class PetRelatedModel extends BaseModel
{
    /**
      Get records with pagination and search
      Used for showing data in pages
     */
    public function getWithPagination(int $page = 1, int $perPage = 10, string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM " . $this->getTableName();
        $params = [];

        // Add search if needed
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


    //  Child classes must say which fields can be searched

    abstract protected function getSearchableFields(): array;


    //  Calculate total pages for pagination

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