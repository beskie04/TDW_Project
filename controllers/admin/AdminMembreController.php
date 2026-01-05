<?php
require_once __DIR__ . '/../../models/admin/AdminMembreModel.php';
require_once __DIR__ . '/../../views/admin/AdminMembreView.php';
require_once __DIR__ . '/../LoginController.php';

class AdminMembreController
{
    private $model;
    private $view;

    public function __construct()
    {
        LoginController::requireAdmin();
        $this->model = new AdminMembreModel();
        $this->view = new AdminMembreView();
    }

    /**
     * Afficher la liste des membres
     */
    public function index()
    {
        // Get filters from query params
        $filters = [
            'role' => $_GET['role'] ?? '',
            'actif' => $_GET['actif'] ?? '',
            'grade' => $_GET['grade'] ?? '',
            'specialite' => $_GET['specialite'] ?? '',
            'search' => $_GET['search'] ?? '',
            'min_publications' => $_GET['min_publications'] ?? '',
            'min_projets' => $_GET['min_projets'] ?? '',
            'sort' => $_GET['sort'] ?? 'date_creation',
            'order' => $_GET['order'] ?? 'DESC'
        ];

        // Get data
        $membres = $this->model->getAllWithStats($filters);
        $stats = $this->model->getStatistics();
        $roles = $this->model->getRoles();
        $grades = $this->model->getGrades();
        $specialites = $this->model->getSpecialites();

        $this->view->render($membres, $stats, $roles, $grades, $specialites, $filters);
    }

    /**
     * Créer un nouveau membre (via modal)
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=membres');
            exit;
        }

        // Validation
        $errors = $this->validateMembre($_POST);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_data'] = $_POST;
            header('Location: ?page=admin&section=membres');
            exit;
        }

        // Check if email exists
        if ($this->model->emailExists($_POST['email'])) {
            BaseView::setFlash('Cet email existe déjà', 'error');
            $_SESSION['old_data'] = $_POST;
            header('Location: ?page=admin&section=membres');
            exit;
        }

        // Prepare data
        $data = [
            'nom' => $_POST['nom'],
            'prenom' => $_POST['prenom'],
            'email' => $_POST['email'],
            'mot_de_passe' => $_POST['mot_de_passe'],
            'poste' => $_POST['poste'],
            'grade' => $_POST['grade'],
            'role' => $_POST['role'],
            'role_systeme' => $_POST['role_systeme'] ?? 'user',
            'actif' => isset($_POST['actif']) ? 1 : 0,
            'specialite' => $_POST['specialite'] ?? null,
            'domaine_recherche' => $_POST['domaine_recherche'] ?? null,
            'biographie' => $_POST['biographie'] ?? null
        ];

        // Create member
        if ($this->model->createMembre($data)) {
            BaseView::setFlash('Membre ajouté avec succès', 'success');
        } else {
            BaseView::setFlash('Erreur lors de l\'ajout du membre', 'error');
        }

        header('Location: ?page=admin&section=membres');
        exit;
    }

    /**
     * Mettre à jour un membre
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=membres');
            exit;
        }

        $id = $_POST['id_membre'] ?? null;

        if (!$id) {
            BaseView::setFlash('ID membre manquant', 'error');
            header('Location: ?page=admin&section=membres');
            exit;
        }

        // Validation
        $errors = $this->validateMembre($_POST, $id);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_data'] = $_POST;
            header('Location: ?page=admin&section=membres');
            exit;
        }

        // Check email uniqueness (excluding current member)
        if ($this->model->emailExists($_POST['email'], $id)) {
            BaseView::setFlash('Cet email est déjà utilisé par un autre membre', 'error');
            $_SESSION['old_data'] = $_POST;
            header('Location: ?page=admin&section=membres');
            exit;
        }

        // Prepare data
        $data = [
            'nom' => $_POST['nom'],
            'prenom' => $_POST['prenom'],
            'email' => $_POST['email'],
            'poste' => $_POST['poste'],
            'grade' => $_POST['grade'],
            'role' => $_POST['role'],
            'role_systeme' => $_POST['role_systeme'] ?? 'user',
            'actif' => isset($_POST['actif']) ? 1 : 0,
            'specialite' => $_POST['specialite'] ?? null,
            'domaine_recherche' => $_POST['domaine_recherche'] ?? null,
            'biographie' => $_POST['biographie'] ?? null
        ];

        // Add password only if provided
        if (!empty($_POST['mot_de_passe'])) {
            $data['mot_de_passe'] = $_POST['mot_de_passe'];
        }

        // Update member
        if ($this->model->updateMembre($id, $data)) {
            BaseView::setFlash('Membre mis à jour avec succès', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la mise à jour', 'error');
        }

        header('Location: ?page=admin&section=membres');
        exit;
    }

    /**
     * Suspendre/Activer un membre
     */
    public function toggleStatus()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            BaseView::setFlash('ID membre manquant', 'error');
            header('Location: ?page=admin&section=membres');
            exit;
        }

        // Don't allow disabling yourself
        if ($id == $_SESSION['user']['id']) {
            BaseView::setFlash('Vous ne pouvez pas désactiver votre propre compte', 'error');
            header('Location: ?page=admin&section=membres');
            exit;
        }

        if ($this->model->toggleStatus($id)) {
            BaseView::setFlash('Statut du membre modifié avec succès', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la modification du statut', 'error');
        }

        header('Location: ?page=admin&section=membres');
        exit;
    }

    /**
     * Supprimer un membre
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            BaseView::setFlash('ID membre manquant', 'error');
            header('Location: ?page=admin&section=membres');
            exit;
        }

        // Don't allow deleting yourself
        if ($id == $_SESSION['user']['id']) {
            BaseView::setFlash('Vous ne pouvez pas supprimer votre propre compte', 'error');
            header('Location: ?page=admin&section=membres');
            exit;
        }

        if ($this->model->deleteMembre($id)) {
            BaseView::setFlash('Membre supprimé/désactivé avec succès', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la suppression', 'error');
        }

        header('Location: ?page=admin&section=membres');
        exit;
    }

    /**
     * Valider les données du membre
     */
    private function validateMembre($data, $excludeId = null)
    {
        $errors = [];

        if (empty($data['nom'])) {
            $errors['nom'] = 'Le nom est requis';
        }

        if (empty($data['prenom'])) {
            $errors['prenom'] = 'Le prénom est requis';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'L\'email est requis';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format d\'email invalide';
        }

        // Password required only for new members
        if (!$excludeId && empty($data['mot_de_passe'])) {
            $errors['mot_de_passe'] = 'Le mot de passe est requis';
        } elseif (!empty($data['mot_de_passe']) && strlen($data['mot_de_passe']) < 6) {
            $errors['mot_de_passe'] = 'Le mot de passe doit contenir au moins 6 caractères';
        }

        if (empty($data['poste'])) {
            $errors['poste'] = 'Le poste est requis';
        }

        if (empty($data['grade'])) {
            $errors['grade'] = 'Le grade est requis';
        }

        if (empty($data['role'])) {
            $errors['role'] = 'Le rôle est requis';
        }

        return $errors;
    }
}
?>