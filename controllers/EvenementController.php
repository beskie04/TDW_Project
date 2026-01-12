<?php
require_once __DIR__ . '/../models/EvenementModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../views/EvenementView.php';

class EvenementController
{
    private $model;
    private $notificationModel;
    private $view;

    public function __construct()
    {
        $this->model = new EvenementModel();
        $this->notificationModel = new NotificationModel();
        $this->view = new EvenementView();
    }

    /**
     * Display events list
     */
    public function index()
    {
        // Update event statuses automatically
        $this->model->updateStatuts();

        $evenements = $this->model->getAllWithDetails();
        $types = $this->model->getTypes();

        $this->view->renderListe($evenements, $types);
    }

    /**
     * Display event details
     */
    public function details()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=evenements');
            exit;
        }

        $evenement = $this->model->getByIdWithDetails($id);

        if (!$evenement) {
            header('Location: ?page=evenements');
            exit;
        }

        $participants = $this->model->getParticipants($id);

        // Check if current user is registered
        $isInscrit = false;
        if (isset($_SESSION['user'])) {
            $isInscrit = $this->model->isInscrit($id, $_SESSION['user']['id_membre']);
        }

        $this->view->renderDetails($evenement, $participants, $isInscrit);
    }

    /**
     * Filter events (AJAX)
     */
    public function filter()
    {
        header('Content-Type: application/json');

        $filters = [
            'type' => $_GET['type'] ?? '',
            'statut' => $_GET['statut'] ?? '',
            'search' => $_GET['search'] ?? '',
            'sort' => $_GET['sort'] ?? 'date_debut',
            'order' => $_GET['order'] ?? 'ASC'
        ];

        $evenements = $this->model->filter($filters);

        ob_start();
        $this->view->renderEvenementsCards($evenements);
        $html = ob_get_clean();

        echo json_encode([
            'success' => true,
            'html' => $html,
            'count' => count($evenements)
        ]);
    }

    /**
     * Register for event
     */
    public function inscrire()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=evenements');
            exit;
        }

        $evenementId = $_POST['id_evenement'] ?? null;
        $membreId = $_SESSION['user']['id_membre'] ?? null;

        if (!$evenementId) {
            $_SESSION['flash_message'] = 'Événement invalide';
            $_SESSION['flash_type'] = 'error';
            header('Location: ?page=evenements');
            exit;
        }

        // Get event details
        $evenement = $this->model->getByIdWithDetails($evenementId);

        $data = [
            'email' => $_POST['email'] ?? $_SESSION['user']['email'] ?? '',
            'telephone' => $_POST['telephone'] ?? '',
            'nom' => $_SESSION['user']['nom'] ?? $_POST['nom'] ?? '',
            'prenom' => $_SESSION['user']['prenom'] ?? $_POST['prenom'] ?? ''
        ];

        $result = $this->model->inscrire($evenementId, $membreId, $data);

        if ($result) {
            $_SESSION['flash_message'] = 'Inscription réussie !';
            $_SESSION['flash_type'] = 'success';

            // Send confirmation notification to user
            if ($membreId) {
                $dateDebut = new DateTime($evenement['date_debut']);
                
                $this->notificationModel->create([
                    'id_membre' => $membreId,
                    'type' => 'inscription_confirmee',
                    'titre' => 'Inscription confirmée',
                    'message' => "Votre inscription à l'événement \"{$evenement['titre']}\" est confirmée. L'événement aura lieu le " . $dateDebut->format('d/m/Y à H:i') . ".",
                    'lien' => "?page=evenements&action=details&id={$evenementId}",
                    'id_evenement' => $evenementId
                ]);
            }

            // Notify organizer if exists
            if ($evenement['organisateur_id']) {
                $userName = $data['nom'] . ' ' . $data['prenom'];
                $this->notificationModel->create([
                    'id_membre' => $evenement['organisateur_id'],
                    'type' => 'systeme',
                    'titre' => 'Nouvelle inscription',
                    'message' => "{$userName} s'est inscrit à votre événement \"{$evenement['titre']}\".",
                    'lien' => "?page=admin&section=evenements&action=inscriptions&id={$evenementId}",
                    'id_evenement' => $evenementId
                ]);
            }
        } else {
            $_SESSION['flash_message'] = 'Erreur lors de l\'inscription. Vous êtes peut-être déjà inscrit ou l\'événement est complet.';
            $_SESSION['flash_type'] = 'error';
        }

        header('Location: ?page=evenements&action=details&id=' . $evenementId);
        exit;
    }

    /**
     * Cancel registration
     */
    public function annuler()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ?page=login');
            exit;
        }

        $evenementId = $_POST['id_evenement'] ?? null;
        $membreId = $_SESSION['user']['id_membre'];

        if (!$evenementId) {
            header('Location: ?page=evenements');
            exit;
        }

        // Get event details before cancellation
        $evenement = $this->model->getByIdWithDetails($evenementId);

        $result = $this->model->annulerInscription($evenementId, $membreId);

        if ($result) {
            $_SESSION['flash_message'] = 'Inscription annulée avec succès';
            $_SESSION['flash_type'] = 'success';

            // Notify organizer about cancellation
            if ($evenement['organisateur_id']) {
                $userName = $_SESSION['user']['nom'] . ' ' . $_SESSION['user']['prenom'];
                $this->notificationModel->create([
                    'id_membre' => $evenement['organisateur_id'],
                    'type' => 'systeme',
                    'titre' => 'Annulation d\'inscription',
                    'message' => "{$userName} a annulé son inscription à l'événement \"{$evenement['titre']}\".",
                    'lien' => "?page=admin&section=evenements&action=inscriptions&id={$evenementId}",
                    'id_evenement' => $evenementId
                ]);
            }
        } else {
            $_SESSION['flash_message'] = 'Erreur lors de l\'annulation';
            $_SESSION['flash_type'] = 'error';
        }

        header('Location: ?page=evenements&action=details&id=' . $evenementId);
        exit;
    }
}
?>