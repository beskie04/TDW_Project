<?php
require_once __DIR__ . '/BaseModel.php';

class AccueilModel extends BaseModel
{
    /**
     * Récupérer les actualités pour le slider
     */
    public function getActualitesSlider()
    {
        try {
            
            $sql = "
                (SELECT 
                    id_projet as id_entite,
                    'projet' as type,
                    titre,
                    description,
                    NULL as image,
                    CONCAT('?page=projets&action=details&id=', id_projet) as lien,
                    date_debut as date_publication
                FROM projets 
                ORDER BY date_debut DESC 
                LIMIT 2)
                
                UNION ALL
                
                (SELECT 
                    id_evenement as id_entite,
                    'evenement' as type,
                    titre,
                    description,
                    image,
                    CONCAT('?page=evenements&action=details&id=', id_evenement) as lien,
                    date_debut as date_publication
                FROM evenements 
                WHERE date_debut >= CURDATE()
                ORDER BY date_debut ASC 
                LIMIT 2)
                
                UNION ALL
                
                (SELECT 
                    id as id_entite,
                    'publication' as type,
                    titre,
                    SUBSTRING(resume, 1, 200) as description,
                    NULL as image,
                    '#' as lien,
                    date_publication
                FROM publications 
                WHERE validee = 1
                ORDER BY date_publication DESC 
                LIMIT 1)
                
                ORDER BY date_publication DESC
                LIMIT 5
            ";

            $result = $this->query($sql);
            
            //  Vérifier le résultat
            if ($result === false || !is_array($result)) {
                error_log("AccueilModel::getActualitesSlider() - Requête échouée");
                return [];
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("AccueilModel::getActualitesSlider() - Erreur: " . $e->getMessage());
            return [];
        }
    }

    /**
     * SECTION 1: Récupérer les actualités scientifiques
     */
    public function getActualitesScientifiques()
    {
        try {
            $sql = "
                (SELECT 
                    id_projet as id_entite,
                    'projet' as type,
                    titre,
                    description,
                    NULL as image,
                    CONCAT('?page=projets&action=details&id=', id_projet) as lien,
                    date_debut as date_publication
                FROM projets 
                ORDER BY date_debut DESC 
                LIMIT 3)
                
                UNION ALL
                
                (SELECT 
                    id as id_entite,
                    'publication' as type,
                    titre,
                    SUBSTRING(resume, 1, 150) as description,
                    NULL as image,
                    '#' as lien,
                    date_publication
                FROM publications 
                WHERE validee = 1
                ORDER BY date_publication DESC 
                LIMIT 3)
                
                ORDER BY date_publication DESC
            ";

            $result = $this->query($sql);
            return is_array($result) ? $result : [];
            
        } catch (Exception $e) {
            error_log("AccueilModel::getActualitesScientifiques() - Erreur: " . $e->getMessage());
            return [];
        }
    }

    /**
     * SECTION 2: Récupérer l'organigramme
     */
    public function getOrganigramme()
    {
        try {
            $sql = "SELECT * FROM membres 
                    WHERE actif = 1
                    AND (
                        poste LIKE '%Directeur%' 
                        OR poste LIKE '%Chef%'
                        OR poste LIKE '%chef_equipe%'
                    )
                    ORDER BY 
                        CASE 
                            WHEN poste LIKE '%Directeur%' THEN 1
                            WHEN poste LIKE '%Chef%' THEN 2
                            ELSE 3
                        END,
                        nom ASC
                    LIMIT 6";

            $result = $this->query($sql);
            return is_array($result) ? $result : [];
            
        } catch (Exception $e) {
            error_log("AccueilModel::getOrganigramme() - Erreur: " . $e->getMessage());
            return [];
        }
    }

    /**
     * SECTION 3: Récupérer les événements à venir
     */
    public function getEvenementsAvenir($page = 1, $perPage = 6)
    {
        try {
            $offset = ($page - 1) * $perPage;

            $sql = "SELECT * FROM evenements 
                    WHERE date_debut >= CURDATE()
                    ORDER BY date_debut ASC 
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll();
            return is_array($result) ? $result : [];
            
        } catch (Exception $e) {
            error_log("AccueilModel::getEvenementsAvenir() - Erreur: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Compter le total d'événements à venir
     */
    public function countEvenementsAvenir()
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM evenements 
                    WHERE date_debut >= CURDATE()";

            $result = $this->query($sql);
            return isset($result[0]['total']) ? (int)$result[0]['total'] : 0;
            
        } catch (Exception $e) {
            error_log("AccueilModel::countEvenementsAvenir() - Erreur: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * SECTION 4: Récupérer les partenaires
     */
    public function getPartenaires()
    {
        try {
            $sql = "SELECT * FROM partenaires 
                    ORDER BY type_partenaire, nom
                    LIMIT 12";

            $result = $this->query($sql);
            return is_array($result) ? $result : [];
            
        } catch (Exception $e) {
            error_log("AccueilModel::getPartenaires() - Erreur: " . $e->getMessage());
            return [];
        }
    }
}
?>