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
    public function isAvailable($equipementId, $dateDebut, $dateFin, $excludeReservationId = null)
    {
        // ✅ SOLUTION : Utiliser des placeholders uniques
        $sql = "SELECT COUNT(*) as total FROM reservations
            WHERE id_equipement = :id
            AND statut = 'active'";

        if ($excludeReservationId) {
            $sql .= " AND id != :exclude_id";
        }

        // ✅ Placeholders uniques : debut1, debut2, fin1, fin2
        $sql .= " AND (
                (date_debut <= :debut1 AND date_fin >= :debut2)
                OR (date_debut <= :fin1 AND date_fin >= :fin2)
                OR (date_debut >= :debut3 AND date_fin <= :fin3)
            )";

        $params = [
            'id' => $equipementId,
            'debut1' => $dateDebut,
            'debut2' => $dateDebut,
            'debut3' => $dateDebut,
            'fin1' => $dateFin,
            'fin2' => $dateFin,
            'fin3' => $dateFin
        ];

        if ($excludeReservationId) {
            $params['exclude_id'] = $excludeReservationId;
        }

        $result = $this->query($sql, $params);
        return $result[0]['total'] == 0;
    }

    /**
     * Réserver un équipement
     */
    public function reserver($equipementId, $membreId, $dateDebut, $dateFin)
    {
        // Vérifier que l'équipement existe
        $equipement = $this->getById($equipementId);
        if (!$equipement) {
            return ['success' => false, 'error' => 'Équipement introuvable'];
        }

        // Vérifier les conflits de réservation
        if (!$this->isAvailable($equipementId, $dateDebut, $dateFin)) {
            return ['success' => false, 'error' => 'Créneau déjà réservé', 'conflit' => true];
        }

        $sql = "INSERT INTO reservations (id_equipement, id_membre, date_debut, date_fin, statut)
                VALUES (:equipement, :membre, :debut, :fin, 'active')";

        try {
            // ✅ CORRIGÉ : execute() au lieu de query()
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
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Erreur lors de la réservation: ' . $e->getMessage()];
        }

        return ['success' => false, 'error' => 'Erreur lors de la réservation'];
    }

    /**
     * Créer une demande prioritaire
     */
    public function creerDemandePrioritaire($equipementId, $membreId, $dateDebut, $dateFin, $justification)
    {
        $sql = "INSERT INTO demandes_prioritaires (id_equipement, id_membre, date_debut, date_fin, justification, statut)
                VALUES (:equipement, :membre, :debut, :fin, :justification, 'en_attente')";

        try {
            // ✅ CORRIGÉ : execute() au lieu de query()
            $result = $this->execute($sql, [
                'equipement' => $equipementId,
                'membre' => $membreId,
                'debut' => $dateDebut,
                'fin' => $dateFin,
                'justification' => $justification
            ]);

            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Erreur lors de la création de la demande: ' . $e->getMessage()];
        }
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
     * Récupérer les réservations d'un membre (actives)
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
                AND r.date_fin >= NOW()
                ORDER BY r.date_debut ASC";

        return $this->query($sql, ['membre_id' => $membreId]);
    }

    /**
     * Récupérer l'historique complet des réservations d'un membre
     */
    public function getHistoriqueReservations($membreId)
    {
        $sql = "SELECT r.*, 
                       e.nom as equipement_nom,
                       e.type as equipement_type,
                       e.etat as equipement_etat
                FROM reservations r
                INNER JOIN equipements e ON r.id_equipement = e.id
                WHERE r.id_membre = :membre_id
                ORDER BY r.created_at DESC";

        return $this->query($sql, ['membre_id' => $membreId]);
    }

    /**
     * Récupérer les demandes prioritaires d'un membre
     */
    public function getDemandesPrioritaires($membreId)
    {
        $sql = "SELECT dp.*, 
                       e.nom as equipement_nom,
                       e.type as equipement_type
                FROM demandes_prioritaires dp
                INNER JOIN equipements e ON dp.id_equipement = e.id
                WHERE dp.id_membre = :membre_id
                ORDER BY dp.created_at DESC";

        return $this->query($sql, ['membre_id' => $membreId]);
    }

    /**
     * Annuler une réservation
     */
    public function annulerReservation($reservationId, $membreId = null)
    {
        $sql = "UPDATE reservations SET statut = 'annulee' WHERE id = :id";
        $params = ['id' => $reservationId];

        // Vérifier que c'est bien le membre qui possède la réservation
        if ($membreId) {
            $sql .= " AND id_membre = :membre_id";
            $params['membre_id'] = $membreId;
        }

        try {
            // ✅ CORRIGÉ : execute() au lieu de query()
            $this->execute($sql, $params);

            // Mettre à jour l'état de l'équipement si plus de réservations
            $reservation = $this->query("SELECT id_equipement FROM reservations WHERE id = :id", ['id' => $reservationId]);
            if (!empty($reservation)) {
                $equipementId = $reservation[0]['id_equipement'];
                $activeReservations = $this->query(
                    "SELECT COUNT(*) as total FROM reservations WHERE id_equipement = :id AND statut = 'active'",
                    ['id' => $equipementId]
                );

                if ($activeReservations[0]['total'] == 0) {
                    $this->update($equipementId, ['etat' => 'libre']);
                }
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
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

        // En utilisation maintenant
        $sql = "SELECT COUNT(DISTINCT id_equipement) as total
                FROM reservations
                WHERE statut = 'active'
                AND date_debut <= NOW()
                AND date_fin >= NOW()";
        $result = $this->query($sql);
        $stats['en_utilisation'] = $result[0]['total'];

        // Taux d'occupation (%)
        if ($stats['total'] > 0) {
            $stats['taux_occupation'] = round(($stats['en_utilisation'] / $stats['total']) * 100, 1);
        } else {
            $stats['taux_occupation'] = 0;
        }

        // Top 5 équipements les plus réservés
        $sql = "SELECT e.nom, COUNT(r.id) as nb_reservations
                FROM equipements e
                LEFT JOIN reservations r ON e.id = r.id_equipement
                GROUP BY e.id
                ORDER BY nb_reservations DESC
                LIMIT 5";
        $stats['top_equipements'] = $this->query($sql);

        // Réservations par utilisateur
        $sql = "SELECT m.nom, m.prenom, COUNT(r.id) as nb_reservations
                FROM membres m
                LEFT JOIN reservations r ON m.id_membre = r.id_membre
                WHERE r.statut = 'active'
                GROUP BY m.id_membre
                ORDER BY nb_reservations DESC
                LIMIT 10";
        $stats['par_utilisateur'] = $this->query($sql);

        return $stats;
    }

    /**
     * Statistiques d'utilisation pour un équipement
     */
    public function getStatistiquesEquipement($equipementId)
    {
        $stats = [];

        // Total réservations
        $sql = "SELECT COUNT(*) as total FROM reservations WHERE id_equipement = :id";
        $result = $this->query($sql, ['id' => $equipementId]);
        $stats['total_reservations'] = $result[0]['total'];

        // Réservations actives
        $sql = "SELECT COUNT(*) as total FROM reservations WHERE id_equipement = :id AND statut = 'active'";
        $result = $this->query($sql, ['id' => $equipementId]);
        $stats['reservations_actives'] = $result[0]['total'];

        // Utilisateurs uniques
        $sql = "SELECT COUNT(DISTINCT id_membre) as total FROM reservations WHERE id_equipement = :id";
        $result = $this->query($sql, ['id' => $equipementId]);
        $stats['utilisateurs_uniques'] = $result[0]['total'];

        return $stats;
    }

    /**
     * Récupérer toutes les demandes prioritaires (admin)
     */
    public function getAllDemandesPrioritaires($filters = [])
    {
        $conditions = [];
        $params = [];

        $sql = "SELECT dp.*, 
                   e.nom as equipement_nom,
                   e.type as equipement_type,
                   e.etat as equipement_etat,
                   m.nom as membre_nom,
                   m.prenom as membre_prenom,
                   m.email as membre_email
            FROM demandes_prioritaires dp
            INNER JOIN equipements e ON dp.id_equipement = e.id
            INNER JOIN membres m ON dp.id_membre = m.id_membre";

        if (!empty($filters['statut'])) {
            $conditions[] = "dp.statut = :statut";
            $params['statut'] = $filters['statut'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY dp.created_at DESC";

        return $this->query($sql, $params);
    }

    /**
     * Approuver une demande prioritaire
     */
    public function approuverDemande($demandeId, $reponseAdmin = '')
    {
        $sql = "UPDATE demandes_prioritaires 
            SET statut = 'approuvee', 
                reponse_admin = :reponse,
                date_reponse = NOW()
            WHERE id = :id";

        return $this->execute($sql, [
            'id' => $demandeId,
            'reponse' => $reponseAdmin
        ]);
    }

    /**
     * Rejeter une demande prioritaire
     */
    public function rejeterDemande($demandeId, $reponseAdmin = '')
    {
        $sql = "UPDATE demandes_prioritaires 
            SET statut = 'rejetee', 
                reponse_admin = :reponse,
                date_reponse = NOW()
            WHERE id = :id";

        return $this->execute($sql, [
            'id' => $demandeId,
            'reponse' => $reponseAdmin
        ]);
    }

    /**
     * Récupérer toutes les réservations (pour historique admin)
     */
    public function getAllReservations($limit = null)
    {
        $sql = "SELECT r.*, 
                   e.nom as equipement_nom,
                   e.type as equipement_type,
                   m.nom as membre_nom,
                   m.prenom as membre_prenom,
                   m.email as membre_email
            FROM reservations r
            INNER JOIN equipements e ON r.id_equipement = e.id
            INNER JOIN membres m ON r.id_membre = m.id_membre
            ORDER BY r.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        return $this->query($sql);
    }
}
?>