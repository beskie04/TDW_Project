<?php
require_once __DIR__ . '/BaseModel.php';

class PartenaireModel extends BaseModel
{
    protected $table = 'partenaires';
    protected $primaryKey = 'id_partenaire';

    /**
     * Get all active partners
     */
    public function getAllActive()
    {
        $sql = "SELECT * FROM {$this->table} WHERE actif = 1 ORDER BY nom";
        return $this->query($sql);
    }

    /**
     * Get partners by type
     */
    public function getByType($type)
    {
        $sql = "SELECT * FROM {$this->table} WHERE type_partenaire = :type AND actif = 1 ORDER BY nom";
        return $this->query($sql, ['type' => $type]);
    }

    /**
     * Get project partners
     */
    public function getProjectPartners($projetId)
    {
        $sql = "SELECT p.*, pp.role_partenaire, pp.date_debut, pp.date_fin, pp.description as collab_description
                FROM {$this->table} p
                INNER JOIN projet_partenaires pp ON p.id_partenaire = pp.id_partenaire
                WHERE pp.id_projet = :projet_id
                ORDER BY p.nom";

        return $this->query($sql, ['projet_id' => $projetId]);
    }

    /**
     * Add partner to project
     */
    public function addToProject($projetId, $partenaireId, $role = 'Collaborateur', $dateDebut = null, $dateFin = null, $description = null)
    {
        $sql = "INSERT INTO projet_partenaires (id_projet, id_partenaire, role_partenaire, date_debut, date_fin, description)
                VALUES (:projet_id, :partenaire_id, :role, :date_debut, :date_fin, :description)";

        return $this->execute($sql, [
            'projet_id' => $projetId,
            'partenaire_id' => $partenaireId,
            'role' => $role,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'description' => $description
        ]);
    }

    /**
     * Remove partner from project
     */
    public function removeFromProject($projetId, $partenaireId)
    {
        $sql = "DELETE FROM projet_partenaires WHERE id_projet = :projet_id AND id_partenaire = :partenaire_id";
        return $this->execute($sql, ['projet_id' => $projetId, 'partenaire_id' => $partenaireId]);
    }

    /**
     * Remove all partners from project
     */
    public function removeAllFromProject($projetId)
    {
        $sql = "DELETE FROM projet_partenaires WHERE id_projet = :projet_id";
        return $this->execute($sql, ['projet_id' => $projetId]);
    }

    /**
     * Sync project partners
     */
    public function syncProjectPartners($projetId, $partenaires)
    {
        // Remove all existing
        $this->removeAllFromProject($projetId);

        // Add new ones
        if (!empty($partenaires)) {
            foreach ($partenaires as $partenaire) {
                if (empty($partenaire['id_partenaire']))
                    continue;

                $this->addToProject(
                    $projetId,
                    $partenaire['id_partenaire'],
                    $partenaire['role_partenaire'] ?? 'Collaborateur',
                    $partenaire['date_debut'] ?? null,
                    $partenaire['date_fin'] ?? null,
                    $partenaire['description'] ?? null
                );
            }
        }

        return true;
    }
}
?>