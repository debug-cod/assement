<?php
// Pet model for database operations
// This file handles all database queries for pets

class PetModel
{
    private $db;

    // Constructor - connect to database
    public function __construct($databasePath)
    {
        try {
            $this->db = new PDO('sqlite:' . $databasePath);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    // Get all pets with search and filter
    public function getPets($search = '', $filters = [], $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;

        // Base query with joins
        $sql = "SELECT p.*, 
                       COUNT(s.id) as sighting_count,
                       COALESCE(SUM(s.reward), 0) as total_reward,
                       MAX(s.timestamp) as last_sighting
                FROM pets p
                LEFT JOIN sightings s ON p.id = s.pet_id
                WHERE 1=1";

        $params = [];

        // Search functionality
        if (!empty($search)) {
            $sql .= " AND (p.name LIKE ? OR p.species LIKE ? OR p.breed LIKE ? 
                     OR p.color LIKE ? OR p.description LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_fill(0, 5, $searchTerm);
        }

        // Apply filters
        if (!empty($filters['species'])) {
            if ($filters['species'] === 'Other') {
                $sql .= " AND p.species NOT IN ('Cat', 'Dog', 'Bird', 'Rabbit', 'Hamster')";
            } else {
                $sql .= " AND p.species = ?";
                $params[] = $filters['species'];
            }
        }

        if (!empty($filters['color'])) {
            if ($filters['color'] === 'Other') {
                $sql .= " AND p.color NOT IN ('White', 'Black', 'Red', 'Purple', 'Multi-color', 'Grey', 'Yellow')";
            } else {
                $sql .= " AND p.color = ?";
                $params[] = $filters['color'];
            }
        }

        if (!empty($filters['gender'])) {
            $sql .= " AND p.gender = ?";
            $params[] = $filters['gender'];
        }

        // Age filter
        if (!empty($filters['age'])) {
            switch ($filters['age']) {
                case '1-5': $sql .= " AND p.age BETWEEN 1 AND 5"; break;
                case '6-10': $sql .= " AND p.age BETWEEN 6 AND 10"; break;
                case '15-20': $sql .= " AND p.age BETWEEN 15 AND 20"; break;
                case '20+': $sql .= " AND p.age > 20"; break;
            }
        }

        // Sighting count filter
        if (!empty($filters['sightings'])) {
            if ($filters['sightings'] === '5+') {
                $sql .= " HAVING COUNT(s.id) > 5";
            } else {
                $sql .= " HAVING COUNT(s.id) = ?";
                $params[] = (int)$filters['sightings'];
            }
        }

        // Reward filter
        if (!empty($filters['reward'])) {
            switch ($filters['reward']) {
                case '0-50': $sql .= " AND COALESCE(SUM(s.reward), 0) BETWEEN 0 AND 50"; break;
                case '50-100': $sql .= " AND COALESCE(SUM(s.reward), 0) BETWEEN 50 AND 100"; break;
                case '100-200': $sql .= " AND COALESCE(SUM(s.reward), 0) BETWEEN 100 AND 200"; break;
                case '200-250': $sql .= " AND COALESCE(SUM(s.reward), 0) BETWEEN 200 AND 250"; break;
                case '250-300': $sql .= " AND COALESCE(SUM(s.reward), 0) BETWEEN 250 AND 300"; break;
                case '300+': $sql .= " AND COALESCE(SUM(s.reward), 0) > 300"; break;
            }
        }

        // Group and order
        $sql .= " GROUP BY p.id";

        // Sorting
        $sort = isset($filters['sort']) ? $filters['sort'] : 'late';
        if ($sort === 'early') {
            $sql .= " ORDER BY p.date_reported ASC";
        } else {
            $sql .= " ORDER BY p.date_reported DESC";
        }

        // Add limit for pagination
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Get total count for pagination
    public function getTotalPets($search = '', $filters = [])
    {
        // Similar query as above but without LIMIT and only count
        $sql = "SELECT COUNT(DISTINCT p.id) as total
                FROM pets p
                LEFT JOIN sightings s ON p.id = s.pet_id
                WHERE 1=1";

        $params = [];

        if (!empty($search)) {
            $sql .= " AND (p.name LIKE ? OR p.species LIKE ? OR p.breed LIKE ? 
                     OR p.color LIKE ? OR p.description LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_fill(0, 5, $searchTerm);
        }

        // Apply same filters as above...
        // (Filter logic would be duplicated here for counting)

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return isset($result['total']) ? $result['total'] : 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
}
?>