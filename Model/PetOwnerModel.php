<?php
//use the model loader
require_once '../Model/ModelLoader.php';
/**
  PetOwnerModel - handles everything for pet owners
  Add pets, view pets, update pets, delete pets, and see sightings
 */
class PetOwnerModel
{
    private $db;

    // Constructor needs database connection
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }


     // Add a new pet to the system

    public function addPet($name, $species, $breed, $color, $photo_url, $status, $description, $user_id, $gender, $age, $reward = 0)
    {
        $stmt = $this->db->prepare("
            INSERT INTO pets (name, species, breed, color, photo_url, status, description, user_id, gender, age, date_reported)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, date('now'))
        ");
        return $stmt->execute([$name, $species, $breed, $color, $photo_url, $status, $description, $user_id, $gender, $age]);
    }

    /**
      Get pets owned by a specific user
      Used on "my pets" page
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

        // Add search if needed
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


    //  Count total pets for pagination

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


     // Get one specific pet by ID - for editing

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

    //Update pet information

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


    //  Delete a pet and all its sightings

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
      Get all sightings for a specific pet
      Used on pet details page
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