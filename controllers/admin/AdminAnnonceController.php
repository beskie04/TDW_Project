<?php
require_once __DIR__ . '/../../models/AnnonceModel.php';
require_once __DIR__ . '/../../views/admin/AdminAnnonceView.php';

class AdminAnnonceController
{
    private $model;
    private $view;

    public function __construct()
    {
        $this->model = new AnnonceModel();
        $this->view = new AdminAnnonceView();
    }

    /**
     * List all announcements
     */
    public function index()
    {
        $annonces = $this->model->getAllForAdmin();
        $this->view->renderIndex($annonces);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $this->view->renderCreate();
    }

    /**
     * Store new announcement
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=annonces');
            exit;
        }

        $data = [
            'titre' => $_POST['titre'] ?? '',
            'contenu' => $_POST['contenu'] ?? '',
            'type_annonce' => $_POST['type_annonce'] ?? 'info',
            'date_debut' => $_POST['date_debut'] ?? date('Y-m-d'),
            'date_fin' => $_POST['date_fin'] ?? null,
            'auteur_id' => $_SESSION['user']['id_membre'],
            'priorite' => $_POST['priorite'] ?? 0,
            'est_publie' => isset($_POST['publier']) ? 1 : 0
        ];

        $result = $this->model->insert($data);

        if ($result) {
            $_SESSION['flash_message'] = 'Annonce créée avec succès';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Erreur lors de la création';
            $_SESSION['flash_type'] = 'error';
        }

        header('Location: ?page=admin&section=annonces');
        exit;
    }

    /**
     * Show edit form
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?page=admin&section=annonces');
            exit;
        }

        $annonce = $this->model->getById($id);
        if (!$annonce) {
            header('Location: ?page=admin&section=annonces');
            exit;
        }

        $this->view->renderEdit($annonce);
    }

    /**
     * Update announcement
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=annonces');
            exit;
        }

        $id = $_POST['id_annonce'] ?? null;
        if (!$id) {
            header('Location: ?page=admin&section=annonces');
            exit;
        }

        $data = [
            'titre' => $_POST['titre'] ?? '',
            'contenu' => $_POST['contenu'] ?? '',
            'type_annonce' => $_POST['type_annonce'] ?? 'info',
            'date_debut' => $_POST['date_debut'] ?? date('Y-m-d'),
            'date_fin' => $_POST['date_fin'] ?? null,
            'priorite' => $_POST['priorite'] ?? 0,
            'est_publie' => isset($_POST['publier']) ? 1 : 0
        ];

        $result = $this->model->update($id, $data);

        if ($result) {
            $_SESSION['flash_message'] = 'Annonce modifiée avec succès';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Erreur lors de la modification';
            $_SESSION['flash_type'] = 'error';
        }

        header('Location: ?page=admin&section=annonces');
        exit;
    }

    /**
     * Delete announcement
     */
    public function delete()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            header('Location: ?page=admin&section=annonces');
            exit;
        }

        $result = $this->model->delete($id);

        if ($result) {
            $_SESSION['flash_message'] = 'Annonce supprimée avec succès';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Erreur lors de la suppression';
            $_SESSION['flash_type'] = 'error';
        }

        header('Location: ?page=admin&section=annonces');
        exit;
    }

    /**
     * Toggle publish status
     */
    public function togglePublish()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false]);
            exit;
        }

        $annonce = $this->model->getById($id);
        if (!$annonce) {
            echo json_encode(['success' => false]);
            exit;
        }

        $newStatus = $annonce['est_publie'] ? 0 : 1;
        $result = $this->model->update($id, ['est_publie' => $newStatus]);

        echo json_encode([
            'success' => $result,
            'est_publie' => $newStatus
        ]);
        exit;
    }
}
?>