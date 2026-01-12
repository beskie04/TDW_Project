<?php
require_once __DIR__ . '/BaseModel.php';

class UserModel extends BaseModel
{
    protected $table = 'membres';
    protected $primaryKey = 'id_membre';

    /**
     * Authentifier un utilisateur avec username
     */
    public function authenticate($username, $password)
    {
        $sql = "SELECT * FROM {$this->table} WHERE username = :username AND actif = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            // Mettre à jour la dernière connexion
            $this->updateLastLogin($user['id_membre']);
            return $user;
        }

        return false;
    }

    /**
     * Mettre à jour la dernière connexion
     */
    private function updateLastLogin($id)
    {
        $sql = "UPDATE {$this->table} SET derniere_connexion = NOW() WHERE id_membre = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    /**
     * Vérifier si un username existe déjà
     */
    public function usernameExists($username, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = :username";
        $params = ['username' => $username];

        if ($excludeId) {
            $sql .= " AND id_membre != :id";
            $params['id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['count'] > 0;
    }

    /**
     * Vérifier si un email existe déjà
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
     * Obtenir le profil complet d'un utilisateur
     */
    public function getProfileWithStats($id)
    {
        $profile = $this->getById($id);
        if (!$profile)
            return null;

        // Statistiques
        $profile['stats'] = [
            'projets' => $this->countUserProjects($id),
            'publications' => $this->countUserPublications($id),
            'reservations' => $this->countUserReservations($id),
            'equipes' => $this->countUserTeams($id)
        ];

        return $profile;
    }

    /**
     * Obtenir les projets d'un utilisateur
     */
    public function getUserProjects($id)
    {
        $sql = "SELECT p.*, pm.role_projet, pm.date_debut, pm.date_fin,
                       m.nom as responsable_nom, m.prenom as responsable_prenom,
                       t.nom_thematique, s.nom_statut
                FROM projets p
                INNER JOIN projet_membres pm ON p.id_projet = pm.id_projet
                LEFT JOIN membres m ON p.responsable_id = m.id_membre
                LEFT JOIN thematiques t ON p.id_thematique = t.id_thematique
                LEFT JOIN statuts_projet s ON p.id_statut = s.id_statut
                WHERE pm.id_membre = :id
                ORDER BY p.date_debut DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll();
    }

    /**
     * Obtenir les publications d'un utilisateur
     */
    public function getUserPublications($id)
    {
        $sql = "SELECT p.*, pa.ordre_auteur
            FROM publications p
            INNER JOIN publication_auteurs pa ON p.id = pa.id_publication
            WHERE pa.id_membre = :id
            ORDER BY p.annee DESC, pa.ordre_auteur ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll();
    }
    /**
     * Obtenir les réservations d'un utilisateur
     */
    public function getUserReservations($id, $includeExpired = false)
    {
        $sql = "SELECT r.*, e.nom as equipement_nom, e.type, e.etat
            FROM reservations r
            INNER JOIN equipements e ON r.id_equipement = e.id
            WHERE r.id_membre = :id";

        if (!$includeExpired) {
            $sql .= " AND r.date_fin >= NOW()";
        }

        $sql .= " ORDER BY r.date_debut DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll();
    }

    /**
     * Obtenir les équipes d'un utilisateur
     */
    public function getUserTeams($id)
    {
        $sql = "SELECT e.*, em.date_ajout,
                   m.nom as chef_nom, m.prenom as chef_prenom, m.photo as chef_photo,
                   (SELECT COUNT(*) FROM equipe_membres WHERE id_equipe = e.id) as nb_membres
            FROM equipes e
            INNER JOIN equipe_membres em ON e.id = em.id_equipe
            LEFT JOIN membres m ON e.chef_id = m.id_membre
            WHERE em.id_membre = :id
            ORDER BY em.date_ajout DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll();
    }

    /**
     * Obtenir les documents personnels d'un utilisateur
     */
    public function getUserDocuments($id)
    {
        $sql = "SELECT * FROM documents_membres 
                WHERE id_membre = :id 
                ORDER BY date_upload DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll();
    }

    /**
     * Ajouter un document personnel
     */
    public function addDocument($memberId, $data)
    {
        $sql = "INSERT INTO documents_membres (id_membre, nom_document, type_document, chemin_fichier, taille_fichier)
                VALUES (:id_membre, :nom_document, :type_document, :chemin_fichier, :taille_fichier)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id_membre' => $memberId,
            'nom_document' => $data['nom_document'],
            'type_document' => $data['type_document'],
            'chemin_fichier' => $data['chemin_fichier'],
            'taille_fichier' => $data['taille_fichier']
        ]);
    }

    /**
     * Supprimer un document personnel
     */
    public function deleteDocument($documentId, $memberId)
    {
        $sql = "DELETE FROM documents_membres 
                WHERE id_document = :id_document AND id_membre = :id_membre";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id_document' => $documentId,
            'id_membre' => $memberId
        ]);
    }

    /**
     * Obtenir un document spécifique
     */
    public function getDocument($documentId, $memberId)
    {
        $sql = "SELECT * FROM documents_membres 
                WHERE id_document = :id_document AND id_membre = :id_membre";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id_document' => $documentId,
            'id_membre' => $memberId
        ]);
        return $stmt->fetch();
    }

    /**
     * Mettre à jour la photo de profil
     */
    public function updatePhoto($id, $photoPath)
    {
        return $this->update($id, ['photo' => $photoPath]);
    }

    /**
     * Mettre à jour les informations de recherche
     */
    public function updateResearchInfo($id, $data)
    {
        $allowedFields = ['domaine_recherche', 'biographie', 'poste', 'grade'];
        $updateData = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        return $this->update($id, $updateData);
    }

    /**
     * Mettre à jour le mot de passe
     */
    public function updatePassword($id, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($id, ['mot_de_passe' => $hashedPassword]);
    }

    /**
     * Compter les projets d'un utilisateur
     */
    private function countUserProjects($id)
    {
        $sql = "SELECT COUNT(*) as count FROM projet_membres WHERE id_membre = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch()['count'];
    }

    /**
     * Compter les publications d'un utilisateur
     */
    private function countUserPublications($id)
    {
        $sql = "SELECT COUNT(*) as count FROM publication_auteurs WHERE id_membre = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch()['count'];
    }

    /**
     * Compter les réservations actives d'un utilisateur
     */
    private function countUserReservations($id)
    {
        $sql = "SELECT COUNT(*) as count FROM reservations 
                WHERE id_membre = :id AND date_fin >= NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch()['count'];
    }

    /**
     * Compter les équipes d'un utilisateur
     */
    private function countUserTeams($id)
    {
        $sql = "SELECT COUNT(*) as count FROM equipe_membres WHERE id_membre = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch()['count'];
    }
}
?>