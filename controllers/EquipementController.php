<?php
require_once __DIR__ . '/../models/EquipementModel.php';
require_once __DIR__ . '/../views/EquipementView.php';

class EquipementController
{
    private $model;
    private $view;

    public function __construct()
    {
        $this->model = new EquipementModel();
        $this->view = new EquipementView();
    }

    /**
     * Liste des équipements
     */
    public function index()
    {
        $equipements = $this->model->getAllWithReservations();
        $types = TYPES_EQUIPEMENTS;
        $etats = ETATS_EQUIPEMENTS;

        // Si connecté, récupérer ses réservations
        $mesReservations = [];
        $mesEquipementsReserves = []; 
        if (isset($_SESSION['user']) && isset($_SESSION['user']['id_membre'])) {
            $mesReservations = $this->model->getReservationsByMembre($_SESSION['user']['id_membre']);

            //  Extraire les IDs des équipements déjà réservés
            foreach ($mesReservations as $res) {
                $mesEquipementsReserves[] = $res['id_equipement'];
            }
        }

        // Récupérer les statistiques
        $stats = $this->model->getStatistics();

        $this->view->renderListe($equipements, $types, $etats, $mesReservations, $stats, $mesEquipementsReserves);
    }
    /**
     * Historique des réservations
     */
    public function historique()
    {

        // Vérifier connexion
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id_membre'])) {
            BaseView::setFlash('Vous devez être connecté', 'error');
            header('Location: ?page=login');
            exit;
        }

        $reservations = $this->model->getHistoriqueReservations($_SESSION['user']['id_membre']);
        $demandes = $this->model->getDemandesPrioritaires($_SESSION['user']['id_membre']);

        $this->view->renderHistorique($reservations, $demandes);
    }

    /**
     * Détails d'un équipement
     */
    public function details()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=equipements');
            exit;
        }

        $equipement = $this->model->getById($id);

        if (!$equipement) {
            header('Location: ?page=equipements');
            exit;
        }

        $reservations = $this->model->getReservations($id);
        $statsEquipement = $this->model->getStatistiquesEquipement($id);

        //  Vérifier si l'utilisateur a déjà réservé
        $userHasReservation = false;
        if (isset($_SESSION['user']['id_membre'])) {
            foreach ($reservations as $res) {
                if ($res['id_membre'] == $_SESSION['user']['id_membre']) {
                    $userHasReservation = true;
                    break;
                }
            }
        }

        $this->view->renderDetails($equipement, $reservations, $statsEquipement, $userHasReservation);
    }


    /**
     * Formulaire de réservation
     */
    public function reserver()
    {
        // Vérifier connexion
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id_membre'])) {
            BaseView::setFlash('Vous devez être connecté pour réserver un équipement', 'error');
            header('Location: ?page=login');
            exit;
        }

        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=equipements');
            exit;
        }

        $equipement = $this->model->getById($id);

        if (!$equipement) {
            BaseView::setFlash('Équipement introuvable', 'error');
            header('Location: ?page=equipements');
            exit;
        }

        $this->view->renderReservation($equipement);
    }

    /**
     * Confirmer la réservation
     */
    public function confirmer_reservation()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=equipements');
            exit;
        }

        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id_membre'])) {
            BaseView::setFlash('Vous devez être connecté', 'error');
            header('Location: ?page=login');
            exit;
        }

        $equipementId = $_POST['id_equipement'] ?? null;
        $dateDebut = $_POST['date_debut'] ?? null;
        $dateFin = $_POST['date_fin'] ?? null;
        $demandePrioritaire = isset($_POST['demande_prioritaire']);
        $justification = $_POST['justification'] ?? '';

        if (!$equipementId || !$dateDebut || !$dateFin) {
            BaseView::setFlash('Données incomplètes', 'error');
            header('Location: ?page=equipements');
            exit;
        }

        // Valider les dates
        if ($dateFin <= $dateDebut) {
            BaseView::setFlash('La date de fin doit être après la date de début', 'error');
            header('Location: ?page=equipements&action=reserver&id=' . $equipementId);
            exit;
        }

        // Vérifier que la date de début est dans le futur
        if ($dateDebut < date('Y-m-d H:i:s')) {
            BaseView::setFlash('La date de début doit être dans le futur', 'error');
            header('Location: ?page=equipements&action=reserver&id=' . $equipementId);
            exit;
        }

        // Si demande prioritaire
        if ($demandePrioritaire) {
            if (empty($justification)) {
                BaseView::setFlash('La justification est requise pour une demande prioritaire', 'error');
                header('Location: ?page=equipements&action=reserver&id=' . $equipementId);
                exit;
            }

            $result = $this->model->creerDemandePrioritaire(
                $equipementId,
                $_SESSION['user']['id_membre'],
                $dateDebut,
                $dateFin,
                $justification
            );

            if ($result['success']) {
                BaseView::setFlash('Demande prioritaire envoyée avec succès ! Elle sera traitée par un administrateur.', 'success');
            } else {
                BaseView::setFlash('Erreur lors de l\'envoi de la demande', 'error');
            }

            header('Location: ?page=equipements');
            exit;
        }

        // Réservation normale
        $result = $this->model->reserver(
            $equipementId,
            $_SESSION['user']['id_membre'],
            $dateDebut,
            $dateFin
        );

        if ($result['success']) {
            BaseView::setFlash('Réservation effectuée avec succès !', 'success');
            header('Location: ?page=equipements');
        } else {
            if (isset($result['conflit']) && $result['conflit']) {
                // Rediriger avec option de demande prioritaire
                $_SESSION['conflit_reservation'] = [
                    'equipement_id' => $equipementId,
                    'date_debut' => $dateDebut,
                    'date_fin' => $dateFin,
                    'message' => $result['error']
                ];
                header('Location: ?page=equipements&action=reserver&id=' . $equipementId . '&conflit=1');
            } else {
                BaseView::setFlash($result['error'], 'error');
                header('Location: ?page=equipements&action=reserver&id=' . $equipementId);
            }
        }
        exit;
    }

    /**
     * Annuler une réservation
     */
    public function annuler()
    {
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id_membre'])) {
            header('Location: ?page=login');
            exit;
        }

        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=equipements');
            exit;
        }

        $success = $this->model->annulerReservation($id, $_SESSION['user']['id_membre']);

        if ($success) {
            BaseView::setFlash('Réservation annulée avec succès', 'success');
        } else {
            BaseView::setFlash('Erreur lors de l\'annulation', 'error');
        }

        header('Location: ?page=equipements');
        exit;
    }

    /**
     * Filtrer les équipements (AJAX)
     */
    public function filter()
    {
        header('Content-Type: application/json');

        $filters = [
            'type' => $_GET['type'] ?? '',
            'etat' => $_GET['etat'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        $equipements = $this->model->filter($filters);
        $isLoggedIn = isset($_SESSION['user']);

        ob_start();
        $this->view->renderEquipementsList($equipements, $isLoggedIn);
        $html = ob_get_clean();

        echo json_encode([
            'success' => true,
            'html' => $html,
            'count' => count($equipements)
        ]);
    }
}
?>