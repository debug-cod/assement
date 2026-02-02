<?php

//use the model loader
require_once '../Model/ModelLoader.php';

/**
  PetModel - for browsing and searching pets
  Extends PetRelatedModel to use its pet functions
 */
class PetModel extends PetRelatedModel
{

     // Tell which table this model uses

    protected function getTableName(): string
    {
        return 'pets';
    }


    //  Which fields can be searched

    protected function getSearchableFields(): array
    {
        return ['name', 'species', 'breed', 'color', 'description'];
    }

    /**
      Get all pets with search and filters
      Used on the pet browsing page
     */
    public function getPets($search = '', $filters = [], $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;

        // Base query with counts for sightings and rewards
        $sql = "SELECT p.*, 
                       COUNT(s.id) as sighting_count,
                       COALESCE(SUM(s.reward), 0) as total_reward,
                       MAX(s.timestamp) as last_sighting
                FROM pets p
                LEFT JOIN sightings s ON p.id = s.pet_id
                WHERE 1=1";

        $params = [];

        // Add search
        if (!empty($search)) {
            $searchCondition = $this->buildSearchCondition($this->getSearchableFields(), $search);
            $sql .= $searchCondition[0];
            $params = array_merge($params, $searchCondition[1]);
        }

        // Apply filters
        $sql = $this->applyFilters($sql, $filters, $params);

        // Group by pet ID
        $sql .= " GROUP BY p.id";

        // Handle reward filtering
        $sql = $this->applyHavingConditions($sql, $filters);

        // Sort by date
        $sort = isset($filters['sort']) ? $filters['sort'] : 'late';
        $sql .= ($sort === 'early') ? " ORDER BY p.date_reported ASC" : " ORDER BY p.date_reported DESC";

        // Add pagination
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->executeStatement($sql, $params);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
      Apply filters to the SQL query
      Handles species, color, gender, age filters
     */
    private function applyFilters(string $sql, array $filters, array &$params): string
    {
        // Species filter
        if (!empty($filters['species'])) {
            if ($filters['species'] === 'Other') {
                $sql .= " AND p.species NOT IN ('Cat', 'Dog', 'Bird', 'Rabbit', 'Hamster')";
            } else {
                $sql .= " AND p.species = ?";
                $params[] = $filters['species'];
            }
        }

        // Color filter
        if (!empty($filters['color'])) {
            if ($filters['color'] === 'Other') {
                $sql .= " AND p.color NOT IN ('White', 'Black', 'Red', 'Purple', 'Multi-color', 'Grey', 'Yellow')";
            } else {
                $sql .= " AND p.color = ?";
                $params[] = $filters['color'];
            }
        }

        // Gender filter
        if (!empty($filters['gender'])) {
            $sql .= " AND p.gender = ?";
            $params[] = $filters['gender'];
        }

        // Age filter
        if (!empty($filters['age'])) {
            $sql .= $this->getAgeCondition($filters['age']);
        }

        return $sql;
    }


      //Convert age range to SQL condition

    private function getAgeCondition(string $ageRange): string
    {
        switch ($ageRange) {
            case '1-5': return " AND p.age BETWEEN 1 AND 5";
            case '6-10': return " AND p.age BETWEEN 6 AND 10";
            case '15-20': return " AND p.age BETWEEN 15 AND 20";
            case '20+': return " AND p.age > 20";
            default: return "";
        }
    }


    //  Handle reward filtering with HAVING clause

    private function applyHavingConditions(string $sql, array $filters): string
    {
        if (!empty($filters['reward'])) {
            $sql .= " HAVING COALESCE(SUM(s.reward), 0) " . $this->getRewardCondition($filters['reward']);
        }
        return $sql;
    }


    //  Convert reward range to SQL condition

    private function getRewardCondition(string $rewardRange): string
    {
        switch ($rewardRange) {
            case '0-50': return "BETWEEN 0 AND 50";
            case '50-100': return "BETWEEN 50 AND 100";
            case '100-200': return "BETWEEN 100 AND 200";
            case '200-250': return "BETWEEN 200 AND 250";
            case '250-300': return "BETWEEN 250 AND 300";
            case '300+': return "> 300";
            default: return "BETWEEN 0 AND 999999";
        }
    }

    /**
      Count total pets for pagination
      Handles the same filters as getPets
     */
    public function getTotalPets($search = '', $filters = [])
    {
        $sql = "SELECT COUNT(DISTINCT p.id) as total FROM pets p WHERE 1=1";
        $params = [];

        // Search
        if (!empty($search)) {
            $searchCondition = $this->buildSearchCondition($this->getSearchableFields(), $search);
            $sql .= $searchCondition[0];
            $params = $searchCondition[1];
        }

        // Apply same filters
        $sql = $this->applyFilters($sql, $filters, $params);

        // For reward filter, check pets with matching sightings
        if (!empty($filters['reward'])) {
            $sql .= " AND p.id IN (SELECT pet_id FROM sightings GROUP BY pet_id 
                      HAVING COALESCE(SUM(reward), 0) " . $this->getRewardCondition($filters['reward']) . ")";
        }

        $stmt = $this->executeStatement($sql, $params);
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['total' => 0];
        return isset($result['total']) ? (int)$result['total'] : 0;
    }
}
?>