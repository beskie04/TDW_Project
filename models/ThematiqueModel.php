<?php
require_once __DIR__ . '/BaseModel.php';

class ThematiqueModel extends BaseModel
{
    protected $table = 'thematiques';
    protected $primaryKey = 'id_thematique';

    /**
     * Récupérer toutes les thématiques actives
     */
    public function getAllActives()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY nom_thematique";
        return $this->query($sql);
    }
}
?>