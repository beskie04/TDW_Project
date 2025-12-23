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
        // Récupérer les données via le modèle
        $actualites = $this->model->getActualites();
        $publications = $this->model->getPublicationsRecentes();
        $projets = $this->model->getProjetsEnCours();
        $equipes = $this->model->getEquipesAvecMembres();

        $this->view->render($actualites, $publications, $projets, $equipes);
    }
}
?>