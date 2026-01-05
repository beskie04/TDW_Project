<?php
require_once __DIR__ . '/BaseModel.php';

class EvenementModel extends BaseModel
{
    protected $table = 'evenements';
    protected $primaryKey = 'id_evenement';

    /**
     * Get all events with details (only published for public)
     */
    public function getAllWithDetails($includeUnpublished = false)
    {
        $sql = "SELECT e.*, 
                   te.nom_type as type_nom,
                   te.couleur as type_couleur,
                   m.nom as organisateur_nom,
                   m.prenom as organisateur_prenom,
                   m.photo as organisateur_photo,
                   (SELECT COUNT(*) FROM evenement_participants WHERE id_evenement = e.id_evenement) as nb_inscrits
            FROM {$this->table} e
            LEFT JOIN types_evenement te ON e.id_type_evenement = te.id_type_evenement
            LEFT JOIN membres m ON e.organisateur_id = m.id_membre";

        // Only show published events for public view
        if (!$includeUnpublished) {
            $sql .= " WHERE e.est_publie = 1";
        }

        $sql .= " ORDER BY e.date_debut ASC";

        return $this->query($sql);
    }

    /**
     * Get all events including unpublished (for admin)
     */
    public function getAllForAdmin()
    {
        return $this->getAllWithDetails(true);
    }
    /**
     * Get event by ID with details
     */
    public function getByIdWithDetails($id)
    {
        $sql = "SELECT e.*, 
                   te.nom_type as type_nom,
                   te.couleur as type_couleur,
                   te.description as type_description,
                   m.nom as organisateur_nom,
                   m.prenom as organisateur_prenom,
                   m.email as organisateur_email,
                   m.photo as organisateur_photo,
                   (SELECT COUNT(*) FROM evenement_participants WHERE id_evenement = e.id_evenement) as nb_inscrits
            FROM {$this->table} e
            LEFT JOIN types_evenement te ON e.id_type_evenement = te.id_type_evenement
            LEFT JOIN membres m ON e.organisateur_id = m.id_membre
            WHERE e.{$this->primaryKey} = :id";

        $result = $this->query($sql, ['id' => $id]);
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Filter events
     */
    public function filter($filters = [])
    {
        $conditions = [];
        $params = [];

        $sql = "SELECT e.*, 
                       te.nom_type as type_nom,
                       te.couleur as type_couleur,
                       m.nom as organisateur_nom,
                       m.prenom as organisateur_prenom,
                       (SELECT COUNT(*) FROM evenement_participants WHERE id_evenement = e.id_evenement) as nb_inscrits
                FROM {$this->table} e
                LEFT JOIN types_evenement te ON e.id_type_evenement = te.id_type_evenement
                LEFT JOIN membres m ON e.organisateur_id = m.id_membre";

        if (!empty($filters['type'])) {
            $conditions[] = "e.id_type_evenement = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['statut'])) {
            $conditions[] = "e.statut = :statut";
            $params['statut'] = $filters['statut'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = "(e.titre LIKE :search OR e.description LIKE :search OR e.lieu LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $orderBy = $filters['sort'] ?? 'date_debut';
        $order = $filters['order'] ?? 'ASC';
        $sql .= " ORDER BY e.{$orderBy} {$order}";

        return $this->query($sql, $params);
    }

    /**
     * Get event participants
     */
    public function getParticipants($evenementId)
    {
        $sql = "SELECT ep.*, m.nom, m.prenom, m.photo, m.grade
                FROM evenement_participants ep
                LEFT JOIN membres m ON ep.id_membre = m.id_membre
                WHERE ep.id_evenement = :evenement_id
                ORDER BY ep.date_inscription DESC";

        return $this->query($sql, ['evenement_id' => $evenementId]);
    }

    /**
     * Register user for event
     */
    public function inscrire($evenementId, $membreId = null, $data = [])
    {
        // Check if event is full
        $event = $this->getById($evenementId);
        if ($event && $event['capacite_max']) {
            $nbInscrits = $this->count(['id_evenement' => $evenementId]);
            if ($nbInscrits >= $event['capacite_max']) {
                return false; // Event is full
            }
        }

        // Check if already registered
        if ($membreId) {
            $existing = $this->query(
                "SELECT * FROM evenement_participants WHERE id_evenement = :evenement_id AND id_membre = :membre_id",
                ['evenement_id' => $evenementId, 'membre_id' => $membreId]
            );
            if (!empty($existing)) {
                return false; // Already registered
            }
        }

        $insertData = [
            'id_evenement' => $evenementId,
            'id_membre' => $membreId,
            'nom' => $data['nom'] ?? null,
            'prenom' => $data['prenom'] ?? null,
            'email' => $data['email'],
            'telephone' => $data['telephone'] ?? null,
            'statut_participation' => 'inscrit'
        ];

        $sql = "INSERT INTO evenement_participants (id_evenement, id_membre, nom, prenom, email, telephone, statut_participation) 
                VALUES (:id_evenement, :id_membre, :nom, :prenom, :email, :telephone, :statut_participation)";

        return $this->execute($sql, $insertData);
    }

    /**
     * Cancel registration
     */
    public function annulerInscription($evenementId, $membreId)
    {
        $sql = "DELETE FROM evenement_participants WHERE id_evenement = :evenement_id AND id_membre = :membre_id";
        return $this->execute($sql, ['evenement_id' => $evenementId, 'membre_id' => $membreId]);
    }

    /**
     * Check if user is registered
     */
    public function isInscrit($evenementId, $membreId)
    {
        $sql = "SELECT * FROM evenement_participants WHERE id_evenement = :evenement_id AND id_membre = :membre_id";
        $result = $this->query($sql, ['evenement_id' => $evenementId, 'membre_id' => $membreId]);
        return !empty($result);
    }

    /**
     * Get event types
     */
    public function getTypes()
    {
        $sql = "SELECT * FROM types_evenement ORDER BY nom_type";
        return $this->query($sql);
    }
    /**
     * Update event status automatically based on date
     */
    public function updateStatuts()
    {
        $now = date('Y-m-d H:i:s');

        // Set to 'en cours' if started but not ended
        $sql1 = "UPDATE {$this->table} SET statut = 'en cours' 
             WHERE date_debut <= :now1 
             AND (date_fin IS NULL OR date_fin >= :now2) 
             AND statut = 'à venir'";

        $stmt1 = $this->db->prepare($sql1);
        $stmt1->execute(['now1' => $now, 'now2' => $now]);

        // Set to 'terminé' if ended
        $sql2 = "UPDATE {$this->table} SET statut = 'terminé' 
             WHERE date_fin < :now 
             AND statut != 'annulé'";

        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute(['now' => $now]);
    }
}
?>