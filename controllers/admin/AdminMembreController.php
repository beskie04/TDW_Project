<?php
require_once __DIR__ . '/../../models/MembreModel.php';
require_once __DIR__ . '/../../views/admin/AdminMembreView.php';
require_once __DIR__ . '/../../views/BaseView.php';

class AdminMembreController
{
    private $model;
    private $view;

    public function __construct()
    {
        // Vérifier si admin
        $this->checkAdmin();

        $this->model = new MembreModel();
        $this->view = new AdminMembreView();
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
     * Liste des membres
     */
    public function index()
    {
        $membres = $this->model->getAll('nom', 'ASC');
        $stats = $this->model->getStatistics();

        $this->view->renderListe($membres, $stats);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $this->view->renderForm(null);
    }

    /**
     * Enregistrer un nouveau membre
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=membres');
            exit;
        }

        // Validation
        $errors = $this->validate($_POST);

        if (!empty($errors)) {
            BaseView::setFlash(implode(', ', $errors), 'error');
            header('Location: ?page=admin&section=membres&action=create');
            exit;
        }

        // Préparer les données
        $data = [
            'nom' => $_POST['nom'],
            'prenom' => $_POST['prenom'],
            'email' => $_POST['email'],
            'poste' => !empty($_POST['poste']) ? $_POST['poste'] : null,
            'grade' => !empty($_POST['grade']) ? $_POST['grade'] : null,
            'role' => $_POST['role'],
            'actif' => $_POST['actif'],
            'mot_de_passe' => password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT),
            'biographie' => !empty($_POST['biographie']) ? $_POST['biographie'] : null,
            'domaine_recherche' => !empty($_POST['domaine_recherche']) ? $_POST['domaine_recherche'] : null
        ];

        // Gestion de l'upload de la photo
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $uploadResult = $this->handlePhotoUpload($_FILES['photo']);
            if ($uploadResult['success']) {
                $data['photo'] = $uploadResult['filename'];
            } else {
                BaseView::setFlash($uploadResult['error'], 'error');
                header('Location: ?page=admin&section=membres&action=create');
                exit;
            }
        }

        // Insérer
        $id = $this->model->insert($data);

        if ($id) {
            BaseView::setFlash('Membre créé avec succès !', 'success');
            header('Location: ?page=admin&section=membres');
        } else {
            BaseView::setFlash('Erreur lors de la création du membre', 'error');
            header('Location: ?page=admin&section=membres&action=create');
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
            header('Location: ?page=admin&section=membres');
            exit;
        }

        $membre = $this->model->getById($id);

        if (!$membre) {
            BaseView::setFlash('Membre introuvable', 'error');
            header('Location: ?page=admin&section=membres');
            exit;
        }

        $this->view->renderForm($membre);
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

        $id = $_POST['id'] ?? null;

        if (!$id) {
            header('Location: ?page=admin&section=membres');
            exit;
        }

        // Validation
        $errors = $this->validate($_POST, true);

        if (!empty($errors)) {
            BaseView::setFlash(implode(', ', $errors), 'error');
            header('Location: ?page=admin&section=membres&action=edit&id=' . $id);
            exit;
        }

        // Préparer les données
        $data = [
            'nom' => $_POST['nom'],
            'prenom' => $_POST['prenom'],
            'email' => $_POST['email'],
            'poste' => !empty($_POST['poste']) ? $_POST['poste'] : null,
            'grade' => !empty($_POST['grade']) ? $_POST['grade'] : null,
            'role' => $_POST['role'],
            'actif' => $_POST['actif'],
            'biographie' => !empty($_POST['biographie']) ? $_POST['biographie'] : null,
            'domaine_recherche' => !empty($_POST['domaine_recherche']) ? $_POST['domaine_recherche'] : null
        ];

        // Mettre à jour le mot de passe si fourni
        if (!empty($_POST['mot_de_passe'])) {
            $data['mot_de_passe'] = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
        }

        // Gestion de l'upload de la photo
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $uploadResult = $this->handlePhotoUpload($_FILES['photo']);
            if ($uploadResult['success']) {
                // Supprimer l'ancienne photo si existe
                $oldMembre = $this->model->getById($id);
                if (!empty($oldMembre['photo'])) {
                    $oldFile = UPLOADS_PATH . 'photos/' . $oldMembre['photo'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
                $data['photo'] = $uploadResult['filename'];
            } else {
                BaseView::setFlash($uploadResult['error'], 'error');
                header('Location: ?page=admin&section=membres&action=edit&id=' . $id);
                exit;
            }
        }

        // Mettre à jour
        $success = $this->model->update($id, $data);

        if ($success) {
            BaseView::setFlash('Membre mis à jour avec succès !', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la mise à jour du membre', 'error');
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
            header('Location: ?page=admin&section=membres');
            exit;
        }

        // Empêcher la suppression de son propre compte
        if ($id == $_SESSION['user']['id_membre']) {
            BaseView::setFlash('Vous ne pouvez pas supprimer votre propre compte', 'error');
            header('Location: ?page=admin&section=membres');
            exit;
        }

        // Supprimer la photo associée si existe
        $membre = $this->model->getById($id);
        if (!empty($membre['photo'])) {
            $file = UPLOADS_PATH . 'photos/' . $membre['photo'];
            if (file_exists($file)) {
                unlink($file);
            }
        }

        $success = $this->model->delete($id);

        if ($success) {
            BaseView::setFlash('Membre supprimé avec succès !', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la suppression du membre', 'error');
        }

        header('Location: ?page=admin&section=membres');
        exit;
    }

    /**
     * Validation des données
     */
    private function validate($data, $isEdit = false)
    {
        $errors = [];

        if (empty($data['nom'])) {
            $errors[] = 'Le nom est requis';
        }

        if (empty($data['prenom'])) {
            $errors[] = 'Le prénom est requis';
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email invalide';
        }

        if (empty($data['role'])) {
            $errors[] = 'Le rôle est requis';
        }

        if (!$isEdit && empty($data['mot_de_passe'])) {
            $errors[] = 'Le mot de passe est requis';
        }

        return $errors;
    }

    /**
     * Gérer l'upload de photo
     */
    private function handlePhotoUpload($file)
    {
        // Vérifier le type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Seules les images JPG et PNG sont autorisées'];
        }

        // Vérifier la taille (2 MB max)
        if ($file['size'] > 2 * 1024 * 1024) {
            return ['success' => false, 'error' => 'Le fichier est trop volumineux (max 2 MB)'];
        }

        // Créer le dossier si n'existe pas
        $uploadDir = UPLOADS_PATH . 'photos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Générer un nom unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $destination = $uploadDir . $filename;

        // Déplacer le fichier
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => true, 'filename' => $filename];
        }

        return ['success' => false, 'error' => 'Erreur lors de l\'upload de la photo'];
    }
}
?>