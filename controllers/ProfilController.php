<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../views/ProfilView.php';
require_once __DIR__ . '/LoginController.php';

class ProfilController
{
    private $model;
    private $view;

    public function __construct()
    {
        LoginController::requireLogin();
        $this->model = new UserModel();
        $this->view = new ProfilView();
    }

    /**
     * Afficher le tableau de bord
     */
    public function index()
    {
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user']['id'])) {
            header('Location: ?page=login');
            exit;
        }

        $userId = $_SESSION['user']['id'];

        // Obtenir le profil avec statistiques
        $profile = $this->model->getProfileWithStats($userId);

        // Obtenir les données du dashboard
        $projets = $this->model->getUserProjects($userId);
        $publications = $this->model->getUserPublications($userId);
        $reservations = $this->model->getUserReservations($userId);
        $equipes = $this->model->getUserTeams($userId);

        $this->view->renderDashboard($profile, $projets, $publications, $reservations, $equipes);
    }

    /**
     * Afficher le formulaire d'édition du profil
     */
    public function edit()
    {
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user']['id'])) {
            header('Location: ?page=login');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $profile = $this->model->getById($userId);

        if (!$profile) {
            BaseView::setFlash('Profil introuvable', 'error');
            header('Location: ?page=profil');
            exit;
        }

        $this->view->renderEdit($profile);
    }

    /**
     * Mettre à jour le profil
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=profil&action=edit');
            exit;
        }

        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user']['id'])) {
            header('Location: ?page=login');
            exit;
        }

        $userId = $_SESSION['user']['id'];

        $data = [
            'domaine_recherche' => $_POST['domaine_recherche'] ?? '',
            'biographie' => $_POST['biographie'] ?? '',
            'poste' => $_POST['poste'] ?? '',
            'grade' => $_POST['grade'] ?? ''
        ];

        // Validation
        $errors = $this->validateProfileData($data);

        if (!empty($errors)) {
            $profile = $this->model->getById($userId);
            $this->view->renderEdit($profile, $errors);
            return;
        }

        // Mise à jour
        if ($this->model->updateResearchInfo($userId, $data)) {
            // Mettre à jour la session
            $_SESSION['user']['poste'] = $data['poste'];

            BaseView::setFlash('Profil mis à jour avec succès', 'success');
            header('Location: ?page=profil');
        } else {
            BaseView::setFlash('Erreur lors de la mise à jour du profil', 'error');
            header('Location: ?page=profil&action=edit');
        }
        exit;
    }

    /**
     * Mettre à jour la photo de profil
     */
    public function updatePhoto()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=profil&action=edit');
            exit;
        }

        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user']['id'])) {
            header('Location: ?page=login');
            exit;
        }

        $userId = $_SESSION['user']['id'];

        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            BaseView::setFlash('Aucune photo sélectionnée ou erreur lors du téléchargement', 'error');
            header('Location: ?page=profil&action=edit');
            exit;
        }

        // Validation du fichier
        $file = $_FILES['photo'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            BaseView::setFlash('Type de fichier non autorisé. Utilisez JPG, JPEG ou PNG', 'error');
            header('Location: ?page=profil&action=edit');
            exit;
        }

        if ($file['size'] > $maxSize) {
            BaseView::setFlash('Le fichier est trop volumineux (max 5MB)', 'error');
            header('Location: ?page=profil&action=edit');
            exit;
        }

        // Upload du fichier
        $uploadResult = $this->uploadPhoto($file, $userId);

        if ($uploadResult['success']) {
            // Supprimer l'ancienne photo
            $oldProfile = $this->model->getById($userId);
            if ($oldProfile['photo'] && file_exists(__DIR__ . '/../../assets/uploads/photos/' . $oldProfile['photo'])) {
                unlink(__DIR__ . '/../../assets/uploads/photos/' . $oldProfile['photo']);
            }

            // Mettre à jour la BDD
            if ($this->model->updatePhoto($userId, $uploadResult['filename'])) {
                $_SESSION['user']['photo'] = $uploadResult['filename'];
                BaseView::setFlash('Photo de profil mise à jour avec succès', 'success');
            } else {
                BaseView::setFlash('Erreur lors de la mise à jour de la photo', 'error');
            }
        } else {
            BaseView::setFlash($uploadResult['message'], 'error');
        }

        header('Location: ?page=profil&action=edit');
        exit;
    }

    /**
     * Uploader un document
     */
    public function uploadDocument()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=profil');
            exit;
        }

        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user']['id'])) {
            header('Location: ?page=login');
            exit;
        }

        $userId = $_SESSION['user']['id'];

        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            BaseView::setFlash('Aucun document sélectionné ou erreur lors du téléchargement', 'error');
            header('Location: ?page=profil');
            exit;
        }

        $file = $_FILES['document'];
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxSize = 10 * 1024 * 1024; // 10MB

        if (!in_array($file['type'], $allowedTypes)) {
            BaseView::setFlash('Type de fichier non autorisé. Utilisez PDF ou Word', 'error');
            header('Location: ?page=profil');
            exit;
        }

        if ($file['size'] > $maxSize) {
            BaseView::setFlash('Le fichier est trop volumineux (max 10MB)', 'error');
            header('Location: ?page=profil');
            exit;
        }

        // Upload
        $uploadResult = $this->uploadFile($file);

        if ($uploadResult['success']) {
            $data = [
                'nom_document' => $_POST['nom_document'] ?? $file['name'],
                'type_document' => $_POST['type_document'] ?? 'Autre',
                'chemin_fichier' => $uploadResult['filename'],
                'taille_fichier' => $file['size']
            ];

            if ($this->model->addDocument($userId, $data)) {
                BaseView::setFlash('Document ajouté avec succès', 'success');
            } else {
                BaseView::setFlash('Erreur lors de l\'ajout du document', 'error');
            }
        } else {
            BaseView::setFlash($uploadResult['message'], 'error');
        }

        header('Location: ?page=profil');
        exit;
    }

    /**
     * Download a document
     */
    public function downloadDocument()
    {
        if (!isset($_SESSION['user']['id'])) {
            header('Location: ?page=login');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $documentId = $_GET['doc_id'] ?? null;

        if (!$documentId) {
            BaseView::setFlash('Document introuvable', 'error');
            header('Location: ?page=profil');
            exit;
        }

        // Get document
        $document = $this->model->getDocument($documentId, $userId);

        if (!$document) {
            BaseView::setFlash('Document introuvable ou accès refusé', 'error');
            header('Location: ?page=profil');
            exit;
        }

        $filePath = __DIR__ . '/../../assets/uploads/documents/' . $document['chemin_fichier'];

        if (!file_exists($filePath)) {
            BaseView::setFlash('Fichier introuvable sur le serveur', 'error');
            header('Location: ?page=profil');
            exit;
        }

        // Get file extension to set proper content type
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $contentType = 'application/octet-stream';
        
        switch ($extension) {
            case 'pdf':
                $contentType = 'application/pdf';
                break;
            case 'doc':
                $contentType = 'application/msword';
                break;
            case 'docx':
                $contentType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                break;
        }

        // Force download
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $document['nom_document'] . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');
        
        // Clear output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        readfile($filePath);
        exit;
    }

    /**
     * Supprimer un document
     */
    public function deleteDocument()
    {
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user']['id'])) {
            header('Location: ?page=login');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $documentId = $_GET['doc_id'] ?? null;

        if (!$documentId) {
            BaseView::setFlash('Document introuvable', 'error');
            header('Location: ?page=profil');
            exit;
        }

        // Vérifier que le document appartient à l'utilisateur
        $document = $this->model->getDocument($documentId, $userId);

        if (!$document) {
            BaseView::setFlash('Document introuvable ou accès refusé', 'error');
            header('Location: ?page=profil');
            exit;
        }

        // Supprimer le fichier physique
        $filePath = __DIR__ . '/../../assets/uploads/documents/' . $document['chemin_fichier'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Supprimer de la BDD
        if ($this->model->deleteDocument($documentId, $userId)) {
            BaseView::setFlash('Document supprimé avec succès', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la suppression du document', 'error');
        }

        header('Location: ?page=profil');
        exit;
    }

    /**
     * Changer le mot de passe
     */
    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=profil&action=edit');
            exit;
        }

        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user']['id'])) {
            header('Location: ?page=login');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        $errors = $this->validatePasswordChange($currentPassword, $newPassword, $confirmPassword, $userId);

        if (!empty($errors)) {
            $_SESSION['password_errors'] = $errors;
            header('Location: ?page=profil&action=edit');
            exit;
        }

        // Mise à jour
        if ($this->model->updatePassword($userId, $newPassword)) {
            BaseView::setFlash('Mot de passe modifié avec succès', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la modification du mot de passe', 'error');
        }

        header('Location: ?page=profil&action=edit');
        exit;
    }

    /**
     * Obtenir les documents (AJAX)
     */
    public function getDocuments()
    {
        header('Content-Type: application/json');

        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user']['id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Non authentifié'
            ]);
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $documents = $this->model->getUserDocuments($userId);

        ob_start();
        $this->view->renderDocuments($documents);
        $html = ob_get_clean();

        echo json_encode([
            'success' => true,
            'html' => $html,
            'count' => count($documents)
        ]);
        exit;
    }

    /**
     * Valider les données du profil
     */
    private function validateProfileData($data)
    {
        $errors = [];

        if (empty($data['poste'])) {
            $errors['poste'] = 'Le poste est requis';
        }

        if (empty($data['grade'])) {
            $errors['grade'] = 'Le grade est requis';
        }

        return $errors;
    }

    /**
     * Valider le changement de mot de passe
     */
    private function validatePasswordChange($currentPassword, $newPassword, $confirmPassword, $userId)
    {
        $errors = [];

        if (empty($currentPassword)) {
            $errors['current_password'] = 'Le mot de passe actuel est requis';
        } else {
            // Vérifier le mot de passe actuel
            $user = $this->model->getById($userId);
            if (!password_verify($currentPassword, $user['mot_de_passe'])) {
                $errors['current_password'] = 'Mot de passe actuel incorrect';
            }
        }

        if (empty($newPassword)) {
            $errors['new_password'] = 'Le nouveau mot de passe est requis';
        } elseif (strlen($newPassword) < 6) {
            $errors['new_password'] = 'Le mot de passe doit contenir au moins 6 caractères';
        }

        if ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'Les mots de passe ne correspondent pas';
        }

        return $errors;
    }

    /**
     * Upload une photo
     */
    private function uploadPhoto($file, $userId)
    {
        $uploadDir = __DIR__ . '/../../assets/uploads/photos/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . $userId . '_' . time() . '.' . $extension;
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => true, 'filename' => $filename];
        }

        return ['success' => false, 'message' => 'Erreur lors de l\'upload du fichier'];
    }

    /**
     * Upload un document
     */
    private function uploadFile($file)
    {
        $uploadDir = __DIR__ . '/../../assets/uploads/documents/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'doc_' . time() . '_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => true, 'filename' => $filename];
        }

        return ['success' => false, 'message' => 'Erreur lors de l\'upload du fichier'];
    }
}
?>