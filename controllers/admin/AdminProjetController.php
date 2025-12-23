<?php
require_once __DIR__ . '/../../models/ProjetModel.php';
require_once __DIR__ . '/../../views/admin/AdminProjetView.php';
require_once __DIR__ . '/../../views/BaseView.php';

class AdminProjetController
{
    private $model;
    private $view;

    public function __construct()
    {
        // Vérifier si admin
        $this->checkAdmin();

        $this->model = new ProjetModel();
        $this->view = new AdminProjetView();
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
     * Liste des projets
     */
    public function index()
    {
        $projets = $this->model->getAllWithDetails();
        $stats = $this->model->getStatistics();

        $this->view->renderListe($projets, $stats);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $thematiques = $this->getThematiques();
        $statuts = $this->getStatuts();
        $typesFinancement = $this->getTypesFinancement();
        $membres = $this->getMembres();

        $this->view->renderForm(null, $thematiques, $statuts, $typesFinancement, $membres, []);
    }

    /**
     * Enregistrer un nouveau projet
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=projets');
            exit;
        }

        // Validation basique
        $errors = $this->validate($_POST);

        if (!empty($errors)) {
            BaseView::setFlash(implode(', ', $errors), 'error');
            header('Location: ?page=admin&section=projets&action=create');
            exit;
        }

        // Préparer les données
        $data = [
            'titre' => $_POST['titre'],
            'description' => $_POST['description'],
            'objectifs' => $_POST['objectifs'] ?? null,
            'id_thematique' => $_POST['id_thematique'],
            'id_statut' => $_POST['id_statut'],
            'id_type_financement' => $_POST['id_type_financement'],
            'responsable_id' => $_POST['responsable_id'],
            'date_debut' => $_POST['date_debut'],
            'date_fin' => !empty($_POST['date_fin']) ? $_POST['date_fin'] : null,
            'budget' => !empty($_POST['budget']) ? $_POST['budget'] : null
        ];

        // Insérer le projet
        $id = $this->model->insert($data);

        if ($id) {
            // Associer les membres au projet
            if (!empty($_POST['membres'])) {
                $membres = $this->prepareMembresData($_POST['membres']);
                $this->model->syncMembres($id, $membres);
            }

            BaseView::setFlash('Projet créé avec succès !', 'success');
            header('Location: ?page=admin&section=projets');
        } else {
            BaseView::setFlash('Erreur lors de la création du projet', 'error');
            header('Location: ?page=admin&section=projets&action=create');
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
            header('Location: ?page=admin&section=projets');
            exit;
        }

        $projet = $this->model->getById($id);

        if (!$projet) {
            BaseView::setFlash('Projet introuvable', 'error');
            header('Location: ?page=admin&section=projets');
            exit;
        }

        $thematiques = $this->getThematiques();
        $statuts = $this->getStatuts();
        $typesFinancement = $this->getTypesFinancement();
        $membres = $this->getMembres();

        // Récupérer les membres associés au projet
        $projetMembres = $this->model->getMembres($id);

        $this->view->renderForm($projet, $thematiques, $statuts, $typesFinancement, $membres, $projetMembres);
    }

    /**
     * Mettre à jour un projet
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=projets');
            exit;
        }

        $id = $_POST['id'] ?? null;

        if (!$id) {
            header('Location: ?page=admin&section=projets');
            exit;
        }

        // Validation
        $errors = $this->validate($_POST);

        if (!empty($errors)) {
            BaseView::setFlash(implode(', ', $errors), 'error');
            header('Location: ?page=admin&section=projets&action=edit&id=' . $id);
            exit;
        }

        // Préparer les données
        $data = [
            'titre' => $_POST['titre'],
            'description' => $_POST['description'],
            'objectifs' => $_POST['objectifs'] ?? null,
            'id_thematique' => $_POST['id_thematique'],
            'id_statut' => $_POST['id_statut'],
            'id_type_financement' => $_POST['id_type_financement'],
            'responsable_id' => $_POST['responsable_id'],
            'date_debut' => $_POST['date_debut'],
            'date_fin' => !empty($_POST['date_fin']) ? $_POST['date_fin'] : null,
            'budget' => !empty($_POST['budget']) ? $_POST['budget'] : null
        ];

        // Mettre à jour le projet
        $success = $this->model->update($id, $data);

        if ($success) {
            // Synchroniser les membres du projet
            if (isset($_POST['membres'])) {
                $membres = $this->prepareMembresData($_POST['membres']);
                $this->model->syncMembres($id, $membres);
            }

            BaseView::setFlash('Projet mis à jour avec succès !', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la mise à jour du projet', 'error');
        }

        header('Location: ?page=admin&section=projets');
        exit;
    }

    /**
     * Supprimer un projet
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ?page=admin&section=projets');
            exit;
        }

        $success = $this->model->delete($id);

        if ($success) {
            BaseView::setFlash('Projet supprimé avec succès !', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la suppression du projet', 'error');
        }

        header('Location: ?page=admin&section=projets');
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

        if (empty($data['description'])) {
            $errors[] = 'La description est requise';
        }

        if (empty($data['id_thematique'])) {
            $errors[] = 'La thématique est requise';
        }

        if (empty($data['id_statut'])) {
            $errors[] = 'Le statut est requis';
        }

        if (empty($data['id_type_financement'])) {
            $errors[] = 'Le type de financement est requis';
        }

        if (empty($data['responsable_id'])) {
            $errors[] = 'Le responsable est requis';
        }

        if (empty($data['date_debut'])) {
            $errors[] = 'La date de début est requise';
        }

        return $errors;
    }

    /**
     * Préparer les données des membres depuis le formulaire
     */
    private function prepareMembresData($membresPost)
    {
        $membres = [];

        foreach ($membresPost as $membreData) {
            // Ignorer les lignes vides
            if (empty($membreData['id_membre'])) {
                continue;
            }

            $membres[] = [
                'id_membre' => $membreData['id_membre'],
                'role_projet' => !empty($membreData['role_projet']) ? $membreData['role_projet'] : 'Membre',
                'date_debut' => !empty($membreData['date_debut']) ? $membreData['date_debut'] : null,
                'date_fin' => !empty($membreData['date_fin']) ? $membreData['date_fin'] : null
            ];
        }

        return $membres;
    }

    /**
     * Méthodes utilitaires
     */
    private function getThematiques()
    {
        return $this->model->query("SELECT id_thematique as id, nom_thematique as nom FROM thematiques ORDER BY nom_thematique");
    }

    private function getStatuts()
    {
        return $this->model->query("SELECT * FROM statuts_projet ORDER BY nom_statut");
    }

    private function getTypesFinancement()
    {
        return $this->model->query("SELECT id_type_financement as id, nom_type as nom FROM types_financement ORDER BY nom_type");
    }

    private function getMembres()
    {
        return $this->model->query("SELECT * FROM membres WHERE actif = 1 ORDER BY nom, prenom");
    }
}
?>