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
        $this->checkAdmin();
        $this->model = new EquipementModel();
        $this->view = new AdminEquipementView();
    }

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

        $this->view->renderListe($equipements, $stats);
    }

    /**
     * Historique des réservations
     */
    public function historique()
    {
        $reservations = $this->model->getAllReservations();
        $this->view->renderHistorique($reservations);
    }

    /**
     * Gestion des demandes prioritaires
     */
    public function demandes()
    {
        $filters = [
            'statut' => $_GET['statut'] ?? ''
        ];

        $demandes = $this->model->getAllDemandesPrioritaires($filters);
        $this->view->renderDemandes($demandes);
    }

    /**
     * Approuver une demande
     */
    public function approuverDemande()
    {
        $id = $_POST['id'] ?? null;
        $reponse = $_POST['reponse'] ?? '';

        if (!$id) {
            BaseView::setFlash('ID manquant', 'error');
            header('Location: ?page=admin&section=equipements&action=demandes');
            exit;
        }

        $success = $this->model->approuverDemande($id, $reponse);

        if ($success) {
            BaseView::setFlash('Demande approuvée avec succès', 'success');
        } else {
            BaseView::setFlash('Erreur lors de l\'approbation', 'error');
        }

        header('Location: ?page=admin&section=equipements&action=demandes');
        exit;
    }

    /**
     * Rejeter une demande
     */
    public function rejeterDemande()
    {
        $id = $_POST['id'] ?? null;
        $reponse = $_POST['reponse'] ?? '';

        if (!$id) {
            BaseView::setFlash('ID manquant', 'error');
            header('Location: ?page=admin&section=equipements&action=demandes');
            exit;
        }

        $success = $this->model->rejeterDemande($id, $reponse);

        if ($success) {
            BaseView::setFlash('Demande rejetée', 'success');
        } else {
            BaseView::setFlash('Erreur lors du rejet', 'error');
        }

        header('Location: ?page=admin&section=equipements&action=demandes');
        exit;
    }

    /**
     * Générer rapport PDF
     */
    public function genererRapport()
    {
        require_once __DIR__ . '/../../utils/PdfGeneratorEquipement.php';

        $filters = [
            'type' => $_GET['type'] ?? '',
            'etat' => $_GET['etat'] ?? ''
        ];

        $equipements = $this->model->filter($filters);
        $stats = $this->model->getStatistics();

        $pdfGen = new PdfGeneratorEquipement();
        $pdfGen->generateEquipementsReport($equipements, $stats, $filters);
        exit;
    }

    public function create()
    {
        $this->view->renderForm(null);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=equipements');
            exit;
        }

        $errors = $this->validate($_POST);

        if (!empty($errors)) {
            BaseView::setFlash(implode(', ', $errors), 'error');
            header('Location: ?page=admin&section=equipements&action=create');
            exit;
        }

        $data = [
            'nom' => $_POST['nom'],
            'type' => $_POST['type'],
            'etat' => $_POST['etat'],
            'description' => $_POST['description'],
            'specifications' => !empty($_POST['specifications']) ? $_POST['specifications'] : null
        ];

        $id = $this->model->insert($data);

        if ($id) {
            BaseView::setFlash('Équipement créé avec succès !', 'success');
            header('Location: ?page=admin&section=equipements');
        } else {
            BaseView::setFlash('Erreur lors de la création', 'error');
            header('Location: ?page=admin&section=equipements&action=create');
        }
        exit;
    }

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

        $errors = $this->validate($_POST);

        if (!empty($errors)) {
            BaseView::setFlash(implode(', ', $errors), 'error');
            header('Location: ?page=admin&section=equipements&action=edit&id=' . $id);
            exit;
        }

        $data = [
            'nom' => $_POST['nom'],
            'type' => $_POST['type'],
            'etat' => $_POST['etat'],
            'description' => $_POST['description'],
            'specifications' => !empty($_POST['specifications']) ? $_POST['specifications'] : null
        ];

        $success = $this->model->update($id, $data);

        if ($success) {
            BaseView::setFlash('Équipement mis à jour avec succès !', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la mise à jour', 'error');
        }

        header('Location: ?page=admin&section=equipements');
        exit;
    }

    public function delete()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=admin&section=equipements');
            exit;
        }

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
            BaseView::setFlash('Erreur lors de la suppression', 'error');
        }

        header('Location: ?page=admin&section=equipements');
        exit;
    }

    /**
     * Voir détails (admin view only)
     */
    public function details()
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

        $reservations = $this->model->getReservations($id, true); // Include history
        $stats = $this->model->getStatistiquesEquipement($id);

        $this->view->renderDetails($equipement, $reservations, $stats);
    }

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
            BaseView::setFlash('Erreur lors de l\'annulation', 'error');
        }

        header('Location: ?page=admin&section=equipements&action=historique');
        exit;
    }

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
}
?>