<?php

//use the model loader
require_once '../Model/ModelLoader.php';

/**
 * SightingModel class - Handles all sighting-related database operations
 * Extends PetRelatedModel to inherit pet-specific functionality
 * This demonstrates how different entities can share common behavior through inheritance
 */
class SightingModel extends PetRelatedModel
{
    /**
     * Get the table name for this model
     * @return string Table name
     */
    protected function getTableName(): string
    {
        return 'sightings';
    }

    /**
     * Get searchable fields for sighting model
     * @return array List of searchable field names
     */
    protected function getSearchableFields(): array
    {
        return ['comment', 'latitude', 'longitude', 'timestamp'];
    }

    /**
     * Get all lost pets for reporting sightings
     * This method demonstrates specialized functionality in child class
     * @return array Array of lost pets with reward information
     */
    public function getLostPets()
    {
        $sql = "SELECT p.*, COALESCE(SUM(s.reward), 0) as total_reward
                FROM pets p
                LEFT JOIN sightings s ON p.id = s.pet_id
                WHERE p.status = 'lost'
                GROUP BY p.id
                ORDER BY p.name";

        $stmt = $this->executeStatement($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Add a new sighting record
     * Demonstrates parameter validation and database insertion
     * @param int $pet_id ID of the pet
     * @param int $user_id ID of the user reporting
     * @param string $comment Description of the sighting
     * @param float $latitude Geographic latitude
     * @param float $longitude Geographic longitude
     * @param string $timestamp When the sighting occurred
     * @param float $reward Optional reward amount
     * @return bool Success status of the operation
     */
    public function addSighting($pet_id, $user_id, $comment, $latitude, $longitude, $timestamp, $reward = 0)
    {
        $sql = "INSERT INTO sightings (pet_id, user_id, comment, latitude, longitude, timestamp, reward)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $params = [$pet_id, $user_id, $comment, $latitude, $longitude, $timestamp, $reward];
        $stmt = $this->executeStatement($sql, $params);
        return $stmt && $stmt->rowCount() > 0;
    }

    /**
     * Get sightings by specific user with optional search
     * Demonstrates method overriding with additional parameters
     * @param int $user_id ID of the user
     * @param string $search Optional search term
     * @return array Array of sightings with pet information
     */
    public function getSightingsByUser($user_id, $search = '')
    {
        $sql = "SELECT s.*, p.name as pet_name, p.photo_url, p.description, p.species, p.breed, p.color
                FROM sightings s
                JOIN pets p ON s.pet_id = p.id
                WHERE s.user_id = ?";

        $params = [$user_id];

        // Add search condition if provided
        if (!empty($search)) {
            $searchFields = ['p.name', 'p.description', 's.comment', 's.latitude', 's.longitude', 's.timestamp'];
            $searchCondition = $this->buildSearchCondition($searchFields, $search);
            $sql .= $searchCondition[0];
            $params = array_merge($params, $searchCondition[1]);
        }

        $sql .= " ORDER BY s.timestamp DESC";

        $stmt = $this->executeStatement($sql, $params);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Delete a sighting by ID with user verification
     * Demonstrates security through user ownership check
     * @param int $sighting_id ID of the sighting to delete
     * @param int $user_id ID of the user (for verification)
     * @return bool Success status of deletion
     */
    public function deleteSighting($sighting_id, $user_id)
    {
        $sql = "DELETE FROM sightings WHERE id = ? AND user_id = ?";
        $stmt = $this->executeStatement($sql, [$sighting_id, $user_id]);
        return $stmt && $stmt->rowCount() > 0;
    }

    /**
     * Get recent sightings for dashboard display
     * Demonstrates additional specialized methods in child class
     * @param int $limit Number of recent sightings to return
     * @return array Array of recent sightings
     */
    public function getRecentSightings($limit = 5)
    {
        $sql = "SELECT s.*, p.name as pet_name, u.username as reporter
                FROM sightings s
                JOIN pets p ON s.pet_id = p.id
                JOIN users u ON s.user_id = u.id
                ORDER BY s.timestamp DESC
                LIMIT ?";

        $stmt = $this->executeStatement($sql, [$limit]);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
}
?>