<?php

// Use the model loader to ensure base classes are loaded
require_once '../Model/ModelLoader.php';

/**
 * PetModel - MariaDB Compatible
 * Handles pet browsing with aggregated sighting data.
 */
class PetModel extends PetRelatedModel
{
    /**
     * Tells the model which database table to use.
     */
    protected function getTableName(): string
    {
        return 'pets';
    }

    /**
     * Fields that can be searched via the search bar.
     */
    protected function getSearchableFields(): array
    {
        return ['name', 'species', 'breed', 'color', 'description'];
    }

    /**
     * Get all pets with aggregated statistics (Sightings, Rewards, Last Seen).
     * Mapped to match the keys in browse.phtml exactly.
     */
    public function getPets($search = '', $filters = [], $page = 1, $perPage = 10)
    {
        $offset = (int)($page - 1) * (int)$perPage;

        // SQL Explanation:
        // sighting_count -> Counts total rows in sightings table for this pet
        // total_reward   -> Sums the reward column (using 0 if null)
        // last_sighting  -> Gets the latest timestamp from sightings table
        $sql = "SELECT p.id, p.name, p.species, p.breed, p.color, p.photo_url, 
                       p.status, p.gender, p.age, p.description, p.date_reported,
                       COUNT(s.id) as sighting_count,
                       IFNULL(SUM(s.reward), 0) as total_reward,
                       IFNULL(MAX(s.timestamp), 'Never') as last_sighting
                FROM pets p
                LEFT JOIN sightings s ON p.id = s.pet_id
                WHERE 1=1";

        $params = [];

        // Handle search keyword
        if (!empty($search)) {
            $searchCondition = $this->buildSearchCondition($this->getSearchableFields(), $search);
            $sql .= $searchCondition[0];
            $params = $searchCondition[1];
        }

        // Apply sidebar filters (Species, Gender, etc.)
        $sql = $this->applyFilters($sql, $filters, $params);

        // MariaDB Compatibility: Group by all non-aggregated columns
        $sql .= " GROUP BY p.id, p.name, p.species, p.breed, p.color, p.photo_url, 
                           p.status, p.gender, p.age, p.description, p.date_reported";

        // Sorting logic
        $sort = $filters['sort'] ?? 'late';
        switch ($sort) {
            case 'old':    $sql .= " ORDER BY p.id ASC"; break;
            case 'reward': $sql .= " ORDER BY total_reward DESC"; break;
            default:       $sql .= " ORDER BY p.id DESC"; break;
        }

        // Pagination
        $sql .= " LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $stmt = $this->executeStatement($sql, $params);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Private helper to apply filters to the SQL string.
     */
    private function applyFilters($sql, $filters, &$params)
    {
        if (!empty($filters['species'])) {
            $sql .= " AND p.species = ?";
            $params[] = $filters['species'];
        }
        if (!empty($filters['color'])) {
            $sql .= " AND p.color = ?";
            $params[] = $filters['color'];
        }
        if (!empty($filters['gender'])) {
            $sql .= " AND p.gender = ?";
            $params[] = $filters['gender'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }
        return $sql;
    }


    //  Counts the total number of pets for pagination controls.

    public function getTotalPets($search = '', $filters = [])
    {
        $sql = "SELECT COUNT(DISTINCT p.id) as total FROM pets p WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $searchCondition = $this->buildSearchCondition($this->getSearchableFields(), $search);
            $sql .= $searchCondition[0];
            $params = $searchCondition[1];
        }

        $sql = $this->applyFilters($sql, $filters, $params);

        $stmt = $this->executeStatement($sql, $params);
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['total' => 0];
        return (int)$result['total'];
    }
}