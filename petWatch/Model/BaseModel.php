<?php
// Model/BaseModel.php
require_once '../Model/ModelLoader.php';
/**
 * Abstract BaseModel class
 * Provides common database functionality for all models
 * This demonstrates inheritance and polymorphism in OOP
 */
abstract class BaseModel
{
    protected $db;
    protected $tableName;

    /**
     * Constructor - accepts PDO database connection
     * @param PDO $db Database connection object
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get the table name for this model
     * @return string Table name
     */
    abstract protected function getTableName(): string;

    /**
     * Common method to execute prepared statement
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for the query
     * @return PDOStatement|false Executed statement
     */
    protected function executeStatement(string $sql, array $params = [])
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database error in " . get_class($this) . ": " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get record by ID - common CRUD operation
     * @param int $id Record ID
     * @return array|false Record data or false if not found
     */
    public function getById(int $id)
    {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE id = ?";
        $stmt = $this->executeStatement($sql, [$id]);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }

    /**
     * Count all records in table
     * @return int Number of records
     */
    public function countAll(): int
    {
        $sql = "SELECT COUNT(*) as total FROM " . $this->getTableName();
        $stmt = $this->executeStatement($sql);
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['total' => 0];
        return (int)$result['total'];
    }

    /**
     * Delete record by ID
     * @param int $id Record ID
     * @return bool Success status
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM " . $this->getTableName() . " WHERE id = ?";
        $stmt = $this->executeStatement($sql, [$id]);
        return $stmt && $stmt->rowCount() > 0;
    }

    /**
     * Common method for building search conditions
     * @param array $searchFields Fields to search in
     * @param string $searchTerm Search term
     * @return array [sql_condition, parameters]
     */
    protected function buildSearchCondition(array $searchFields, string $searchTerm): array
    {
        if (empty($searchTerm)) {
            return ['', []];
        }

        $conditions = [];
        $params = [];
        $searchTerm = "%$searchTerm%";

        foreach ($searchFields as $field) {
            $conditions[] = "$field LIKE ?";
            $params[] = $searchTerm;
        }

        $sql = " AND (" . implode(" OR ", $conditions) . ")";
        return [$sql, $params];
    }
}
?>