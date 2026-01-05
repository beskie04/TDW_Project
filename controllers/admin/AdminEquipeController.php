<?php
require_once __DIR__ . '/../../models/admin/AdminEquipeModel.php';
require_once __DIR__ . '/../../models/MembreModel.php';
require_once __DIR__ . '/../../views/admin/AdminEquipeView.php';

class AdminEquipeController
{
    private $equipeModel;
    private $membreModel;
    private $view;

    public function __construct()
    {
        $this->equipeModel = new AdminEquipeModel();
        $this->membreModel = new MembreModel();
        $this->view = new AdminEquipeView();
    }

    /**
     * Liste des équipes
     */
    public function index()
    {
        $equipes = $this->equipeModel->getAllWithChef();
        $stats = $this->equipeModel->getStatistics();

        $this->view->renderIndex($equipes, $stats);
    }

    /**
     * Détails d'une équipe
     */
    public function details()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=admin&section=equipes');
            exit;
        }

        $equipe = $this->equipeModel->getWithDetails($id);

        if (!$equipe) {
            BaseView::setFlash('Équipe introuvable.', 'error');
            header('Location: ?page=admin&section=equipes');
            exit;
        }

        $membres = $this->equipeModel->getMembres($id);
        $ressources = $this->equipeModel->getRessources($id);
        $publications = $this->equipeModel->getPublications($id);
        $membresDisponibles = $this->equipeModel->getMembresDisponibles($id);

        $this->view->renderDetails($equipe, $membres, $ressources, $publications, $membresDisponibles);
    }

    /**
     * Créer une équipe
     */
    public function create()
    {
        $membres = $this->membreModel->getAllActifs();
        $this->view->renderCreate($membres);
    }

    /**
     * Enregistrer une nouvelle équipe
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=equipes');
            exit;
        }

        $data = [
            'nom' => trim($_POST['nom'] ?? ''),
            'chef_id' => $_POST['chef_id'] ?? null,
            'description' => trim($_POST['description'] ?? '')
        ];

        // Validation
        if (empty($data['nom'])) {
            BaseView::setFlash('Le nom de l\'équipe est requis.', 'error');
            header('Location: ?page=admin&section=equipes&action=create');
            exit;
        }

        $result = $this->equipeModel->create($data);

        if ($result) {
            BaseView::setFlash('Équipe créée avec succès.', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la création de l\'équipe.', 'error');
        }

        header('Location: ?page=admin&section=equipes');
        exit;
    }

    /**
     * Modifier une équipe
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=admin&section=equipes');
            exit;
        }

        $equipe = $this->equipeModel->getById($id);

        if (!$equipe) {
            BaseView::setFlash('Équipe introuvable.', 'error');
            header('Location: ?page=admin&section=equipes');
            exit;
        }

        $membres = $this->membreModel->getAllActifs();
        $this->view->renderEdit($equipe, $membres);
    }

    /**
     * Mettre à jour une équipe
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=equipes');
            exit;
        }

        $id = $_POST['id'] ?? null;

        if (!$id) {
            header('Location: ?page=admin&section=equipes');
            exit;
        }

        $data = [
            'nom' => trim($_POST['nom'] ?? ''),
            'chef_id' => $_POST['chef_id'] ?? null,
            'description' => trim($_POST['description'] ?? '')
        ];

        // Validation
        if (empty($data['nom'])) {
            BaseView::setFlash('Le nom de l\'équipe est requis.', 'error');
            header('Location: ?page=admin&section=equipes&action=edit&id=' . $id);
            exit;
        }

        $result = $this->equipeModel->update($id, $data);

        if ($result) {
            BaseView::setFlash('Équipe modifiée avec succès.', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la modification de l\'équipe.', 'error');
        }

        header('Location: ?page=admin&section=equipes');
        exit;
    }

    /**
     * Supprimer une équipe
     */
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=equipes');
            exit;
        }

        $id = $_POST['id'] ?? null;

        if (!$id) {
            header('Location: ?page=admin&section=equipes');
            exit;
        }

        // Vérifier s'il y a des membres
        $membres = $this->equipeModel->getMembres($id);
        if (!empty($membres)) {
            BaseView::setFlash('Impossible de supprimer une équipe contenant des membres.', 'error');
            header('Location: ?page=admin&section=equipes');
            exit;
        }

        $result = $this->equipeModel->delete($id);

        if ($result) {
            BaseView::setFlash('Équipe supprimée avec succès.', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la suppression de l\'équipe.', 'error');
        }

        header('Location: ?page=admin&section=equipes');
        exit;
    }

    /**
     * Ajouter un membre à l'équipe
     */
    public function addMembre()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=equipes');
            exit;
        }

        $equipeId = $_POST['equipe_id'] ?? null;
        $membreId = $_POST['membre_id'] ?? null;

        if (!$equipeId || !$membreId) {
            BaseView::setFlash('Données invalides.', 'error');
            header('Location: ?page=admin&section=equipes');
            exit;
        }

        $result = $this->equipeModel->addMembre($equipeId, $membreId);

        if ($result) {
            BaseView::setFlash('Membre ajouté à l\'équipe avec succès.', 'success');
        } else {
            BaseView::setFlash('Erreur lors de l\'ajout du membre.', 'error');
        }

        header('Location: ?page=admin&section=equipes&action=details&id=' . $equipeId);
        exit;
    }

    /**
     * Retirer un membre de l'équipe
     */
    public function removeMembre()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=equipes');
            exit;
        }

        $equipeId = $_POST['equipe_id'] ?? null;
        $membreId = $_POST['membre_id'] ?? null;

        if (!$equipeId || !$membreId) {
            BaseView::setFlash('Données invalides.', 'error');
            header('Location: ?page=admin&section=equipes');
            exit;
        }

        $result = $this->equipeModel->removeMembre($equipeId, $membreId);

        if ($result) {
            BaseView::setFlash('Membre retiré de l\'équipe avec succès.', 'success');
        } else {
            BaseView::setFlash('Erreur lors du retrait du membre.', 'error');
        }

        header('Location: ?page=admin&section=equipes&action=details&id=' . $equipeId);
        exit;
    }
}
?>