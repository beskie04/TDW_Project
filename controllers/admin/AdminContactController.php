<?php
require_once __DIR__ . '/../../models/ContactModel.php';
require_once __DIR__ . '/../../views/admin/AdminContactView.php';

class AdminContactController
{
    private $model;
    private $view;

    public function __construct()
    {
        $this->model = new ContactModel();
        $this->view = new AdminContactView();
    }

    /**
     * Liste des messages
     */
    public function index()
    {
        $filters = [
            'statut' => $_GET['statut'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        $messages = $this->model->getAllWithFilters($filters);
        $stats = $this->model->getStatistics();

        $this->view->renderListe($messages, $stats);
    }

    /**
     * Voir les détails d'un message
     */
    public function details()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=admin&section=messages');
            exit;
        }

        $message = $this->model->getById($id);

        if (!$message) {
            require_once __DIR__ . '/../../views/BaseView.php';
            BaseView::setFlash('Message introuvable', 'error');
            header('Location: ?page=admin&section=messages');
            exit;
        }

        // Marquer comme lu automatiquement
        if ($message['statut'] === 'nouveau') {
            $this->model->markAsRead($id);
            $message['statut'] = 'lu';
        }

        $this->view->renderDetails($message);
    }

    /**
     * Marquer comme lu
     */
    public function markAsRead()
    {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            exit;
        }

        $success = $this->model->markAsRead($id);

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Message marqué comme lu' : 'Erreur'
        ]);
    }

    /**
     * Archiver un message
     */
    public function archive()
    {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            exit;
        }

        $success = $this->model->archive($id);

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Message archivé' : 'Erreur'
        ]);
    }

    /**
     * Supprimer un message
     */
    public function delete()
    {
        $id = $_POST['id'] ?? $_GET['id'] ?? null;

        if (!$id) {
            require_once __DIR__ . '/../../views/BaseView.php';
            BaseView::setFlash('ID manquant', 'error');
            header('Location: ?page=admin&section=messages');
            exit;
        }

        $success = $this->model->delete($id);

        require_once __DIR__ . '/../../views/BaseView.php';
        if ($success) {
            BaseView::setFlash('Message supprimé avec succès', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la suppression', 'error');
        }

        header('Location: ?page=admin&section=messages');
        exit;
    }
}
?>