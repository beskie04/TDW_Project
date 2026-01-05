<?php
require_once __DIR__ . '/../BaseModel.php';

class AdminMembreModel extends BaseModel
{
    protected $table = 'membres';
    protected $primaryKey = 'id_membre';

    /**
     * Obtenir tous les membres avec statistiques
     */
    public function getAllWithStats($filters = [])
    {
        $sql = "SELECT m.*,
                       (SELECT COUNT(*) FROM publication_auteurs pa WHERE pa.id_membre = m.id_membre) as nb_publications,
                       (SELECT COUNT(*) FROM projet_membres pm WHERE pm.id_membre = m.id_membre) as nb_projets,
                       (SELECT COUNT(*) FROM equipe_membres em WHERE em.id_membre = m.id_membre) as nb_equipes
                FROM {$this->table} m
                WHERE 1=1";

        $params = [];

        // Filter by role (member type)
        if (!empty($filters['role'])) {
            $sql .= " AND m.role = :role";
            $params['role'] = $filters['role'];
        }

        // Filter by status (active/inactive)
        if (isset($filters['actif']) && $filters['actif'] !== '') {
            $sql .= " AND m.actif = :actif";
            $params['actif'] = $filters['actif'];
        }

        // Filter by grade
        if (!empty($filters['grade'])) {
            $sql .= " AND m.grade LIKE :grade";
            $params['grade'] = '%' . $filters['grade'] . '%';
        }

        // Filter by specialite
        if (!empty($filters['specialite'])) {
            $sql .= " AND m.specialite LIKE :specialite";
            $params['specialite'] = '%' . $filters['specialite'] . '%';
        }

        // Search by name or email
        if (!empty($filters['search'])) {
            $sql .= " AND (m.nom LIKE :search OR m.prenom LIKE :search OR m.email LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        // Order base query first
        $orderBy = $filters['sort'] ?? 'date_creation';
        $order = $filters['order'] ?? 'DESC';

        // Filter by number of publications or projects (using HAVING)
        $havingClauses = [];
        if (!empty($filters['min_publications'])) {
            $havingClauses[] = "nb_publications >= :min_publications";
            $params['min_publications'] = $filters['min_publications'];
        }
        if (!empty($filters['min_projets'])) {
            $havingClauses[] = "nb_projets >= :min_projets";
            $params['min_projets'] = $filters['min_projets'];
        }

        if (!empty($havingClauses)) {
            $sql .= " HAVING " . implode(' AND ', $havingClauses);
        }

        $sql .= " ORDER BY m.{$orderBy} {$order}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Créer un nouveau membre
     */
    public function createMembre($data)
    {
        // Hash the password
        if (isset($data['mot_de_passe'])) {
            $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
        }

        // Set defaults
        $data['actif'] = $data['actif'] ?? 1;
        $data['role_systeme'] = $data['role_systeme'] ?? 'user';

        return $this->insert($data);
    }

    /**
     * Mettre à jour un membre
     */
    public function updateMembre($id, $data)
    {
        // Hash password if provided
        if (!empty($data['mot_de_passe'])) {
            $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
        } else {
            // Don't update password if not provided
            unset($data['mot_de_passe']);
        }

        return $this->update($id, $data);
    }

    /**
     * Suspendre/Activer un membre
     */
    public function toggleStatus($id)
    {
        $membre = $this->getById($id);
        if (!$membre)
            return false;

        $newStatus = $membre['actif'] ? 0 : 1;
        return $this->update($id, ['actif' => $newStatus]);
    }

    /**
     * Supprimer un membre (soft delete ou hard delete)
     */
    public function deleteMembre($id)
    {
        // Check if member has dependencies
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM publication_auteurs WHERE id_membre = :id) as publications,
                    (SELECT COUNT(*) FROM projet_membres WHERE id_membre = :id) as projets,
                    (SELECT COUNT(*) FROM reservations WHERE id_membre = :id) as reservations
                ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $deps = $stmt->fetch();

        // If has dependencies, just deactivate instead of delete
        if ($deps['publications'] > 0 || $deps['projets'] > 0 || $deps['reservations'] > 0) {
            return $this->update($id, ['actif' => 0]);
        }

        // Otherwise, hard delete
        return $this->delete($id);
    }

    /**
     * Obtenir les statistiques globales
     */
    public function getStatistics()
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN actif = 1 THEN 1 ELSE 0 END) as actifs,
                    SUM(CASE WHEN actif = 0 THEN 1 ELSE 0 END) as inactifs,
                    SUM(CASE WHEN role = 'enseignant' THEN 1 ELSE 0 END) as enseignants,
                    SUM(CASE WHEN role = 'doctorant' THEN 1 ELSE 0 END) as doctorants,
                    SUM(CASE WHEN role = 'etudiant' THEN 1 ELSE 0 END) as etudiants,
                    SUM(CASE WHEN role = 'invite' THEN 1 ELSE 0 END) as invites,
                    SUM(CASE WHEN role_systeme = 'admin' THEN 1 ELSE 0 END) as admins
                FROM {$this->table}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Obtenir tous les rôles disponibles
     */
    public function getRoles()
    {
        return [
            ['value' => 'enseignant', 'text' => 'Enseignant'],
            ['value' => 'doctorant', 'text' => 'Doctorant'],
            ['value' => 'etudiant', 'text' => 'Étudiant'],
            ['value' => 'invite', 'text' => 'Invité']
        ];
    }

    /**
     * Obtenir tous les rôles système
     */
    public function getRolesSysteme()
    {
        return [
            ['value' => 'user', 'text' => 'Utilisateur'],
            ['value' => 'admin', 'text' => 'Administrateur']
        ];
    }

    /**
     * Vérifier si un email existe (pour validation)
     */
    public function emailExists($email, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = :email";
        $params = ['email' => $email];

        if ($excludeId) {
            $sql .= " AND id_membre != :id";
            $params['id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['count'] > 0;
    }

    /**
     * Obtenir tous les grades uniques
     */
    public function getGrades()
    {
        $sql = "SELECT DISTINCT grade 
                FROM {$this->table} 
                WHERE grade IS NOT NULL AND grade != ''
                ORDER BY grade";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $grades = [];
        while ($row = $stmt->fetch()) {
            $grades[] = [
                'value' => $row['grade'],
                'text' => $row['grade']
            ];
        }

        return $grades;
    }

    /**
     * Obtenir toutes les spécialités uniques
     */
    public function getSpecialites()
    {
        $sql = "SELECT DISTINCT specialite 
                FROM {$this->table} 
                WHERE specialite IS NOT NULL AND specialite != ''
                ORDER BY specialite";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $specialites = [];
        while ($row = $stmt->fetch()) {
            $specialites[] = [
                'value' => $row['specialite'],
                'text' => $row['specialite']
            ];
        }

        return $specialites;
    }
}
?>