<?php
require_once '../Model/ModelLoader.php';
/**
  Base model class that other models extend from
  Handles common database stuff like get by ID, delete, count, etc.
  This is the parent class for all models
 */
abstract class BaseModel
{
    protected $db;
    protected $tableName;

    //Constructor - needs a database connection to work

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }


    // Each model must tell which table it uses

    abstract protected function getTableName(): string;

    /**
      Common way to run SQL queries safely
      Handles errors so the app doesn't crash
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
     Get a single record by its ID
     */
    public function getById(int $id)
    {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE id = ?";
        $stmt = $this->executeStatement($sql, [$id]);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }


      //Count how many records are in the table

    public function countAll(): int
    {
        $sql = "SELECT COUNT(*) as total FROM " . $this->getTableName();
        $stmt = $this->executeStatement($sql);
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['total' => 0];
        return (int)$result['total'];
    }


     // Delete a record by ID - returns true if deleted successfully

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM " . $this->getTableName() . " WHERE id = ?";
        $stmt = $this->executeStatement($sql, [$id]);
        return $stmt && $stmt->rowCount() > 0;
    }

    /**
      Build search conditions for multiple fields
      Used for search functionality
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