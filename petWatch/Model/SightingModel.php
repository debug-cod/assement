<?php
// Model/SightingModel.php

/**
 * Handles all sighting-related database operations for petWatch.
 */
class SightingModel
{
    private $db;

    // Constructor to receive the database connection
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all lost pets for reporting sightings
     */
    public function getLostPets()
    {
        $stmt = $this->db->prepare("
            SELECT p.*, 
                   COALESCE(SUM(s.reward), 0) as total_reward
            FROM pets p
            LEFT JOIN sightings s ON p.id = s.pet_id
            WHERE p.status = 'lost'
            GROUP BY p.id
            ORDER BY p.name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add a new sighting
     */
    public function addSighting($pet_id, $user_id, $comment, $latitude, $longitude, $timestamp, $reward)
    {
        $stmt = $this->db->prepare("
            INSERT INTO sightings (pet_id, user_id, comment, latitude, longitude, timestamp, reward)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$pet_id, $user_id, $comment, $latitude, $longitude, $timestamp, $reward]);
    }

    /**
     * Get sightings by user ID with search
     */
    public function getSightingsByUser($user_id, $search = '')
    {
        $sql = "
            SELECT s.*, p.name as pet_name, p.photo_url, p.description, p.species, p.breed, p.color
            FROM sightings s
            JOIN pets p ON s.pet_id = p.id
            WHERE s.user_id = ?
        ";

        $params = [$user_id];

        if (!empty($search)) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR s.comment LIKE ? 
                     OR s.latitude LIKE ? OR s.longitude LIKE ? OR s.timestamp LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, array_fill(0, 6, $searchTerm));
        }

        $sql .= " ORDER BY s.timestamp DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete a sighting by ID
     */
    public function deleteSighting($sighting_id, $user_id)
    {
        $stmt = $this->db->prepare("
            DELETE FROM sightings 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$sighting_id, $user_id]);
    }
}
?>