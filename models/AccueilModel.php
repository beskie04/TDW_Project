<?php
require_once __DIR__ . '/BaseModel.php';

class AccueilModel extends BaseModel
{

    /**
     * Récupérer les actualités actives pour le diaporama
     */
    public function getActualites()
    {
        $sql = "SELECT * FROM actualites 
                WHERE actif = 1 
                ORDER BY ordre_affichage ASC, date_publication DESC 
                LIMIT 5";

        return $this->query($sql);
    }

    /**
     * Récupérer les publications récentes
     */
    public function getPublicationsRecentes($limit = 5)
    {
        $sql = "SELECT * FROM publications 
                ORDER BY annee DESC, created_at DESC 
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Récupérer les projets en cours
     */
    public function getProjetsEnCours($limit = 4)
    {
        $sql = "SELECT p.*, t.nom_thematique as thematique_nom
                FROM projets p
                LEFT JOIN thematiques t ON p.id_thematique = t.id_thematique
                LEFT JOIN statuts_projet s ON p.id_statut = s.id_statut
                WHERE s.nom_statut LIKE '%cours%'
                ORDER BY p.date_debut DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Récupérer toutes les équipes avec nombre de membres
     */
    public function getEquipesAvecMembres()
    {
        $sql = "SELECT e.*, 
                       COUNT(em.id_membre) as nb_membres
                FROM equipes e
                LEFT JOIN equipe_membres em ON e.id = em.id_equipe
                GROUP BY e.id
                ORDER BY e.nom";

        return $this->query($sql);
    }

    /**
     * Récupérer les statistiques pour la page d'accueil
     */
    public function getStatistiques()
    {
        $stats = [];

        // Total projets en cours
        $sql = "SELECT COUNT(*) as total FROM projets p
                INNER JOIN statuts_projet s ON p.id_statut = s.id_statut
                WHERE s.nom_statut LIKE '%cours%'";
        $result = $this->query($sql);
        $stats['projets_en_cours'] = $result[0]['total'];

        // Total publications
        $sql = "SELECT COUNT(*) as total FROM publications";
        $result = $this->query($sql);
        $stats['publications'] = $result[0]['total'];

        // Total membres actifs
        $sql = "SELECT COUNT(*) as total FROM membres WHERE actif = 1";
        $result = $this->query($sql);
        $stats['membres'] = $result[0]['total'];

        // Total équipes
        $sql = "SELECT COUNT(*) as total FROM equipes";
        $result = $this->query($sql);
        $stats['equipes'] = $result[0]['total'];

        return $stats;
    }
}
?>