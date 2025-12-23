<?php
require_once __DIR__ . '/../models/ProjetModel.php';
require_once __DIR__ . '/../views/ProjetView.php';

class ProjetController
{
    private $model;
    private $view;

    public function __construct()
    {
        $this->model = new ProjetModel();
        $this->view = new ProjetView();
    }

    /**
     * Afficher la liste des projets
     */
    public function index()
    {
        $projets = $this->model->getAllWithDetails();

        // Récupérer les données pour les filtres
        $thematiques = $this->getThematiques();
        $statuts = $this->getStatuts();
        $responsables = $this->getResponsables();

        $this->view->renderListe($projets, $thematiques, $statuts, $responsables);
    }

    /**
     * Afficher les détails d'un projet
     */
    public function details()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=projets');
            exit;
        }

        $projet = $this->model->getByIdWithDetails($id);

        if (!$projet) {
            header('Location: ?page=projets');
            exit;
        }

        $membres = $this->model->getMembres($id);
        $publications = $this->model->getPublications($id);

        $this->view->renderDetails($projet, $membres, $publications);
    }

    /**
     * Filtrer les projets (AJAX)
     */
    public function filter()
    {
        header('Content-Type: application/json');

        $filters = [
            'thematique' => $_GET['thematique'] ?? '',
            'statut' => $_GET['statut'] ?? '',
            'responsable' => $_GET['responsable'] ?? '',
            'search' => $_GET['search'] ?? '',
            'sort' => $_GET['sort'] ?? 'date_debut',
            'order' => $_GET['order'] ?? 'DESC'
        ];

        $projets = $this->model->filter($filters);

        ob_start();
        $this->view->renderProjetsCards($projets);
        $html = ob_get_clean();

        echo json_encode([
            'success' => true,
            'html' => $html,
            'count' => count($projets)
        ]);
    }

    /**
     * Récupérer les thématiques
     */
    private function getThematiques()
    {
        $sql = "SELECT id_thematique, nom_thematique FROM thematiques ORDER BY nom_thematique";
        return $this->model->query($sql);
    }

    /**
     * Récupérer les statuts
     */
    private function getStatuts()
    {
        $sql = "SELECT * FROM statuts_projet ORDER BY nom_statut";
        return $this->model->query($sql);
    }

    /**
     * Récupérer les responsables
     */
    private function getResponsables()
    {
        $sql = "SELECT DISTINCT m.id_membre, m.nom, m.prenom 
                FROM membres m 
                INNER JOIN projets p ON m.id_membre = p.responsable_id 
                ORDER BY m.nom, m.prenom";
        return $this->model->query($sql);
    }
}
?>