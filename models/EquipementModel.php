<?php
require_once __DIR__ . '/BaseModel.php';

class EquipementModel extends BaseModel
{
    protected $table = 'equipements';
    protected $primaryKey = 'id';

    /**
     * Récupérer tous les équipements avec nombre de réservations
     */
    public function getAllWithReservations()
    {
        $sql = "SELECT e.*, 
                       COUNT(r.id) as nb_reservations_actives
                FROM {$this->table} e
                LEFT JOIN reservations r ON e.id = r.id_equipement AND r.statut = 'active'
                GROUP BY e.id
                ORDER BY e.nom";

        return $this->query($sql);
    }

    /**
     * Filtrer les équipements
     */
    public function filter($filters = [])
    {
        $conditions = [];
        $params = [];

        $sql = "SELECT e.*, 
                       COUNT(r.id) as nb_reservations_actives
                FROM {$this->table} e
                LEFT JOIN reservations r ON e.id = r.id_equipement AND r.statut = 'active'";

        if (!empty($filters['type'])) {
            $conditions[] = "e.type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['etat'])) {
            $conditions[] = "e.etat = :etat";
            $params['etat'] = $filters['etat'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = "(e.nom LIKE :search OR e.description LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " GROUP BY e.id ORDER BY e.nom";

        return $this->query($sql, $params);
    }

    /**
     * Vérifier la disponibilité d'un équipement
     */
    public function isAvailable($equipementId, $dateDebut, $dateFin)
    {
        $sql = "SELECT COUNT(*) as total FROM reservations
                WHERE id_equipement = :id
                AND statut = 'active'
                AND (
                    (date_debut <= :debut AND date_fin >= :debut)
                    OR (date_debut <= :fin AND date_fin >= :fin)
                    OR (date_debut >= :debut AND date_fin <= :fin)
                )";

        $result = $this->query($sql, [
            'id' => $equipementId,
            'debut' => $dateDebut,
            'fin' => $dateFin
        ]);

        return $result[0]['total'] == 0;
    }

    /**
     * Réserver un équipement
     */
    public function reserver($equipementId, $membreId, $dateDebut, $dateFin)
    {
        // Vérifier que l'équipement est libre
        $equipement = $this->getById($equipementId);
        if ($equipement['etat'] !== 'libre') {
            return ['success' => false, 'error' => 'Équipement non disponible'];
        }

        // Vérifier les conflits de réservation
        if (!$this->isAvailable($equipementId, $dateDebut, $dateFin)) {
            return ['success' => false, 'error' => 'Créneau déjà réservé'];
        }

        $sql = "INSERT INTO reservations (id_equipement, id_membre, date_debut, date_fin, statut)
                VALUES (:equipement, :membre, :debut, :fin, 'active')";

        $success = $this->execute($sql, [
            'equipement' => $equipementId,
            'membre' => $membreId,
            'debut' => $dateDebut,
            'fin' => $dateFin
        ]);

        if ($success) {
            // Mettre à jour l'état si nécessaire
            $this->update($equipementId, ['etat' => 'reserve']);
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Erreur lors de la réservation'];
    }

    /**
     * Récupérer les réservations d'un équipement
     */
    public function getReservations($equipementId, $includeHistory = false)
    {
        $sql = "SELECT r.*, 
                       m.nom as membre_nom, 
                       m.prenom as membre_prenom,
                       m.email as membre_email
                FROM reservations r
                INNER JOIN membres m ON r.id_membre = m.id_membre
                WHERE r.id_equipement = :equipement_id";

        if (!$includeHistory) {
            $sql .= " AND r.statut = 'active'";
        }

        $sql .= " ORDER BY r.date_debut DESC";

        return $this->query($sql, ['equipement_id' => $equipementId]);
    }

    /**
     * Récupérer les réservations d'un membre
     */
    public function getReservationsByMembre($membreId)
    {
        $sql = "SELECT r.*, 
                       e.nom as equipement_nom,
                       e.type as equipement_type
                FROM reservations r
                INNER JOIN equipements e ON r.id_equipement = e.id
                WHERE r.id_membre = :membre_id
                AND r.statut = 'active'
                ORDER BY r.date_debut DESC";

        return $this->query($sql, ['membre_id' => $membreId]);
    }

    /**
     * Annuler une réservation
     */
    public function annulerReservation($reservationId)
    {
        $sql = "UPDATE reservations SET statut = 'annulee' WHERE id = :id";
        return $this->execute($sql, ['id' => $reservationId]);
    }

    /**
     * Statistiques des équipements
     */
    public function getStatistics()
    {
        $stats = [];

        // Total
        $stats['total'] = $this->count();

        // Par type
        $sql = "SELECT type, COUNT(*) as total
                FROM {$this->table}
                GROUP BY type
                ORDER BY total DESC";
        $stats['par_type'] = $this->query($sql);

        // Par état
        $sql = "SELECT etat, COUNT(*) as total
                FROM {$this->table}
                GROUP BY etat";
        $stats['par_etat'] = $this->query($sql);

        // Taux d'utilisation
        $sql = "SELECT COUNT(DISTINCT id_equipement) as total
                FROM reservations
                WHERE statut = 'active'
                AND date_debut <= NOW()
                AND date_fin >= NOW()";
        $result = $this->query($sql);
        $stats['en_utilisation'] = $result[0]['total'];

        return $stats;
    }
}
?>