<?php
require_once __DIR__ . '/BaseModel.php';

class PublicationModel extends BaseModel
{
    protected $table = 'publications';
    protected $primaryKey = 'id';

    public function getAllWithDetails()
    {
        $sql = "SELECT p.*, 
                       t.nom_thematique as domaine_nom
                FROM {$this->table} p
                LEFT JOIN thematiques t ON p.id_thematique = t.id_thematique
                ORDER BY p.annee DESC, p.titre ASC";

        return $this->query($sql);
    }

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
     * Filtrer les publications - WITH DEBUG LOGGING
     */
    public function filter($filters = [])
    {
        // Start with validated publications only
        $sql = "SELECT p.*, t.nom_thematique as domaine_nom
            FROM {$this->table} p
            LEFT JOIN thematiques t ON p.id_thematique = t.id_thematique
            WHERE p.validee = 1";

        $params = [];

        // Add filters ONLY if they exist in the array
        if (isset($filters['annee'])) {
            $sql .= " AND p.annee = :annee";
            $params['annee'] = $filters['annee'];
        }

        if (isset($filters['type'])) {
            $sql .= " AND p.type = :type";
            $params['type'] = $filters['type'];
        }

        if (isset($filters['domaine'])) {
            $sql .= " AND p.id_thematique = :domaine";
            $params['domaine'] = $filters['domaine'];
        }

        if (isset($filters['auteur'])) {
            $sql .= " AND p.auteurs LIKE :auteur";
            $params['auteur'] = '%' . $filters['auteur'] . '%';
        }

        if (isset($filters['search'])) {
            $sql .= " AND (p.titre LIKE :search OR p.auteurs LIKE :search OR p.resume LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY p.annee DESC, p.titre ASC";

        return $this->query($sql, $params);
    }
    public function getYears()
    {
        $sql = "SELECT DISTINCT annee FROM {$this->table} WHERE validee = 1 ORDER BY annee DESC";
        return $this->query($sql);
    }

    public function getDomaines()
    {
        $sql = "SELECT DISTINCT t.id_thematique, t.nom_thematique 
                FROM thematiques t
                INNER JOIN publications p ON t.id_thematique = p.id_thematique
                WHERE p.validee = 1
                ORDER BY t.nom_thematique";
        return $this->query($sql);
    }

    public function getAuteurs()
    {
        $sql = "SELECT DISTINCT auteurs FROM {$this->table} WHERE validee = 1 ORDER BY auteurs";
        $results = $this->query($sql);

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

    public function getByDomaine($domaineId)
    {
        $sql = "SELECT p.*, t.nom_thematique as domaine_nom
                FROM {$this->table} p
                LEFT JOIN thematiques t ON p.id_thematique = t.id_thematique
                WHERE p.id_thematique = :domaine_id AND p.validee = 1
                ORDER BY p.annee DESC";
        return $this->query($sql, ['domaine_id' => $domaineId]);
    }

    public function getByAuteur($auteur)
    {
        $sql = "SELECT * FROM {$this->table} WHERE auteurs LIKE :auteur AND validee = 1 ORDER BY annee DESC";
        return $this->query($sql, ['auteur' => '%' . $auteur . '%']);
    }

    public function validatePublication($id)
    {
        $sql = "UPDATE {$this->table} SET validee = 1 WHERE {$this->primaryKey} = :id";
        return $this->execute($sql, ['id' => $id]);
    }

    public function getStatistics()
    {
        $stats = [];

        $sql = "SELECT type, COUNT(*) as total
                FROM {$this->table}
                GROUP BY type
                ORDER BY total DESC";
        $stats['par_type'] = $this->query($sql);

        $sql = "SELECT annee, COUNT(*) as total
                FROM {$this->table}
                GROUP BY annee
                ORDER BY annee DESC";
        $stats['par_annee'] = $this->query($sql);

        $stats['total'] = $this->count();

        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE validee = 1";
        $result = $this->query($sql);
        $stats['validees'] = $result[0]['total'];

        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE validee = 0";
        $result = $this->query($sql);
        $stats['en_attente'] = $result[0]['total'];

        return $stats;
    }
}
?>