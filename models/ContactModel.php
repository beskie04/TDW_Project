<?php
require_once __DIR__ . '/BaseModel.php';

class ContactModel extends BaseModel
{
    protected $table = 'contacts';
    protected $primaryKey = 'id_contact';

    /**
     * Récupérer tous les messages avec filtres
     */
    public function getAllWithFilters($filters = [])
    {
        $conditions = [];
        $params = [];

        $sql = "SELECT * FROM {$this->table}";

        if (!empty($filters['statut'])) {
            $conditions[] = "statut = :statut";
            $params['statut'] = $filters['statut'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = "(nom LIKE :search OR email LIKE :search OR sujet LIKE :search OR message LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY date_envoi DESC";

        return $this->query($sql, $params);
    }

    /**
     * Marquer un message comme lu
     */
    public function markAsRead($id)
    {
        return $this->update($id, [
            'statut' => 'lu',
            'date_lecture' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Archiver un message
     */
    public function archive($id)
    {
        return $this->update($id, ['statut' => 'archive']);
    }

    /**
     * Obtenir les statistiques
     */
    public function getStatistics()
    {
        $stats = [];

        // Total par statut
        $sql = "SELECT statut, COUNT(*) as total FROM {$this->table} GROUP BY statut";
        $results = $this->query($sql);

        foreach ($results as $row) {
            $stats[$row['statut']] = $row['total'];
        }

        // Total général
        $stats['total'] = array_sum($stats);

        // Messages cette semaine
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE date_envoi >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $result = $this->query($sql);
        $stats['cette_semaine'] = $result[0]['total'] ?? 0;

        return $stats;
    }

    /**
     * Créer un nouveau message de contact
     */
    public function createMessage($data)
    {
        $required = ['nom', 'email', 'sujet', 'message'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }

        return $this->insert([
            'nom' => $data['nom'],
            'email' => $data['email'],
            'sujet' => $data['sujet'],
            'message' => $data['message'],
            'statut' => 'nouveau'
        ]);
    }
}
?>