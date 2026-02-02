<?php

//use the model loader
require_once '../Model/ModelLoader.php';

/**
  SightingModel - handles pet sighting reports
  Used when people report seeing lost pets
 */
class SightingModel extends PetRelatedModel
{

    //  Tell which table this model uses

    protected function getTableName(): string
    {
        return 'sightings';
    }


    //  Which fields can be searched

    protected function getSearchableFields(): array
    {
        return ['comment', 'latitude', 'longitude', 'timestamp'];
    }

    /**
      Get all lost pets for reporting sightings
      Used on the "report sighting" page
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


    //  Add a new sighting report

    public function addSighting($pet_id, $user_id, $comment, $latitude, $longitude, $timestamp, $reward = 0)
    {
        $sql = "INSERT INTO sightings (pet_id, user_id, comment, latitude, longitude, timestamp, reward)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $params = [$pet_id, $user_id, $comment, $latitude, $longitude, $timestamp, $reward];
        $stmt = $this->executeStatement($sql, $params);
        return $stmt && $stmt->rowCount() > 0;
    }

    /**
      Get sightings by specific user
      Used on user profile page
     */
    public function getSightingsByUser($user_id, $search = '')
    {
        $sql = "SELECT s.*, p.name as pet_name, p.photo_url, p.description, p.species, p.breed, p.color
                FROM sightings s
                JOIN pets p ON s.pet_id = p.id
                WHERE s.user_id = ?";

        $params = [$user_id];

        // Add search if provided
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


    //  Delete a sighting - only if user owns it

    public function deleteSighting($sighting_id, $user_id)
    {
        $sql = "DELETE FROM sightings WHERE id = ? AND user_id = ?";
        $stmt = $this->executeStatement($sql, [$sighting_id, $user_id]);
        return $stmt && $stmt->rowCount() > 0;
    }


    //  Get recent sightings for dashboard

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