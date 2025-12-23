<?php
require_once __DIR__ . '/BaseModel.php';

class PublicationModel extends BaseModel
{
    protected $table = 'publications';
    protected $primaryKey = 'id';

    /**
     * Récupérer toutes les publications avec détails
     */
    public function getAllWithDetails()
    {
        $sql = "SELECT p.*, 
                       t.nom_thematique as domaine_nom
                FROM {$this->table} p
                LEFT JOIN thematiques t ON p.id_thematique = t.id_thematique
                ORDER BY p.annee DESC, p.titre ASC";

        return $this->query($sql);
    }

    /**
     * Récupérer toutes les publications validées (pour le côté public)
     */
    public function getAllValidated()
    {
        $sql = "SELECT p.*, 
                       t.nom_thematique as domaine_nom
                FROM {$this->table} p
                LEFT JOIN thematiques t ON p.id_thematique = t.id_thematique
                WHERE p.validee = 1
                ORDER BY p.annee DESC, p.titre ASC";

        return $this->query($sql);
    }

    /**
     * Récupérer les publications en attente de validation
     */
    public function getPending()
    {
        $sql = "SELECT p.*, 
                       t.nom_thematique as domaine_nom
                FROM {$this->table} p
                LEFT JOIN thematiques t ON p.id_thematique = t.id_thematique
                WHERE p.validee = 0
                ORDER BY p.created_at DESC";

        return $this->query($sql);
    }

    /**
     * Récupérer une publication par ID avec détails
     */
    public function getByIdWithDetails($id)
    {
        $sql = "SELECT p.*, 
                       t.nom_thematique as domaine_nom,
                       t.id_thematique
                FROM {$this->table} p
                LEFT JOIN thematiques t ON p.id_thematique = t.id_thematique
                WHERE p.{$this->primaryKey} = :id";

        $result = $this->query($sql, ['id' => $id]);
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Filtrer les publications
     */
    public function filter($filters = [])
    {
        $conditions = ['p.validee = 1']; // Seulement les publications validées côté public
        $params = [];

        $sql = "SELECT p.*, 
                       t.nom_thematique as domaine_nom
                FROM {$this->table} p
                LEFT JOIN thematiques t ON p.id_thematique = t.id_thematique";

        if (!empty($filters['annee'])) {
            $conditions[] = "p.annee = :annee";
            $params['annee'] = $filters['annee'];
        }

        if (!empty($filters['type'])) {
            $conditions[] = "p.type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['domaine'])) {
            $conditions[] = "p.id_thematique = :domaine";
            $params['domaine'] = $filters['domaine'];
        }

        if (!empty($filters['auteur'])) {
            $conditions[] = "p.auteurs LIKE :auteur";
            $params['auteur'] = '%' . $filters['auteur'] . '%';
        }

        if (!empty($filters['search'])) {
            $conditions[] = "(p.titre LIKE :search OR p.auteurs LIKE :search OR p.resume LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        // Tri par année décroissant par défaut
        $sql .= " ORDER BY p.annee DESC, p.titre ASC";

        return $this->query($sql, $params);
    }

    /**
     * Récupérer les années disponibles (publications validées)
     */
    public function getYears()
    {
        $sql = "SELECT DISTINCT annee FROM {$this->table} WHERE validee = 1 ORDER BY annee DESC";
        return $this->query($sql);
    }

    /**
     * Récupérer les domaines disponibles (thématiques)
     */
    public function getDomaines()
    {
        $sql = "SELECT DISTINCT t.id_thematique, t.nom_thematique 
                FROM thematiques t
                INNER JOIN publications p ON t.id_thematique = p.id_thematique
                WHERE p.validee = 1
                ORDER BY t.nom_thematique";
        return $this->query($sql);
    }

    /**
     * Récupérer la liste des auteurs (pour le filtre)
     */
    public function getAuteurs()
    {
        $sql = "SELECT DISTINCT auteurs FROM {$this->table} WHERE validee = 1 ORDER BY auteurs";
        $results = $this->query($sql);

        // Extraire les auteurs individuels
        $auteurs = [];
        foreach ($results as $row) {
            $auteursArray = explode(',', $row['auteurs']);
            foreach ($auteursArray as $auteur) {
                $auteur = trim($auteur);
                if (!empty($auteur) && !in_array($auteur, $auteurs)) {
                    $auteurs[] = $auteur;
                }
            }
        }
        sort($auteurs);
        return $auteurs;
    }

    /**
     * Récupérer les publications par domaine
     */
    public function getByDomaine($domaineId)
    {
        $sql = "SELECT p.*, t.nom_thematique as domaine_nom
                FROM {$this->table} p
                LEFT JOIN thematiques t ON p.id_thematique = t.id_thematique
                WHERE p.id_thematique = :domaine_id AND p.validee = 1
                ORDER BY p.annee DESC";
        return $this->query($sql, ['domaine_id' => $domaineId]);
    }

    /**
     * Récupérer les publications par auteur
     */
    public function getByAuteur($auteur)
    {
        $sql = "SELECT * FROM {$this->table} WHERE auteurs LIKE :auteur AND validee = 1 ORDER BY annee DESC";
        return $this->query($sql, ['auteur' => '%' . $auteur . '%']);
    }

    /**
     * Valider une publication
     */
    public function validatePublication($id)
    {
        $sql = "UPDATE {$this->table} SET validee = 1 WHERE {$this->primaryKey} = :id";
        return $this->execute($sql, ['id' => $id]);
    }

    /**
     * Statistiques des publications (pour admin)
     */
    public function getStatistics()
    {
        $stats = [];

        // Par type
        $sql = "SELECT type, COUNT(*) as total
                FROM {$this->table}
                GROUP BY type
                ORDER BY total DESC";
        $stats['par_type'] = $this->query($sql);

        // Par année
        $sql = "SELECT annee, COUNT(*) as total
                FROM {$this->table}
                GROUP BY annee
                ORDER BY annee DESC";
        $stats['par_annee'] = $this->query($sql);

        // Total
        $stats['total'] = $this->count();

        // Validées
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE validee = 1";
        $result = $this->query($sql);
        $stats['validees'] = $result[0]['total'];

        // En attente
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE validee = 0";
        $result = $this->query($sql);
        $stats['en_attente'] = $result[0]['total'];

        return $stats;
    }
}
?>