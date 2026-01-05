<?php
require_once __DIR__ . '/BaseModel.php';

class AnnonceModel extends BaseModel
{
    protected $table = 'annonces';
    protected $primaryKey = 'id_annonce';

    /**
     * Get all announcements (only published and active for public)
     */
    public function getAllActive()
    {
        $today = date('Y-m-d');

        $sql = "SELECT a.*, 
                       m.nom as auteur_nom,
                       m.prenom as auteur_prenom
                FROM {$this->table} a
                LEFT JOIN membres m ON a.auteur_id = m.id_membre
                WHERE a.est_publie = 1
                AND a.date_debut <= :today
                AND (a.date_fin IS NULL OR a.date_fin >= :today)
                ORDER BY a.priorite DESC, a.date_creation DESC";

        return $this->query($sql, ['today' => $today]);
    }

    /**
     * Get all announcements for admin (including unpublished/expired)
     */
    public function getAllForAdmin()
    {
        $sql = "SELECT a.*, 
                       m.nom as auteur_nom,
                       m.prenom as auteur_prenom
                FROM {$this->table} a
                LEFT JOIN membres m ON a.auteur_id = m.id_membre
                ORDER BY a.date_creation DESC";

        return $this->query($sql);
    }

    /**
     * Get announcement by ID with details
     */
    public function getByIdWithDetails($id)
    {
        $sql = "SELECT a.*, 
                       m.nom as auteur_nom,
                       m.prenom as auteur_prenom,
                       m.email as auteur_email
                FROM {$this->table} a
                LEFT JOIN membres m ON a.auteur_id = m.id_membre
                WHERE a.{$this->primaryKey} = :id";

        $result = $this->query($sql, ['id' => $id]);
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Check if announcement is active
     */
    public function isActive($annonce)
    {
        $today = date('Y-m-d');
        return $annonce['est_publie'] &&
            $annonce['date_debut'] <= $today &&
            (empty($annonce['date_fin']) || $annonce['date_fin'] >= $today);
    }
}
?>