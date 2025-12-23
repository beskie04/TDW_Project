<?php
require_once __DIR__ . '/../models/EquipementModel.php';
require_once __DIR__ . '/../views/EquipementView.php';

class EquipementController
{
    private $model;
    private $view;

    public function __construct()
    {
        $this->model = new EquipementModel();
        $this->view = new EquipementView();
    }

    /**
     * Liste des équipements
     */
    public function index()
    {
        $equipements = $this->model->getAllWithReservations();
        $types = TYPES_EQUIPEMENTS;
        $etats = ETATS_EQUIPEMENTS;

        // Si connecté, récupérer ses réservations
        $mesReservations = [];
        if (isset($_SESSION['user'])) {
            $mesReservations = $this->model->getReservationsByMembre($_SESSION['user']['id_membre']);
        }

        $this->view->renderListe($equipements, $types, $etats, $mesReservations);
    }

    /**
     * Détails d'un équipement
     */
    public function details()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=equipements');
            exit;
        }

        $equipement = $this->model->getById($id);

        if (!$equipement) {
            header('Location: ?page=equipements');
            exit;
        }

        $reservations = $this->model->getReservations($id);

        $this->view->renderDetails($equipement, $reservations);
    }

    /**
     * Formulaire de réservation
     */
    public function reserver()
    {
        // Vérifier connexion
        if (!isset($_SESSION['user'])) {
            BaseView::setFlash('Vous devez être connecté pour réserver un équipement', 'error');
            header('Location: ?page=login');
            exit;
        }

        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=equipements');
            exit;
        }

        $equipement = $this->model->getById($id);

        if (!$equipement || $equipement['etat'] !== 'libre') {
            BaseView::setFlash('Cet équipement n\'est pas disponible', 'error');
            header('Location: ?page=equipements');
            exit;
        }

        $this->view->renderReservation($equipement);
    }

    /**
     * Confirmer la réservation
     */
    public function confirmer_reservation()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=equipements');
            exit;
        }

        if (!isset($_SESSION['user'])) {
            BaseView::setFlash('Vous devez être connecté', 'error');
            header('Location: ?page=login');
            exit;
        }

        $equipementId = $_POST['id_equipement'] ?? null;
        $dateDebut = $_POST['date_debut'] ?? null;
        $dateFin = $_POST['date_fin'] ?? null;

        if (!$equipementId || !$dateDebut || !$dateFin) {
            BaseView::setFlash('Données incomplètes', 'error');
            header('Location: ?page=equipements');
            exit;
        }

        // Valider les dates
        if ($dateFin <= $dateDebut) {
            BaseView::setFlash('La date de fin doit être après la date de début', 'error');
            header('Location: ?page=equipements&action=reserver&id=' . $equipementId);
            exit;
        }

        // Réserver
        $result = $this->model->reserver(
            $equipementId,
            $_SESSION['user']['id_membre'],
            $dateDebut,
            $dateFin
        );

        if ($result['success']) {
            BaseView::setFlash('Réservation effectuée avec succès !', 'success');
            header('Location: ?page=equipements');
        } else {
            BaseView::setFlash($result['error'], 'error');
            header('Location: ?page=equipements&action=reserver&id=' . $equipementId);
        }
        exit;
    }

    /**
     * Annuler une réservation
     */
    public function annuler()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ?page=login');
            exit;
        }

        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=equipements');
            exit;
        }

        $success = $this->model->annulerReservation($id);

        if ($success) {
            BaseView::setFlash('Réservation annulée avec succès', 'success');
        } else {
            BaseView::setFlash('Erreur lors de l\'annulation', 'error');
        }

        header('Location: ?page=equipements');
        exit;
    }

    /**
     * Filtrer les équipements (AJAX)
     */
    public function filter()
    {
        header('Content-Type: application/json');

        $filters = [
            'type' => $_GET['type'] ?? '',
            'etat' => $_GET['etat'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        $equipements = $this->model->filter($filters);
        $isLoggedIn = isset($_SESSION['user']);

        ob_start();
        $this->view->renderEquipementsList($equipements, $isLoggedIn);
        $html = ob_get_clean();

        echo json_encode([
            'success' => true,
            'html' => $html,
            'count' => count($equipements)
        ]);
    }
}
?>