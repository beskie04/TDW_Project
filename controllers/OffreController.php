<?php
require_once __DIR__ . '/../models/OffreModel.php';
require_once __DIR__ . '/../views/OffreView.php';

class OffreController
{
    private $model;
    private $view;

    public function __construct()
    {
        $this->model = new OffreModel();
        $this->view = new OffreView();
    }

    /**
     * Liste des offres
     */
    public function index()
    {
        $filters = [
            'type' => $_GET['type'] ?? ''
        ];

        $offres = $this->model->getActiveOffres($filters);
        $this->view->renderListe($offres);
    }

    /**
     * Détails d'une offre
     */
    public function details()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=offres');
            exit;
        }

        $offre = $this->model->getById($id);

        if (!$offre || $offre['statut'] !== 'active') {
            header('Location: ?page=offres');
            exit;
        }

        $this->view->renderDetails($offre);
    }
}
?>