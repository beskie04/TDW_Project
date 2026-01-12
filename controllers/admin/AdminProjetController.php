<?php
// ⭐ AJOUT: Import PermissionHelper
require_once __DIR__ . '/../../utils/PermissionHelper.php';

require_once __DIR__ . '/../../models/ProjetModel.php';
require_once __DIR__ . '/../../models/PartenaireModel.php';
require_once __DIR__ . '/../../views/admin/AdminProjetView.php';
require_once __DIR__ . '/../../views/BaseView.php';
require_once __DIR__ . '/../../utils/PdfGenerator.php';

class AdminProjetController
{
    private $model;
    private $partenaireModel;
    private $view;

    public function __construct()
    {
        $this->checkAccess();
        $this->model = new ProjetModel();
        $this->partenaireModel = new PartenaireModel();
        $this->view = new AdminProjetView();
    }

    private function checkAccess()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ?page=login');
            exit;
        }
    }

    public function index()
    {
        // Check permission
        if (!hasPermission('view_projets')) {
            BaseView::setFlash('Accès refusé. Permission requise: voir les projets', 'error');
            header('Location: ?page=admin');
            exit;
        }

        $projets = $this->model->getAllWithDetails();
        $stats = $this->model->getStatistics();
        $this->view->renderListe($projets, $stats);
    }

    public function stats()
    {
        if (!hasPermission('view_projet_stats')) {
            BaseView::setFlash('Accès refusé. Permission requise: voir les statistiques', 'error');
            header('Location: ?page=admin&section=projets');
            exit;
        }

        $stats = $this->getEnhancedStatistics();
        $thematiques = $this->getThematiques();
        $responsables = $this->getResponsables();
        $annees = $this->getAnnees();

        $this->view->renderStatistics($stats, $thematiques, $responsables, $annees);
    }

    public function generate_pdf()
    {
        if (!hasPermission('generate_projet_pdf')) {
            BaseView::setFlash('Accès refusé. Permission requise: générer des PDF', 'error');
            header('Location: ?page=admin&section=projets&action=stats');
            exit;
        }

        $type = $_GET['type'] ?? 'all';
        $filters = [];
        $projets = [];
        $title = 'Rapport des Projets';

        switch ($type) {
            case 'thematique':
                $thematiqueId = $_GET['thematique_id'] ?? '';
                if ($thematiqueId) {
                    $filters['thematique'] = $thematiqueId;
                    $thematiqueData = $this->model->query(
                        "SELECT nom_thematique FROM thematiques WHERE id_thematique = :id",
                        ['id' => $thematiqueId]
                    );
                    $filters['thematique_nom'] = $thematiqueData[0]['nom_thematique'] ?? '';
                    $title = 'Rapport des Projets - ' . $filters['thematique_nom'];
                }
                break;

            case 'responsable':
                $responsableId = $_GET['responsable_id'] ?? '';
                if ($responsableId) {
                    $filters['responsable'] = $responsableId;
                    $responsableData = $this->model->query(
                        "SELECT nom, prenom FROM membres WHERE id_membre = :id",
                        ['id' => $responsableId]
                    );
                    $filters['responsable_nom'] = ($responsableData[0]['nom'] ?? '') . ' ' . ($responsableData[0]['prenom'] ?? '');
                    $title = 'Rapport des Projets - ' . $filters['responsable_nom'];
                }
                break;

            case 'annee':
                $annee = $_GET['annee'] ?? '';
                if ($annee) {
                    $filters['annee'] = $annee;
                    $title = 'Rapport des Projets - Année ' . $annee;
                }
                break;
        }

        $projets = $this->getFilteredProjects($filters);

        try {
            $pdfGenerator = new PdfGenerator();
            $pdfGenerator->generateProjectsReport($projets, $title, $filters);
        } catch (Exception $e) {
            BaseView::setFlash('Erreur lors de la génération du PDF: ' . $e->getMessage(), 'error');
            header('Location: ?page=admin&section=projets&action=stats');
            exit;
        }
    }

    public function create()
    {
        if (!hasPermission('create_projet') && !hasPermission('create_own_projet')) {
            BaseView::setFlash('Accès refusé. Permission requise: créer un projet', 'error');
            header('Location: ?page=admin&section=projets');
            exit;
        }

        $thematiques = $this->getThematiques();
        $statuts = $this->getStatuts();
        $typesFinancement = $this->getTypesFinancement();
        $membres = $this->getMembres();
        $partenaires = $this->getPartenaires();

        $this->view->renderForm(null, $thematiques, $statuts, $typesFinancement, $membres, [], $partenaires, []);
    }

    public function store()
    {
        if (!hasPermission('create_projet') && !hasPermission('create_own_projet')) {
            BaseView::setFlash('Accès refusé. Permission requise: créer un projet', 'error');
            header('Location: ?page=admin&section=projets');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=projets');
            exit;
        }

        $errors = $this->validate($_POST);
        if (!empty($errors)) {
            BaseView::setFlash(implode(', ', $errors), 'error');
            header('Location: ?page=admin&section=projets&action=create');
            exit;
        }

        // Handle new thematique
        $idThematique = $_POST['id_thematique'];
        if ($idThematique === '__CREATE_NEW__' && !empty($_POST['id_thematique_new'])) {
            $idThematique = $this->createThematique($_POST['id_thematique_new']);
        }

        // Handle new type financement
        $idTypeFinancement = $_POST['id_type_financement'];
        if ($idTypeFinancement === '__CREATE_NEW__' && !empty($_POST['id_type_financement_new'])) {
            $idTypeFinancement = $this->createTypeFinancement($_POST['id_type_financement_new']);
        }

        $data = [
            'titre' => $_POST['titre'],
            'description' => $_POST['description'],
            'objectifs' => $_POST['objectifs'] ?? null,
            'id_thematique' => $idThematique,
            'id_statut' => $_POST['id_statut'],
            'id_type_financement' => $idTypeFinancement,
            'responsable_id' => $_POST['responsable_id'],
            'date_debut' => $_POST['date_debut'],
            'date_fin' => !empty($_POST['date_fin']) ? $_POST['date_fin'] : null,
            
        ];

        $id = $this->model->insert($data);

        if ($id) {
            // ⭐ FIX: Admin bypass OU permission manage_projet_members
            $canManageMembers = ($_SESSION['user']['role'] === 'admin') || hasPermission('manage_projet_members');
            
            if (!empty($_POST['membres']) && $canManageMembers) {
                $membres = $this->prepareMembresData($_POST['membres']);
                $this->model->syncMembres($id, $membres);
            }

            if (!empty($_POST['partenaires']) && $canManageMembers) {
                $partenaires = $this->preparePartenairesData($_POST['partenaires']);
                $this->partenaireModel->syncProjectPartners($id, $partenaires);
            }

            BaseView::setFlash('Projet créé avec succès !', 'success');
            header('Location: ?page=admin&section=projets');
        } else {
            BaseView::setFlash('Erreur lors de la création du projet', 'error');
            header('Location: ?page=admin&section=projets&action=create');
        }
        exit;
    }

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

        $isResponsable = $_SESSION['user']['id_membre'] == $projet['responsable_id'];

        if (
            !hasPermission('edit_projet') &&
            !($isResponsable && hasPermission('edit_own_projet'))
        ) {
            BaseView::setFlash('Accès refusé. Permission requise: modifier ce projet', 'error');
            header('Location: ?page=admin&section=projets');
            exit;
        }

        $thematiques = $this->getThematiques();
        $statuts = $this->getStatuts();
        $typesFinancement = $this->getTypesFinancement();
        $membres = $this->getMembres();
        $projetMembres = $this->model->getMembres($id);
        $partenaires = $this->getPartenaires();
        $projetPartenaires = $this->partenaireModel->getProjectPartners($id);

        $this->view->renderForm($projet, $thematiques, $statuts, $typesFinancement, $membres, $projetMembres, $partenaires, $projetPartenaires);
    }

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

        $projet = $this->model->getById($id);
        if (!$projet) {
            BaseView::setFlash('Projet introuvable', 'error');
            header('Location: ?page=admin&section=projets');
            exit;
        }

        $isResponsable = $_SESSION['user']['id_membre'] == $projet['responsable_id'];

        if (
            !hasPermission('edit_projet') &&
            !($isResponsable && hasPermission('edit_own_projet'))
        ) {
            BaseView::setFlash('Accès refusé. Permission requise: modifier ce projet', 'error');
            header('Location: ?page=admin&section=projets');
            exit;
        }

        $errors = $this->validate($_POST);
        if (!empty($errors)) {
            BaseView::setFlash(implode(', ', $errors), 'error');
            header('Location: ?page=admin&section=projets&action=edit&id=' . $id);
            exit;
        }

        $idThematique = $_POST['id_thematique'];
        if ($idThematique === '__CREATE_NEW__' && !empty($_POST['id_thematique_new'])) {
            $idThematique = $this->createThematique($_POST['id_thematique_new']);
        }

        $idTypeFinancement = $_POST['id_type_financement'];
        if ($idTypeFinancement === '__CREATE_NEW__' && !empty($_POST['id_type_financement_new'])) {
            $idTypeFinancement = $this->createTypeFinancement($_POST['id_type_financement_new']);
        }

        $data = [
            'titre' => $_POST['titre'],
            'description' => $_POST['description'],
            'objectifs' => $_POST['objectifs'] ?? null,
            'id_thematique' => $idThematique,
            'id_statut' => $_POST['id_statut'],
            'id_type_financement' => $idTypeFinancement,
            'responsable_id' => $_POST['responsable_id'],
            'date_debut' => $_POST['date_debut'],
            'date_fin' => !empty($_POST['date_fin']) ? $_POST['date_fin'] : null,
            
        ];

        $success = $this->model->update($id, $data);

        if ($success) {
            // ⭐ FIX: Admin bypass OU permission manage_projet_members
            $canManageMembers = ($_SESSION['user']['role'] === 'admin') || hasPermission('manage_projet_members');
            
            if (isset($_POST['membres']) && $canManageMembers) {
                $membres = $this->prepareMembresData($_POST['membres']);
                $this->model->syncMembres($id, $membres);
            }

            if (isset($_POST['partenaires']) && $canManageMembers) {
                $partenaires = $this->preparePartenairesData($_POST['partenaires']);
                $this->partenaireModel->syncProjectPartners($id, $partenaires);
            }

            BaseView::setFlash('Projet mis à jour avec succès !', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la mise à jour du projet', 'error');
        }

        header('Location: ?page=admin&section=projets');
        exit;
    }

    public function delete()
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

        $isResponsable = $_SESSION['user']['id_membre'] == $projet['responsable_id'];

        if (
            !hasPermission('delete_projet') &&
            !($isResponsable && hasPermission('delete_own_projet'))
        ) {
            BaseView::setFlash('Accès refusé. Permission requise: supprimer ce projet', 'error');
            header('Location: ?page=admin&section=projets');
            exit;
        }

        $success = $this->model->delete($id);
        BaseView::setFlash(
            $success ? 'Projet supprimé avec succès !' : 'Erreur lors de la suppression du projet',
            $success ? 'success' : 'error'
        );

        header('Location: ?page=admin&section=projets');
        exit;
    }

    private function validate($data)
    {
        $errors = [];
        if (empty($data['titre']))
            $errors[] = 'Le titre est requis';
        if (empty($data['description']))
            $errors[] = 'La description est requise';

        if (empty($data['id_thematique'])) {
            $errors[] = 'La thématique est requise';
        } elseif ($data['id_thematique'] === '__CREATE_NEW__' && empty($data['id_thematique_new'])) {
            $errors[] = 'Le nom de la nouvelle thématique est requis';
        }

        if (empty($data['id_statut']))
            $errors[] = 'Le statut est requis';

        if (empty($data['id_type_financement'])) {
            $errors[] = 'Le type de financement est requis';
        } elseif ($data['id_type_financement'] === '__CREATE_NEW__' && empty($data['id_type_financement_new'])) {
            $errors[] = 'Le nom du nouveau type de financement est requis';
        }

        if (empty($data['responsable_id']))
            $errors[] = 'Le responsable est requis';
        if (empty($data['date_debut']))
            $errors[] = 'La date de début est requise';

        return $errors;
    }

    private function prepareMembresData($membresPost)
    {
        $membres = [];
        foreach ($membresPost as $membreData) {
            if (empty($membreData['id_membre']))
                continue;
            $membres[] = [
                'id_membre' => $membreData['id_membre'],
                'role_projet' => $membreData['role_projet'] ?? 'Membre',
                'date_debut' => $membreData['date_debut'] ?? null,
                'date_fin' => $membreData['date_fin'] ?? null
            ];
        }
        return $membres;
    }

    private function preparePartenairesData($partenairesPost)
    {
        $partenaires = [];
        foreach ($partenairesPost as $partenaireData) {
            if (empty($partenaireData['id_partenaire']))
                continue;
            $partenaires[] = [
                'id_partenaire' => $partenaireData['id_partenaire'],
                'role_partenaire' => $partenaireData['role_partenaire'] ?? 'Collaborateur',
                'date_debut' => $partenaireData['date_debut'] ?? null,
                'date_fin' => $partenaireData['date_fin'] ?? null,
                'description' => null
            ];
        }
        return $partenaires;
    }

    private function getEnhancedStatistics()
    {
        $stats = $this->model->getStatistics();

        $sql = "SELECT 
                    CONCAT(m.nom, ' ', m.prenom) as responsable_nom,
                    COUNT(p.id_projet) as total
                FROM membres m
                LEFT JOIN projets p ON m.id_membre = p.responsable_id
                WHERE p.id_projet IS NOT NULL
                GROUP BY m.id_membre, m.nom, m.prenom
                ORDER BY total DESC";
        $stats['par_responsable'] = $this->model->query($sql);

        return $stats;
    }

    private function getFilteredProjects($filters)
    {
        $sql = "SELECT p.*, 
                       t.nom_thematique as thematique_nom,
                       s.nom_statut as statut_nom,
                       tf.nom_type as type_financement_nom,
                       m.nom as responsable_nom, 
                       m.prenom as responsable_prenom
                FROM projets p
                LEFT JOIN thematiques t ON p.id_thematique = t.id_thematique
                LEFT JOIN statuts_projet s ON p.id_statut = s.id_statut
                LEFT JOIN types_financement tf ON p.id_type_financement = tf.id_type_financement
                LEFT JOIN membres m ON p.responsable_id = m.id_membre";

        $conditions = [];
        $params = [];

        if (!empty($filters['thematique'])) {
            $conditions[] = "p.id_thematique = :thematique";
            $params['thematique'] = $filters['thematique'];
        }

        if (!empty($filters['responsable'])) {
            $conditions[] = "p.responsable_id = :responsable";
            $params['responsable'] = $filters['responsable'];
        }

        if (!empty($filters['annee'])) {
            $conditions[] = "YEAR(p.date_debut) = :annee";
            $params['annee'] = $filters['annee'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY p.date_debut DESC";

        return $this->model->query($sql, $params);
    }

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

    private function getPartenaires()
    {
        return $this->partenaireModel->getAllActive();
    }

    private function getResponsables()
    {
        $sql = "SELECT DISTINCT m.id_membre, m.nom, m.prenom 
                FROM membres m 
                INNER JOIN projets p ON m.id_membre = p.responsable_id 
                ORDER BY m.nom, m.prenom";
        return $this->model->query($sql);
    }

    private function getAnnees()
    {
        $sql = "SELECT DISTINCT YEAR(date_debut) as annee 
                FROM projets 
                WHERE date_debut IS NOT NULL
                ORDER BY annee DESC";
        return $this->model->query($sql);
    }

    private function createThematique($nom)
    {
        $sql = "INSERT INTO thematiques (nom_thematique, description) VALUES (:nom, :description)";
        $this->model->execute($sql, [
            'nom' => $nom,
            'description' => 'Créé automatiquement depuis le formulaire projet'
        ]);

        $result = $this->model->query("SELECT LAST_INSERT_ID() as id");
        return $result[0]['id'];
    }

    private function createTypeFinancement($nom)
    {
        $sql = "INSERT INTO types_financement (nom_type) VALUES (:nom)";
        $this->model->execute($sql, ['nom' => $nom]);

        $result = $this->model->query("SELECT LAST_INSERT_ID() as id");
        return $result[0]['id'];
    }
}
?>