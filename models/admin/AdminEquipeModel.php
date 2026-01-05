<?php
require_once __DIR__ . '/../BaseModel.php';

class AdminEquipeModel extends BaseModel
{
    protected $table = 'equipes';
    protected $primaryKey = 'id';

    /**
     * Récupérer toutes les équipes avec détails du chef
     */
    public function getAllWithChef()
    {
        $sql = "SELECT 
                    e.*,
                    m.nom as chef_nom,
                    m.prenom as chef_prenom,
                    m.grade as chef_grade,
                    (SELECT COUNT(*) FROM equipe_membres em WHERE em.id_equipe = e.id) as nb_membres
                FROM {$this->table} e
                LEFT JOIN membres m ON e.chef_id = m.id_membre
                ORDER BY e.nom";

        return $this->query($sql);
    }

    /**
     * Récupérer une équipe avec tous ses détails
     */
    public function getWithDetails($id)
    {
        $sql = "SELECT 
                    e.*,
                    m.nom as chef_nom,
                    m.prenom as chef_prenom,
                    m.email as chef_email,
                    m.grade as chef_grade,
                    m.photo as chef_photo
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
        $sql = "SELECT 
                    m.*,
                    em.date_ajout
                FROM membres m
                INNER JOIN equipe_membres em ON m.id_membre = em.id_membre
                WHERE em.id_equipe = :equipe_id
                ORDER BY m.nom, m.prenom";

        return $this->query($sql, ['equipe_id' => $equipeId]);
    }

    /**
     * Récupérer les membres non encore dans l'équipe
     */
    public function getMembresDisponibles($equipeId)
    {
        $sql = "SELECT m.*
                FROM membres m
                WHERE m.actif = 1
                AND m.id_membre NOT IN (
                    SELECT id_membre 
                    FROM equipe_membres 
                    WHERE id_equipe = :equipe_id
                )
                ORDER BY m.nom, m.prenom";

        return $this->query($sql, ['equipe_id' => $equipeId]);
    }

    /**
     * Ajouter un membre à une équipe
     */
    public function addMembre($equipeId, $membreId)
    {
        $sql = "INSERT INTO equipe_membres (id_equipe, id_membre, date_ajout) 
                VALUES (:equipe_id, :membre_id, NOW())";

        return $this->query($sql, [
            'equipe_id' => $equipeId,
            'membre_id' => $membreId
        ]);
    }

    /**
     * Retirer un membre d'une équipe
     */
    public function removeMembre($equipeId, $membreId)
    {
        $sql = "DELETE FROM equipe_membres 
                WHERE id_equipe = :equipe_id AND id_membre = :membre_id";

        return $this->query($sql, [
            'equipe_id' => $equipeId,
            'membre_id' => $membreId
        ]);
    }

    /**
     * Récupérer les ressources (équipements) allouées à une équipe
     * = équipements réservés par les membres de l'équipe
     */
    public function getRessources($equipeId)
    {
        $sql = "SELECT DISTINCT
                    eq.*,
                    r.date_debut,
                    r.date_fin,
                    r.statut,
                    m.nom as membre_nom,
                    m.prenom as membre_prenom
                FROM equipements eq
                INNER JOIN reservations r ON eq.id = r.id_equipement
                INNER JOIN membres m ON r.id_membre = m.id_membre
                INNER JOIN equipe_membres em ON m.id_membre = em.id_membre
                WHERE em.id_equipe = :equipe_id
                AND r.statut = 'active'
                ORDER BY r.date_debut DESC";

        return $this->query($sql, ['equipe_id' => $equipeId]);
    }

    /**
     * Récupérer les publications d'une équipe
     */
    public function getPublications($equipeId)
    {
        $sql = "SELECT DISTINCT p.*
                FROM publications p
                INNER JOIN publication_auteurs pa ON p.id = pa.id_publication
                INNER JOIN membres m ON pa.id_membre = m.id_membre
                INNER JOIN equipe_membres em ON m.id_membre = em.id_membre
                WHERE em.id_equipe = :equipe_id
                ORDER BY p.annee DESC, p.titre";

        return $this->query($sql, ['equipe_id' => $equipeId]);
    }

    /**
     * Statistiques des équipes
     */
    public function getStatistics()
    {
        $stats = [];

        // Total équipes
        $stats['total'] = $this->count();

        // Équipe avec le plus de membres
        $sql = "SELECT e.nom, COUNT(em.id_membre) as nb_membres
                FROM equipes e
                LEFT JOIN equipe_membres em ON e.id = em.id_equipe
                GROUP BY e.id
                ORDER BY nb_membres DESC
                LIMIT 1";
        $result = $this->query($sql);
        $stats['plus_grande'] = !empty($result) ? $result[0] : null;

        // Moyenne de membres par équipe
        $sql = "SELECT AVG(cnt) as moyenne
                FROM (
                    SELECT COUNT(*) as cnt
                    FROM equipe_membres
                    GROUP BY id_equipe
                ) as t";
        $result = $this->query($sql);
        $stats['moyenne_membres'] = !empty($result) ? round($result[0]['moyenne'], 1) : 0;

        return $stats;
    }
}
?>