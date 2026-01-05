<?php
require_once __DIR__ . '/../models/AccueilModel.php';
require_once __DIR__ . '/../views/AccueilView.php';

class AccueilController
{
    private $model;
    private $view;

    public function __construct()
    {
        $this->model = new AccueilModel();
        $this->view = new AccueilView();
    }

    /**
     * Page d'accueil
     */
    public function index()
    {
        // Slider
        $actualitesSlider = $this->model->getActualitesSlider();

        // Section 1: Actualités scientifiques
        $actualites = $this->model->getActualitesScientifiques();

        // Section 2: Organigramme
        $organigramme = $this->model->getOrganigramme();

        // Section 3: Événements à venir avec pagination
        $page = isset($_GET['page_events']) ? (int) $_GET['page_events'] : 1;
        $evenements = $this->model->getEvenementsAvenir($page, 6);
        $totalEvenements = $this->model->countEvenementsAvenir();
        $totalPages = ceil($totalEvenements / 6);

        // Section 4: Partenaires
        $partenaires = $this->model->getPartenaires();

        $this->view->render(
            $actualitesSlider,
            $actualites,
            $organigramme,
            $evenements,
            $page,
            $totalPages,
            $partenaires
        );
    }
}
?>