<?php
require_once __DIR__ . '/../models/PublicationModel.php';
require_once __DIR__ . '/../views/PublicationView.php';

class PublicationController
{
    private $model;
    private $view;

    public function __construct()
    {
        $this->model = new PublicationModel();
        $this->view = new PublicationView();
    }

    /**
     * Afficher la liste des publications (seulement les validées)
     */
    public function index()
    {
        $publications = $this->model->getAllValidated();

        // Récupérer les données pour les filtres
        $years = $this->model->getYears();
        $types = TYPES_PUBLICATIONS; // Depuis constants.php
        $domaines = $this->model->getDomaines();
        $auteurs = $this->model->getAuteurs();

        // IMPORTANT: Passer les 5 paramètres dans le bon ordre
        $this->view->renderListe($publications, $years, $types, $domaines, $auteurs);
    }

    /**
     * Filtrer les publications (AJAX)
     */
    public function filter()
    {
        header('Content-Type: application/json');

        $filters = [
            'annee' => $_GET['annee'] ?? '',
            'type' => $_GET['type'] ?? '',
            'domaine' => $_GET['domaine'] ?? '',
            'auteur' => $_GET['auteur'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        $publications = $this->model->filter($filters);

        ob_start();
        $this->view->renderPublicationsList($publications);
        $html = ob_get_clean();

        echo json_encode([
            'success' => true,
            'html' => $html,
            'count' => count($publications)
        ]);
    }
}
?>