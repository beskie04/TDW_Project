<?php
require_once __DIR__ . '/../../models/EvenementModel.php';
require_once __DIR__ . '/../../models/NotificationModel.php';
require_once __DIR__ . '/../../views/admin/AdminEvenementView.php';

class AdminEvenementController
{
    private $model;
    private $notificationModel;
    private $view;

    public function __construct()
    {
        $this->model = new EvenementModel();
        $this->notificationModel = new NotificationModel();
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

            // Send notification to organizer if specified
            if ($data['organisateur_id'] && $data['organisateur_id'] != $_SESSION['user']['id_membre']) {
                $this->notificationModel->create([
                    'id_membre' => $data['organisateur_id'],
                    'type' => 'systeme',
                    'titre' => 'Vous êtes organisateur d\'un événement',
                    'message' => "Vous avez été désigné comme organisateur de l'événement \"{$data['titre']}\".",
                    'lien' => "?page=evenements&action=details&id={$result}",
                    'id_evenement' => $result
                ]);
            }
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

        // Get old event data
        $oldEvent = $this->model->getById($id);
        
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

            // Check if important details changed and notify participants
            $hasImportantChanges = false;
            $changeMessage = "L'événement \"{$data['titre']}\" a été modifié. ";
            $changes = [];

            if ($oldEvent['date_debut'] != $data['date_debut']) {
                $hasImportantChanges = true;
                $changes[] = "Nouvelle date: " . date('d/m/Y à H:i', strtotime($data['date_debut']));
            }

            if ($oldEvent['lieu'] != $data['lieu']) {
                $hasImportantChanges = true;
                $changes[] = "Nouveau lieu: {$data['lieu']}";
            }

            if ($data['statut'] == 'annulé' && $oldEvent['statut'] != 'annulé') {
                $hasImportantChanges = true;
                $this->notificationModel->notifyEventParticipants(
                    $id,
                    "Événement annulé : {$data['titre']}",
                    "L'événement \"{$data['titre']}\" a été annulé. Nous nous excusons pour la gêne occasionnée.",
                    'evenement_annulation'
                );
            } elseif ($hasImportantChanges) {
                $changeMessage .= implode('. ', $changes);
                $this->notificationModel->notifyEventParticipants(
                    $id,
                    "Modification : {$data['titre']}",
                    $changeMessage,
                    'evenement_modification'
                );
            }
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

        // Get event data for notification
        $evenement = $this->model->getById($id);

        // Notify participants before deletion
        if ($evenement) {
            $this->notificationModel->notifyEventParticipants(
                $id,
                "Événement supprimé : {$evenement['titre']}",
                "L'événement \"{$evenement['titre']}\" a été supprimé.",
                'evenement_annulation'
            );

            // Delete image file
            if ($evenement['image']) {
                $imagePath = __DIR__ . '/../../uploads/evenements/' . $evenement['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
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