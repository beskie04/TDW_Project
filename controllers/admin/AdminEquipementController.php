<?php
require_once __DIR__ . '/../../models/EquipementModel.php';
require_once __DIR__ . '/../../views/admin/AdminEquipementView.php';
require_once __DIR__ . '/../../views/BaseView.php';

class AdminEquipementController
{
    private $model;
    private $view;

    public function __construct()
    {
        // Vérifier si admin
        $this->checkAdmin();

        $this->model = new EquipementModel();
        $this->view = new AdminEquipementView();
    }

    /**
     * Vérifier si l'utilisateur est admin
     */
    private function checkAdmin()
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: ?page=login');
            exit;
        }
    }

    /**
     * Liste des équipements
     */
    public function index()
    {
        $equipements = $this->model->getAllWithReservations();
        $stats = $this->model->getStatistics();

        // Récupérer toutes les réservations récentes
        $reservations = $this->getAllReservations();

        $this->view->renderListe($equipements, $stats, $reservations);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $this->view->renderForm(null);
    }

    /**
     * Enregistrer un nouvel équipement
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=equipements');
            exit;
        }

        // Validation
        $errors = $this->validate($_POST);

        if (!empty($errors)) {
            BaseView::setFlash(implode(', ', $errors), 'error');
            header('Location: ?page=admin&section=equipements&action=create');
            exit;
        }

        // Préparer les données
        $data = [
            'nom' => $_POST['nom'],
            'type' => $_POST['type'],
            'etat' => $_POST['etat'],
            'description' => $_POST['description'],
            'specifications' => !empty($_POST['specifications']) ? $_POST['specifications'] : null
        ];

        // Insérer
        $id = $this->model->insert($data);

        if ($id) {
            BaseView::setFlash('Équipement créé avec succès !', 'success');
            header('Location: ?page=admin&section=equipements');
        } else {
            BaseView::setFlash('Erreur lors de la création de l\'équipement', 'error');
            header('Location: ?page=admin&section=equipements&action=create');
        }
        exit;
    }

    /**
     * Formulaire de modification
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=admin&section=equipements');
            exit;
        }

        $equipement = $this->model->getById($id);

        if (!$equipement) {
            BaseView::setFlash('Équipement introuvable', 'error');
            header('Location: ?page=admin&section=equipements');
            exit;
        }

        $this->view->renderForm($equipement);
    }

    /**
     * Mettre à jour un équipement
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=equipements');
            exit;
        }

        $id = $_POST['id'] ?? null;

        if (!$id) {
            header('Location: ?page=admin&section=equipements');
            exit;
        }

        // Validation
        $errors = $this->validate($_POST);

        if (!empty($errors)) {
            BaseView::setFlash(implode(', ', $errors), 'error');
            header('Location: ?page=admin&section=equipements&action=edit&id=' . $id);
            exit;
        }

        // Préparer les données
        $data = [
            'nom' => $_POST['nom'],
            'type' => $_POST['type'],
            'etat' => $_POST['etat'],
            'description' => $_POST['description'],
            'specifications' => !empty($_POST['specifications']) ? $_POST['specifications'] : null
        ];

        // Mettre à jour
        $success = $this->model->update($id, $data);

        if ($success) {
            BaseView::setFlash('Équipement mis à jour avec succès !', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la mise à jour de l\'équipement', 'error');
        }

        header('Location: ?page=admin&section=equipements');
        exit;
    }

    /**
     * Supprimer un équipement
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=admin&section=equipements');
            exit;
        }

        // Vérifier s'il y a des réservations actives
        $reservations = $this->model->getReservations($id);
        if (!empty($reservations)) {
            BaseView::setFlash('Impossible de supprimer : cet équipement a des réservations actives', 'error');
            header('Location: ?page=admin&section=equipements');
            exit;
        }

        $success = $this->model->delete($id);

        if ($success) {
            BaseView::setFlash('Équipement supprimé avec succès !', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la suppression de l\'équipement', 'error');
        }

        header('Location: ?page=admin&section=equipements');
        exit;
    }

    /**
     * Annuler une réservation
     */
    public function annuler_reservation()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=admin&section=equipements');
            exit;
        }

        $success = $this->model->annulerReservation($id);

        if ($success) {
            BaseView::setFlash('Réservation annulée avec succès', 'success');
        } else {
            BaseView::setFlash('Erreur lors de l\'annulation de la réservation', 'error');
        }

        header('Location: ?page=admin&section=equipements');
        exit;
    }

    /**
     * Validation des données
     */
    private function validate($data)
    {
        $errors = [];

        if (empty($data['nom'])) {
            $errors[] = 'Le nom est requis';
        }

        if (empty($data['type'])) {
            $errors[] = 'Le type est requis';
        }

        if (empty($data['etat'])) {
            $errors[] = 'L\'état est requis';
        }

        if (empty($data['description'])) {
            $errors[] = 'La description est requise';
        }

        return $errors;
    }

    /**
     * Récupérer toutes les réservations récentes
     */
    private function getAllReservations()
    {
        $sql = "SELECT r.*, 
                       e.nom as equipement_nom,
                       m.nom as membre_nom,
                       m.prenom as membre_prenom
                FROM reservations r
                INNER JOIN equipements e ON r.id_equipement = e.id
                INNER JOIN membres m ON r.id_membre = m.id_membre
                ORDER BY r.created_at DESC
                LIMIT 20";

        return $this->model->query($sql);
    }
}
?>