<?php
require_once __DIR__ . '/../../models/PublicationModel.php';
require_once __DIR__ . '/../../views/admin/AdminPublicationView.php';
require_once __DIR__ . '/../../views/BaseView.php';

class AdminPublicationController
{
    private $model;
    private $view;

    public function __construct()
    {
        // Vérifier si admin
        $this->checkAdmin();

        $this->model = new PublicationModel();
        $this->view = new AdminPublicationView();
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
     * Liste des publications
     */
    public function index()
    {
        $publications = $this->model->getAllWithDetails();
        $stats = $this->model->getStatistics();

        $this->view->renderListe($publications, $stats);
    }

    /**
     * Liste des publications en attente de validation
     */
    public function pending()
    {
        $publications = $this->model->getPending();
        $this->view->renderPending($publications);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $types = TYPES_PUBLICATIONS;
        $domaines = $this->getDomaines();

        $this->view->renderForm(null, $types, $domaines);
    }

    /**
     * Enregistrer une nouvelle publication
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=publications');
            exit;
        }

        // Validation
        $errors = $this->validate($_POST);

        if (!empty($errors)) {
            BaseView::setFlash(implode(', ', $errors), 'error');
            header('Location: ?page=admin&section=publications&action=create');
            exit;
        }

        // Préparer les données
        $data = [
            'titre' => $_POST['titre'],
            'auteurs' => $_POST['auteurs'],
            'annee' => $_POST['annee'],
            'type' => $_POST['type'],
            'id_thematique' => $_POST['id_thematique'],
            'doi' => !empty($_POST['doi']) ? $_POST['doi'] : null,
            'resume' => !empty($_POST['resume']) ? $_POST['resume'] : null,
            'date_publication' => !empty($_POST['date_publication']) ? $_POST['date_publication'] : null,
            'validee' => 0 // Par défaut, non validée
        ];

        // Gestion de l'upload du fichier
        if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === 0) {
            $uploadResult = $this->handleFileUpload($_FILES['fichier']);
            if ($uploadResult['success']) {
                $data['fichier'] = $uploadResult['filename'];
            } else {
                BaseView::setFlash($uploadResult['error'], 'error');
                header('Location: ?page=admin&section=publications&action=create');
                exit;
            }
        }

        // Insérer
        $id = $this->model->insert($data);

        if ($id) {
            BaseView::setFlash('Publication créée avec succès ! En attente de validation.', 'success');
            header('Location: ?page=admin&section=publications');
        } else {
            BaseView::setFlash('Erreur lors de la création de la publication', 'error');
            header('Location: ?page=admin&section=publications&action=create');
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
            header('Location: ?page=admin&section=publications');
            exit;
        }

        $publication = $this->model->getById($id);

        if (!$publication) {
            BaseView::setFlash('Publication introuvable', 'error');
            header('Location: ?page=admin&section=publications');
            exit;
        }

        $types = TYPES_PUBLICATIONS;
        $domaines = $this->getDomaines();

        $this->view->renderForm($publication, $types, $domaines);
    }

    /**
     * Mettre à jour une publication
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=publications');
            exit;
        }

        $id = $_POST['id'] ?? null;

        if (!$id) {
            header('Location: ?page=admin&section=publications');
            exit;
        }

        // Validation
        $errors = $this->validate($_POST);

        if (!empty($errors)) {
            BaseView::setFlash(implode(', ', $errors), 'error');
            header('Location: ?page=admin&section=publications&action=edit&id=' . $id);
            exit;
        }

        // Préparer les données
        $data = [
            'titre' => $_POST['titre'],
            'auteurs' => $_POST['auteurs'],
            'annee' => $_POST['annee'],
            'type' => $_POST['type'],
            'id_thematique' => $_POST['id_thematique'],
            'doi' => !empty($_POST['doi']) ? $_POST['doi'] : null,
            'resume' => !empty($_POST['resume']) ? $_POST['resume'] : null,
            'date_publication' => !empty($_POST['date_publication']) ? $_POST['date_publication'] : null,
            'validee' => isset($_POST['validee']) ? 1 : 0
        ];

        // Gestion de l'upload du fichier
        if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === 0) {
            $uploadResult = $this->handleFileUpload($_FILES['fichier']);
            if ($uploadResult['success']) {
                // Supprimer l'ancien fichier si existe
                $oldPub = $this->model->getById($id);
                if (!empty($oldPub['fichier'])) {
                    $oldFile = UPLOADS_PATH . 'publications/' . $oldPub['fichier'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
                $data['fichier'] = $uploadResult['filename'];
            } else {
                BaseView::setFlash($uploadResult['error'], 'error');
                header('Location: ?page=admin&section=publications&action=edit&id=' . $id);
                exit;
            }
        }

        // Mettre à jour
        $success = $this->model->update($id, $data);

        if ($success) {
            BaseView::setFlash('Publication mise à jour avec succès !', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la mise à jour de la publication', 'error');
        }

        header('Location: ?page=admin&section=publications');
        exit;
    }

    /**
     * Valider une publication
     */
    public function validatePublication()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=admin&section=publications');
            exit;
        }

        $success = $this->model->validatePublication($id);

        if ($success) {
            BaseView::setFlash('Publication validée avec succès !', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la validation de la publication', 'error');
        }

        header('Location: ?page=admin&section=publications');
        exit;
    }

    /**
     * Supprimer une publication
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=admin&section=publications');
            exit;
        }

        // Supprimer le fichier associé si existe
        $publication = $this->model->getById($id);
        if (!empty($publication['fichier'])) {
            $file = UPLOADS_PATH . 'publications/' . $publication['fichier'];
            if (file_exists($file)) {
                unlink($file);
            }
        }

        $success = $this->model->delete($id);

        if ($success) {
            BaseView::setFlash('Publication supprimée avec succès !', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la suppression de la publication', 'error');
        }

        header('Location: ?page=admin&section=publications');
        exit;
    }

    /**
     * Validation des données
     */
    private function validate($data)
    {
        $errors = [];

        if (empty($data['titre'])) {
            $errors[] = 'Le titre est requis';
        }

        if (empty($data['auteurs'])) {
            $errors[] = 'Les auteurs sont requis';
        }

        if (empty($data['annee']) || !is_numeric($data['annee'])) {
            $errors[] = 'L\'année est requise et doit être un nombre';
        }

        if (empty($data['type'])) {
            $errors[] = 'Le type est requis';
        }

        if (empty($data['id_thematique'])) {
            $errors[] = 'Le domaine est requis';
        }

        return $errors;
    }

    /**
     * Gérer l'upload de fichier
     */
    private function handleFileUpload($file)
    {
        // Vérifier le type
        $allowedTypes = ['application/pdf'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Seuls les fichiers PDF sont autorisés'];
        }

        // Vérifier la taille (10 MB max)
        if ($file['size'] > 10 * 1024 * 1024) {
            return ['success' => false, 'error' => 'Le fichier est trop volumineux (max 10 MB)'];
        }

        // Créer le dossier si n'existe pas
        $uploadDir = UPLOADS_PATH . 'publications/';
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

        return ['success' => false, 'error' => 'Erreur lors de l\'upload du fichier'];
    }

    /**
     * Récupérer les domaines (thématiques)
     */
    private function getDomaines()
    {
        return $this->model->query("SELECT id_thematique, nom_thematique FROM thematiques ORDER BY nom_thematique");
    }
}
?>