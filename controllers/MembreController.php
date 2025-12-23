<?php
require_once __DIR__ . '/../models/MembreModel.php';
require_once __DIR__ . '/../models/EquipeModel.php';
require_once __DIR__ . '/../views/MembreView.php';

class MembreController
{
    private $membreModel;
    private $equipeModel;
    private $view;

    public function __construct()
    {
        $this->membreModel = new MembreModel();
        $this->equipeModel = new EquipeModel();
        $this->view = new MembreView();
    }

    /**
     * Page principale : Présentation, organigramme et équipes
     */
    public function index()
    {
        // Récupérer le directeur (celui avec le poste "Directeur du Laboratoire")
        $directeur = $this->membreModel->where([
            'poste' => ['LIKE', '%Directeur%'],
            'actif' => 1
        ], null, 'ASC', 1);

        // Récupérer toutes les équipes
        $equipes = $this->equipeModel->getAllWithChefs();

        // Compter les membres de chaque équipe
        foreach ($equipes as &$equipe) {
            $equipe['nb_membres'] = $this->equipeModel->countMembres($equipe['id']);
        }

        $this->view->renderIndex($equipes, $directeur);
    }

    /**
     * Détails d'une équipe
     */
    public function equipe()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=membres');
            exit;
        }

        $equipe = $this->equipeModel->getByIdWithDetails($id);

        if (!$equipe) {
            header('Location: ?page=membres');
            exit;
        }

        $membres = $this->equipeModel->getMembres($id);
        $publications = $this->equipeModel->getPublications($id);

        $this->view->renderEquipe($equipe, $membres, $publications);
    }

    /**
     * Biographie d'un membre
     */
    public function biographie()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=membres');
            exit;
        }

        $membre = $this->membreModel->getByIdWithEquipes($id);

        if (!$membre) {
            header('Location: ?page=membres');
            exit;
        }

        $equipes = $membre['equipes'] ?? [];
        $publications = $this->membreModel->getPublications($id);

        $this->view->renderBiographie($membre, $equipes, $publications);
    }

    /**
     * Publications d'un membre
     */
    public function publications()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=membres');
            exit;
        }

        $membre = $this->membreModel->getById($id);

        if (!$membre) {
            header('Location: ?page=membres');
            exit;
        }

        $publications = $this->membreModel->getPublications($id);

        $this->view->renderPublications($membre, $publications);
    }
}
?>