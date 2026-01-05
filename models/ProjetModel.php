<?php
require_once __DIR__ . '/BaseModel.php';

class ProjetModel extends BaseModel
{
    protected $table = 'projets';
    protected $primaryKey = 'id_projet';

    /**
     * Récupérer tous les projets avec leurs relations
     */
    public function getAllWithDetails()
    {
        $sql = "SELECT p.*, 
                       t.nom_thematique as thematique_nom,
                       s.nom_statut as statut_nom,
                       tf.nom_type as type_financement_nom,
                       m.nom as responsable_nom, 
                       m.prenom as responsable_prenom
                FROM {$this->table} p
                LEFT JOIN thematiques t ON p.id_thematique = t.id_thematique
                LEFT JOIN statuts_projet s ON p.id_statut = s.id_statut
                LEFT JOIN types_financement tf ON p.id_type_financement = tf.id_type_financement
                LEFT JOIN membres m ON p.responsable_id = m.id_membre
                ORDER BY p.date_debut DESC";

        return $this->query($sql);
    }

    /**
     * Récupérer un projet avec tous ses détails
     */
    public function getByIdWithDetails($id)
    {
        $sql = "SELECT p.*, 
                       t.nom_thematique as thematique_nom,
                       t.description as thematique_description,
                       s.nom_statut as statut_nom,
                       tf.nom_type as type_financement_nom,
                       m.nom as responsable_nom, 
                       m.prenom as responsable_prenom,
                       m.email as responsable_email,
                       m.photo as responsable_photo
                FROM {$this->table} p
                LEFT JOIN thematiques t ON p.id_thematique = t.id_thematique
                LEFT JOIN statuts_projet s ON p.id_statut = s.id_statut
                LEFT JOIN types_financement tf ON p.id_type_financement = tf.id_type_financement
                LEFT JOIN membres m ON p.responsable_id = m.id_membre
                WHERE p.{$this->primaryKey} = :id";

        $result = $this->query($sql, ['id' => $id]);
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Récupérer les membres d'un projet
     */
    public function getMembres($projetId)
    {
        $sql = "SELECT m.*, pm.role_projet, pm.date_debut, pm.date_fin, pm.id_membre
                FROM membres m
                INNER JOIN projet_membres pm ON m.id_membre = pm.id_membre
                WHERE pm.id_projet = :projet_id
                ORDER BY pm.role_projet, m.nom";

        return $this->query($sql, ['projet_id' => $projetId]);
    }

    /**
     * Ajouter un membre à un projet
     */
    public function addMembre($projetId, $membreId, $role = 'Membre', $dateDebut = null, $dateFin = null)
    {
        $sql = "INSERT INTO projet_membres (id_projet, id_membre, role_projet, date_debut, date_fin) 
                VALUES (:projet_id, :membre_id, :role, :date_debut, :date_fin)";

        return $this->execute($sql, [
            'projet_id' => $projetId,
            'membre_id' => $membreId,
            'role' => $role,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin
        ]);
    }

    /**
     * Retirer un membre d'un projet
     */
    public function removeMembre($projetId, $membreId)
    {
        $sql = "DELETE FROM projet_membres WHERE id_projet = :projet_id AND id_membre = :membre_id";
        return $this->execute($sql, ['projet_id' => $projetId, 'membre_id' => $membreId]);
    }

    /**
     * Synchroniser les membres d'un projet
     */
    public function syncMembres($projetId, $membres)
    {
        // Supprimer les anciens membres
        $this->removeAllMembres($projetId);

        // Ajouter les nouveaux membres
        if (!empty($membres)) {
            foreach ($membres as $membre) {
                $this->addMembre(
                    $projetId,
                    $membre['id_membre'],
                    $membre['role_projet'] ?? 'Membre',
                    $membre['date_debut'] ?? null,
                    $membre['date_fin'] ?? null
                );
            }
        }

        return true;
    }

    /**
     * Supprimer tous les membres d'un projet
     */
    public function removeAllMembres($projetId)
    {
        $sql = "DELETE FROM projet_membres WHERE id_projet = :projet_id";
        return $this->execute($sql, ['projet_id' => $projetId]);
    }

    /**
     * Filtrer les projets
     */
    public function filter($filters = [])
    {
        $conditions = [];
        $params = [];

        $sql = "SELECT p.*, 
                       t.nom_thematique as thematique_nom,
                       s.nom_statut as statut_nom,
                       tf.nom_type as type_financement_nom,
                       m.nom as responsable_nom, 
                       m.prenom as responsable_prenom
                FROM {$this->table} p
                LEFT JOIN thematiques t ON p.id_thematique = t.id_thematique
                LEFT JOIN statuts_projet s ON p.id_statut = s.id_statut
                LEFT JOIN types_financement tf ON p.id_type_financement = tf.id_type_financement
                LEFT JOIN membres m ON p.responsable_id = m.id_membre";

        if (!empty($filters['thematique'])) {
            $conditions[] = "p.id_thematique = :thematique";
            $params['thematique'] = $filters['thematique'];
        }

        if (!empty($filters['statut'])) {
            $conditions[] = "p.id_statut = :statut";
            $params['statut'] = $filters['statut'];
        }

        if (!empty($filters['responsable'])) {
            $conditions[] = "p.responsable_id = :responsable";
            $params['responsable'] = $filters['responsable'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = "(p.titre LIKE :search OR p.description LIKE :search OR p.objectifs LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        // Tri
        $orderBy = $filters['sort'] ?? 'date_debut';
        $order = $filters['order'] ?? 'DESC';
        $sql .= " ORDER BY p.{$orderBy} {$order}";

        return $this->query($sql, $params);
    }

    /**
     * Récupérer les publications d'un projet
     */
    public function getPublications($projetId)
    {
        $sql = "SELECT * FROM publications WHERE id_projet = :projet_id ORDER BY annee DESC";
        return $this->query($sql, ['projet_id' => $projetId]);
    }

    /**
     * Statistiques des projets
     */
    public function getStatistics()
    {
        $stats = [];

        // Par thématique
        $sql = "SELECT t.nom_thematique as nom, COUNT(p.id_projet) as total
                FROM thematiques t
                LEFT JOIN projets p ON t.id_thematique = p.id_thematique
                GROUP BY t.id_thematique, t.nom_thematique";
        $stats['par_thematique'] = $this->query($sql);

        // Par statut
        $sql = "SELECT s.nom_statut, COUNT(p.id_projet) as total
                FROM statuts_projet s
                LEFT JOIN projets p ON s.id_statut = p.id_statut
                GROUP BY s.id_statut, s.nom_statut";
        $stats['par_statut'] = $this->query($sql);

        // Par année
        $sql = "SELECT YEAR(date_debut) as annee, COUNT(*) as total
                FROM projets
                GROUP BY YEAR(date_debut)
                ORDER BY annee DESC";
        $stats['par_annee'] = $this->query($sql);

        return $stats;
    }

    public function getPartenaires($projetId)
    {
        $sql = "SELECT p.*, pp.role_partenaire, pp.date_debut, pp.date_fin
            FROM partenaires p
            INNER JOIN projet_partenaires pp ON p.id_partenaire = pp.id_partenaire
            WHERE pp.id_projet = :projet_id
            ORDER BY p.nom";

        return $this->query($sql, ['projet_id' => $projetId]);
    }
}
?>