<?php
require_once __DIR__ . '/BaseModel.php';

class EquipeModel extends BaseModel
{
    protected $table = 'equipes';
    protected $primaryKey = 'id';

    /**
     * Récupérer toutes les équipes avec leurs chefs
     */
    public function getAllWithChefs()
    {
        $sql = "SELECT e.*, 
                       m.nom as chef_nom, 
                       m.prenom as chef_prenom,
                       m.photo as chef_photo
                FROM {$this->table} e
                LEFT JOIN membres m ON e.chef_id = m.id_membre
                ORDER BY e.nom";

        return $this->query($sql);
    }

    /**
     * Récupérer une équipe avec tous ses détails
     */
    public function getByIdWithDetails($id)
    {
        $sql = "SELECT e.*, 
                       m.nom as chef_nom, 
                       m.prenom as chef_prenom,
                       m.email as chef_email,
                       m.photo as chef_photo,
                       m.grade as chef_grade
                FROM {$this->table} e
                LEFT JOIN membres m ON e.chef_id = m.id_membre
                WHERE e.{$this->primaryKey} = :id";

        $result = $this->query($sql, ['id' => $id]);
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Récupérer les membres d'une équipe
     */
    public function getMembres($equipeId)
    {
        $sql = "SELECT m.*, em.date_ajout
                FROM membres m
                INNER JOIN equipe_membres em ON m.id_membre = em.id_membre
                WHERE em.id_equipe = :equipe_id AND m.actif = 1
                ORDER BY m.nom, m.prenom";

        return $this->query($sql, ['equipe_id' => $equipeId]);
    }

    /**
     * Ajouter un membre à une équipe
     */
    public function addMembre($equipeId, $membreId)
    {
        // Vérifier si déjà membre
        $sql = "SELECT COUNT(*) as total FROM equipe_membres 
                WHERE id_equipe = :equipe AND id_membre = :membre";
        $result = $this->query($sql, ['equipe' => $equipeId, 'membre' => $membreId]);

        if ($result[0]['total'] > 0) {
            return false; // Déjà membre
        }

        $sql = "INSERT INTO equipe_membres (id_equipe, id_membre) VALUES (:equipe, :membre)";
        return $this->execute($sql, ['equipe' => $equipeId, 'membre' => $membreId]);
    }

    /**
     * Retirer un membre d'une équipe
     */
    public function removeMembre($equipeId, $membreId)
    {
        $sql = "DELETE FROM equipe_membres WHERE id_equipe = :equipe AND id_membre = :membre";
        return $this->execute($sql, ['equipe' => $equipeId, 'membre' => $membreId]);
    }

    /**
     * Récupérer les publications d'une équipe
     */
    public function getPublications($equipeId)
    {
        $sql = "SELECT DISTINCT p.*
                FROM publications p
                INNER JOIN equipe_membres em ON p.auteurs LIKE CONCAT('%', 
                    (SELECT CONCAT(m.nom, ' ', m.prenom) FROM membres m WHERE m.id_membre = em.id_membre), '%')
                WHERE em.id_equipe = :equipe_id
                ORDER BY p.annee DESC";

        return $this->query($sql, ['equipe_id' => $equipeId]);
    }

    /**
     * Compter les membres d'une équipe
     */
    public function countMembres($equipeId)
    {
        $sql = "SELECT COUNT(*) as total FROM equipe_membres WHERE id_equipe = :equipe_id";
        $result = $this->query($sql, ['equipe_id' => $equipeId]);
        return $result[0]['total'];
    }
}
?>