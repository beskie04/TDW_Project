<?php
require_once __DIR__ . '/BaseModel.php';

class MembreModel extends BaseModel
{
    protected $table = 'membres';
    protected $primaryKey = 'id_membre';

    /**
     * Récupérer tous les membres actifs
     */
    public function getAllActifs()
    {
        $sql = "SELECT * FROM {$this->table} WHERE actif = 1 ORDER BY nom, prenom";
        return $this->query($sql);
    }

    /**
     * Récupérer un membre avec ses équipes
     */
    public function getByIdWithEquipes($id)
    {
        $sql = "SELECT m.* 
                FROM {$this->table} m
                WHERE m.{$this->primaryKey} = :id";

        $result = $this->query($sql, ['id' => $id]);
        $membre = !empty($result) ? $result[0] : null;

        if ($membre) {
            // Récupérer les équipes du membre
            $membre['equipes'] = $this->getEquipesByMembre($id);
        }

        return $membre;
    }

    /**
     * Récupérer les équipes d'un membre
     */
    public function getEquipesByMembre($membreId)
    {
        $sql = "SELECT e.* 
                FROM equipes e
                INNER JOIN equipe_membres em ON e.id = em.id_equipe
                WHERE em.id_membre = :membre_id";

        return $this->query($sql, ['membre_id' => $membreId]);
    }

    /**
     * Récupérer les publications d'un membre
     */
    public function getPublications($membreId)
    {
        $membre = $this->getById($membreId);
        if (!$membre)
            return [];

        $nomComplet = $membre['nom'] . ' ' . $membre['prenom'];

        $sql = "SELECT * FROM publications 
                WHERE auteurs LIKE :nom 
                ORDER BY annee DESC";

        return $this->query($sql, ['nom' => '%' . $nomComplet . '%']);
    }

    /**
     * Filtrer les membres
     */
    public function filter($filters = [])
    {
        $conditions = ['actif = 1'];
        $params = [];

        $sql = "SELECT DISTINCT m.* FROM {$this->table} m
                LEFT JOIN equipe_membres em ON m.id_membre = em.id_membre";

        if (!empty($filters['grade'])) {
            $conditions[] = "m.grade = :grade";
            $params['grade'] = $filters['grade'];
        }

        if (!empty($filters['poste'])) {
            $conditions[] = "m.poste LIKE :poste";
            $params['poste'] = '%' . $filters['poste'] . '%';
        }

        if (!empty($filters['equipe'])) {
            $conditions[] = "em.id_equipe = :equipe";
            $params['equipe'] = $filters['equipe'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = "(m.nom LIKE :search OR m.prenom LIKE :search OR m.email LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " WHERE " . implode(' AND ', $conditions);

        // Tri
        $orderBy = $filters['sort'] ?? 'nom';
        $order = $filters['order'] ?? 'ASC';
        $sql .= " ORDER BY m.{$orderBy} {$order}";

        return $this->query($sql, $params);
    }

    /**
     * Récupérer les grades disponibles
     */
    public function getGrades()
    {
        $sql = "SELECT DISTINCT grade FROM {$this->table} WHERE grade IS NOT NULL AND actif = 1 ORDER BY grade";
        return $this->query($sql);
    }

    /**
     * Récupérer les postes disponibles
     */
    public function getPostes()
    {
        $sql = "SELECT DISTINCT poste FROM {$this->table} WHERE poste IS NOT NULL AND actif = 1 ORDER BY poste";
        return $this->query($sql);
    }

    /**
     * Statistiques des membres
     */
    public function getStatistics()
    {
        $stats = [];

        // Total actifs
        $stats['total'] = $this->count(['actif' => 1]);

        // Par grade
        $sql = "SELECT grade, COUNT(*) as total
                FROM {$this->table}
                WHERE actif = 1 AND grade IS NOT NULL
                GROUP BY grade
                ORDER BY total DESC";
        $stats['par_grade'] = $this->query($sql);

        return $stats;
    }
}
?>