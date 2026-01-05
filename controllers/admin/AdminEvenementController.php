<?php
require_once __DIR__ . '/../../models/EvenementModel.php';
require_once __DIR__ . '/../../views/admin/AdminEvenementView.php';

class AdminEvenementController
{
    private $model;
    private $view;

    public function __construct()
    {
        $this->model = new EvenementModel();
        $this->view = new AdminEvenementView();
    }

    /**
     * List all events (including unpublished for admin)
     */
    public function index()
    {
        $evenements = $this->model->getAllForAdmin();
        $this->view->renderIndex($evenements);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $types = $this->model->getTypes();

        // Get all members for organizer selection
        require_once __DIR__ . '/../../models/MembreModel.php';
        $membreModel = new MembreModel();
        $membres = $membreModel->getAll('nom', 'ASC');

        $this->view->renderCreate($types, $membres);
    }

    /**
     * Store new event
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=evenements');
            exit;
        }

        $data = [
            'titre' => $_POST['titre'] ?? '',
            'description' => $_POST['description'] ?? '',
            'id_type_evenement' => $_POST['id_type_evenement'] ?? null,
            'lieu' => $_POST['lieu'] ?? '',
            'adresse' => $_POST['adresse'] ?? '',
            'date_debut' => $_POST['date_debut'] ?? null,
            'date_fin' => $_POST['date_fin'] ?? null,
            'organisateur_id' => $_POST['organisateur_id'] ?? $_SESSION['user']['id_membre'],
            'lien_inscription' => $_POST['lien_inscription'] ?? '',
            'capacite_max' => $_POST['capacite_max'] ?? null,
            'statut' => $_POST['statut'] ?? 'à venir',
            'est_publie' => isset($_POST['publier']) ? 1 : 0
        ];

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/evenements/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('event_') . '.' . $extension;
            $uploadPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $data['image'] = $filename;
            }
        }

        $result = $this->model->insert($data);

        if ($result) {
            $_SESSION['flash_message'] = 'Événement créé avec succès';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Erreur lors de la création';
            $_SESSION['flash_type'] = 'error';
        }

        header('Location: ?page=admin&section=evenements');
        exit;
    }

    /**
     * Show edit form
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?page=admin&section=evenements');
            exit;
        }

        $evenement = $this->model->getById($id);
        if (!$evenement) {
            header('Location: ?page=admin&section=evenements');
            exit;
        }

        $types = $this->model->getTypes();

        require_once __DIR__ . '/../../models/MembreModel.php';
        $membreModel = new MembreModel();
        $membres = $membreModel->getAll('nom', 'ASC');

        $this->view->renderEdit($evenement, $types, $membres);
    }

    /**
     * Update event
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=evenements');
            exit;
        }

        $id = $_POST['id_evenement'] ?? null;
        if (!$id) {
            header('Location: ?page=admin&section=evenements');
            exit;
        }

        $data = [
            'titre' => $_POST['titre'] ?? '',
            'description' => $_POST['description'] ?? '',
            'id_type_evenement' => $_POST['id_type_evenement'] ?? null,
            'lieu' => $_POST['lieu'] ?? '',
            'adresse' => $_POST['adresse'] ?? '',
            'date_debut' => $_POST['date_debut'] ?? null,
            'date_fin' => $_POST['date_fin'] ?? null,
            'organisateur_id' => $_POST['organisateur_id'] ?? null,
            'lien_inscription' => $_POST['lien_inscription'] ?? '',
            'capacite_max' => $_POST['capacite_max'] ?? null,
            'statut' => $_POST['statut'] ?? 'à venir',
            'est_publie' => isset($_POST['publier']) ? 1 : 0
        ];

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/evenements/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('event_') . '.' . $extension;
            $uploadPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                // Delete old image
                $oldEvent = $this->model->getById($id);
                if ($oldEvent && $oldEvent['image']) {
                    $oldPath = $uploadDir . $oldEvent['image'];
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $data['image'] = $filename;
            }
        }

        $result = $this->model->update($id, $data);

        if ($result) {
            $_SESSION['flash_message'] = 'Événement modifié avec succès';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Erreur lors de la modification';
            $_SESSION['flash_type'] = 'error';
        }

        header('Location: ?page=admin&section=evenements');
        exit;
    }

    /**
     * Delete event
     */
    public function delete()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            header('Location: ?page=admin&section=evenements');
            exit;
        }

        // Delete image file
        $evenement = $this->model->getById($id);
        if ($evenement && $evenement['image']) {
            $imagePath = __DIR__ . '/../../uploads/evenements/' . $evenement['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $result = $this->model->delete($id);

        if ($result) {
            $_SESSION['flash_message'] = 'Événement supprimé avec succès';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Erreur lors de la suppression';
            $_SESSION['flash_type'] = 'error';
        }

        header('Location: ?page=admin&section=evenements');
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

        $evenement = $this->model->getById($id);
        if (!$evenement) {
            echo json_encode(['success' => false]);
            exit;
        }

        $newStatus = $evenement['est_publie'] ? 0 : 1;
        $result = $this->model->update($id, ['est_publie' => $newStatus]);

        echo json_encode([
            'success' => $result,
            'est_publie' => $newStatus
        ]);
        exit;
    }

    /**
     * View event registrations
     */
    public function inscriptions()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?page=admin&section=evenements');
            exit;
        }

        $evenement = $this->model->getByIdWithDetails($id);
        if (!$evenement) {
            header('Location: ?page=admin&section=evenements');
            exit;
        }

        $participants = $this->model->getParticipants($id);
        $this->view->renderInscriptions($evenement, $participants);
    }

    /**
     * Update participant status
     */
    public function updateParticipantStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false]);
            exit;
        }

        $idParticipation = $_POST['id_participation'] ?? null;
        $newStatus = $_POST['statut'] ?? null;

        if (!$idParticipation || !$newStatus) {
            echo json_encode(['success' => false]);
            exit;
        }

        $sql = "UPDATE evenement_participants SET statut_participation = :statut WHERE id_participation = :id";
        $result = $this->model->execute($sql, [
            'statut' => $newStatus,
            'id' => $idParticipation
        ]);

        echo json_encode(['success' => $result]);
        exit;
    }

    /**
     * Delete participant
     */
    public function deleteParticipant()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false]);
            exit;
        }

        $sql = "DELETE FROM evenement_participants WHERE id_participation = :id";
        $result = $this->model->execute($sql, ['id' => $id]);

        echo json_encode(['success' => $result]);
        exit;
    }
}
?>