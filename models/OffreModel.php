<?php
require_once __DIR__ . '/BaseModel.php';

class OffreModel extends BaseModel
{
    protected $table = 'offres';
    protected $primaryKey = 'id_offre';

    /**
     * Récupérer les offres actives avec filtres
     */
    public function getActiveOffres($filters = [])
    {
        $conditions = ["statut = 'active'"];
        $params = [];

        $sql = "SELECT * FROM {$this->table}";

        if (!empty($filters['type'])) {
            $conditions[] = "type = :type";
            $params['type'] = $filters['type'];
        }

        $sql .= " WHERE " . implode(' AND ', $conditions);
        $sql .= " ORDER BY date_creation DESC";

        return $this->query($sql, $params);
    }

    /**
     * Récupérer toutes les offres (admin)
     */
    public function getAllOffres($filters = [])
    {
        $conditions = [];
        $params = [];

        $sql = "SELECT * FROM {$this->table}";

        if (!empty($filters['type'])) {
            $conditions[] = "type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['statut'])) {
            $conditions[] = "statut = :statut";
            $params['statut'] = $filters['statut'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY date_creation DESC";

        return $this->query($sql, $params);
    }

    /**
     * Basculer le statut
     */
    public function toggleStatus($id)
    {
        $offre = $this->getById($id);
        if (!$offre)
            return false;

        $newStatut = $offre['statut'] === 'active' ? 'inactive' : 'active';
        return $this->update($id, ['statut' => $newStatut]);
    }
}
?>