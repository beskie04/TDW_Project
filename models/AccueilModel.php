<?php
require_once __DIR__ . '/BaseModel.php';

class AccueilModel extends BaseModel
{
    /**
     * Récupérer les actualités pour le slider (5 max)
     */
    public function getActualitesSlider()
    {
        $sql = "SELECT * FROM actualites 
                WHERE actif = 1 
                ORDER BY ordre_affichage ASC, date_publication DESC 
                LIMIT 5";

        return $this->query($sql);
    }

    /**
     * SECTION 1: Récupérer les actualités scientifiques (6)
     */
    public function getActualitesScientifiques()
    {
        $sql = "SELECT * FROM actualites 
                WHERE actif = 1 
                ORDER BY date_publication DESC 
                LIMIT 6";

        return $this->query($sql);
    }

    /**
     * SECTION 2: Récupérer l'organigramme (Directeur + postes clés)
     */
    public function getOrganigramme()
    {
        $sql = "SELECT * FROM membres 
                WHERE poste IN ('Directeur du Laboratoire', 'Chef d\'Équipe', 'Adjoint Administratif')
                AND actif = 1
                ORDER BY 
                    CASE poste
                        WHEN 'Directeur du Laboratoire' THEN 1
                        WHEN 'Chef d\'Équipe' THEN 2
                        WHEN 'Adjoint Administratif' THEN 3
                        ELSE 4
                    END,
                    nom ASC";

        return $this->query($sql);
    }

    /**
     * SECTION 3: Récupérer les événements à venir avec pagination
     */
    public function getEvenementsAvenir($page = 1, $perPage = 6)
    {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM evenements 
                WHERE date_debut >= CURDATE() 
                AND est_publie = 'à venir'
                ORDER BY date_debut ASC 
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Compter le total d'événements à venir
     */
    public function countEvenementsAvenir()
    {
        $sql = "SELECT COUNT(*) as total FROM evenements 
                WHERE date_debut >= CURDATE() 
                AND est_publie = 'à venir'";

        $result = $this->query($sql);
        return $result[0]['total'] ?? 0;
    }

    /**
     * SECTION 4: Récupérer les partenaires (universités, entreprises, organismes)
     */
    public function getPartenaires()
    {
        $sql = "SELECT * FROM partenaires 
                ORDER BY type_partenaire, nom";

        return $this->query($sql);
    }
}
?>