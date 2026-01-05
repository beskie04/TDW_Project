<?php
require_once __DIR__ . '/../../models/OffreModel.php';
require_once __DIR__ . '/../../views/admin/AdminOffreView.php';

class AdminOffreController
{
    private $model;
    private $view;

    public function __construct()
    {
        $this->model = new OffreModel();
        $this->view = new AdminOffreView();
    }

    /**
     * Liste des offres
     */
    public function index()
    {
        $filters = [
            'type' => $_GET['type'] ?? '',
            'statut' => $_GET['statut'] ?? ''
        ];

        $offres = $this->model->getAllOffres($filters);
        $this->view->renderListe($offres);
    }

    /**
     * Créer une offre
     */
    public function create()
    {
        $this->view->renderForm();
    }

    /**
     * Enregistrer une offre
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=offres');
            exit;
        }

        $id = $this->model->insert([
            'titre' => $_POST['titre'],
            'description' => $_POST['description'],
            'type' => $_POST['type'],
            'date_limite' => $_POST['date_limite'] ?: null,
            'statut' => 'active'
        ]);

        require_once __DIR__ . '/../../views/BaseView.php';
        if ($id) {
            BaseView::setFlash('Offre créée avec succès', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la création', 'error');
        }

        header('Location: ?page=admin&section=offres');
        exit;
    }

    /**
     * Modifier une offre
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?page=admin&section=offres');
            exit;
        }

        $offre = $this->model->getById($id);
        if (!$offre) {
            header('Location: ?page=admin&section=offres');
            exit;
        }

        $this->view->renderForm($offre);
    }

    /**
     * Mettre à jour une offre
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=offres');
            exit;
        }

        $id = $_POST['id_offre'];
        $success = $this->model->update($id, [
            'titre' => $_POST['titre'],
            'description' => $_POST['description'],
            'type' => $_POST['type'],
            'date_limite' => $_POST['date_limite'] ?: null
        ]);

        require_once __DIR__ . '/../../views/BaseView.php';
        if ($success) {
            BaseView::setFlash('Offre modifiée avec succès', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la modification', 'error');
        }

        header('Location: ?page=admin&section=offres');
        exit;
    }

    /**
     * Basculer le statut
     */
    public function toggleStatus()
    {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->model->toggleStatus($id);
        }

        header('Location: ?page=admin&section=offres');
        exit;
    }

    /**
     * Supprimer une offre
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;

        if ($id) {
            $this->model->delete($id);
        }

        require_once __DIR__ . '/../../views/BaseView.php';
        BaseView::setFlash('Offre supprimée avec succès', 'success');
        header('Location: ?page=admin&section=offres');
        exit;
    }
}
?>