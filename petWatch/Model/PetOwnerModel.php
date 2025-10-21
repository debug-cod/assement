<?php
// Model/PetOwnerModel.php

/**
 * Handles all pet owner related database operations for petWatch.
 */
class PetOwnerModel
{
    private $db;

    // Constructor to receive the database connection
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Add a new pet
     */
    public function addPet($name, $species, $breed, $color, $photo_url, $status, $description, $user_id, $gender, $age, $reward = 0)
    {
        $stmt = $this->db->prepare("
            INSERT INTO pets (name, species, breed, color, photo_url, status, description, user_id, gender, age, date_reported)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, date('now'))
        ");
        return $stmt->execute([$name, $species, $breed, $color, $photo_url, $status, $description, $user_id, $gender, $age]);
    }

    /**
     * Get pets by owner ID with search
     */
    public function getPetsByOwner($user_id, $search = '', $page = 1, $perPage = 9)
    {
        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT p.*, 
                   COUNT(s.id) as sighting_count,
                   COALESCE(SUM(s.reward), 0) as total_reward
            FROM pets p
            LEFT JOIN sightings s ON p.id = s.pet_id
            WHERE p.user_id = ?
        ";

        $params = [$user_id];

        if (!empty($search)) {
            $sql .= " AND (p.name LIKE ? OR p.species LIKE ? OR p.breed LIKE ? 
                     OR p.color LIKE ? OR p.description LIKE ? OR p.status LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, array_fill(0, 6, $searchTerm));
        }

        $sql .= " GROUP BY p.id ORDER BY p.date_reported DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total pets count for pagination
     */
    public function getTotalPetsByOwner($user_id, $search = '')
    {
        $sql = "SELECT COUNT(*) as total FROM pets WHERE user_id = ?";
        $params = [$user_id];

        if (!empty($search)) {
            $sql .= " AND (name LIKE ? OR species LIKE ? OR breed LIKE ? 
                     OR color LIKE ? OR description LIKE ? OR status LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, array_fill(0, 6, $searchTerm));
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Get pet by ID and owner ID (for modification)
     */
    public function getPetById($pet_id, $user_id)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, 
                   COUNT(s.id) as sighting_count,
                   COALESCE(SUM(s.reward), 0) as total_reward
            FROM pets p
            LEFT JOIN sightings s ON p.id = s.pet_id
            WHERE p.id = ? AND p.user_id = ?
            GROUP BY p.id
        ");
        $stmt->execute([$pet_id, $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update a pet
     */
    public function updatePet($pet_id, $user_id, $name, $species, $breed, $color, $photo_url, $status, $description, $gender, $age)
    {
        $stmt = $this->db->prepare("
            UPDATE pets 
            SET name = ?, species = ?, breed = ?, color = ?, photo_url = ?, 
                status = ?, description = ?, gender = ?, age = ?
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$name, $species, $breed, $color, $photo_url, $status, $description, $gender, $age, $pet_id, $user_id]);
    }

    /**
     * Delete a pet and its sightings
     */
    public function deletePet($pet_id, $user_id)
    {
        // First delete sightings for this pet
        $stmt1 = $this->db->prepare("DELETE FROM sightings WHERE pet_id = ?");
        $stmt1->execute([$pet_id]);

        // Then delete the pet
        $stmt2 = $this->db->prepare("DELETE FROM pets WHERE id = ? AND user_id = ?");
        return $stmt2->execute([$pet_id, $user_id]);
    }

    /**
     * Get sightings for a specific pet
     */
    public function getSightingsByPet($pet_id)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM sightings 
            WHERE pet_id = ? 
            ORDER BY timestamp DESC
        ");
        $stmt->execute([$pet_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>