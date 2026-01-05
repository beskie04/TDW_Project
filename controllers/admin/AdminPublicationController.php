<?php
require_once __DIR__ . '/../../models/PublicationModel.php';
require_once __DIR__ . '/../../views/admin/AdminPublicationView.php';
require_once __DIR__ . '/../../views/BaseView.php';
require_once __DIR__ . '/../../utils/PdfGenerator.php';

class AdminPublicationController
{
    private $model;
    private $view;

    public function __construct()
    {
        $this->checkAdmin();
        $this->model = new PublicationModel();
        $this->view = new AdminPublicationView();
    }

    private function checkAdmin()
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: ?page=login');
            exit;
        }
    }

    public function index()
    {
        $publications = $this->model->getAllWithDetails();
        $stats = $this->model->getStatistics();
        $this->view->renderListe($publications, $stats);
    }

    /**
     * Display statistics page - NEW!
     */
    public function stats()
    {
        $stats = $this->getEnhancedStatistics();
        $types = TYPES_PUBLICATIONS;
        $auteurs = $this->model->getAuteurs();
        $annees = $this->model->query("SELECT DISTINCT annee FROM publications ORDER BY annee DESC");
        $projets = $this->model->query("SELECT id_projet, titre FROM projets ORDER BY titre");

        $this->view->renderStatistics($stats, $types, $auteurs, $annees, $projets);
    }

    /**
     * Generate PDF report - NEW!
     */
    public function generate_pdf()
    {
        $type = $_GET['type'] ?? 'all';
        $filters = [];
        $publications = [];
        $title = 'Rapport des Publications';

        // Build filters based on type
        switch ($type) {
            case 'type':
                $typeValue = $_GET['type_value'] ?? '';
                if ($typeValue) {
                    $filters['type'] = $typeValue;
                    $title = 'Rapport des Publications - Type: ' . strtoupper($typeValue);
                }
                break;

            case 'auteur':
                $auteurValue = $_GET['auteur_value'] ?? '';
                if ($auteurValue) {
                    $filters['auteur'] = $auteurValue;
                    $title = 'Rapport des Publications - Auteur: ' . $auteurValue;
                }
                break;

            case 'annee':
                $anneeValue = $_GET['annee_value'] ?? '';
                if ($anneeValue) {
                    $filters['annee'] = $anneeValue;
                    $title = 'Rapport des Publications - Année ' . $anneeValue;
                }
                break;

            case 'projet':
                $projetValue = $_GET['projet_value'] ?? '';
                if ($projetValue) {
                    $filters['projet'] = $projetValue;
                    $projetData = $this->model->query("SELECT titre FROM projets WHERE id_projet = :id", ['id' => $projetValue]);
                    if (!empty($projetData)) {
                        $title = 'Rapport des Publications - Projet: ' . $projetData[0]['titre'];
                    }
                }
                break;
        }

        // Get filtered publications
        $publications = $this->getFilteredPublications($filters);

        // Generate PDF
        try {
            $this->generatePublicationsPdf($publications, $title, $filters);
        } catch (Exception $e) {
            BaseView::setFlash('Erreur lors de la génération du PDF: ' . $e->getMessage(), 'error');
            header('Location: ?page=admin&section=publications&action=stats');
            exit;
        }
    }

    public function pending()
    {
        $publications = $this->model->getPending();
        $this->view->renderPending($publications);
    }

    public function create()
    {
        $types = TYPES_PUBLICATIONS;
        $domaines = $this->getDomaines();
        $this->view->renderForm(null, $types, $domaines);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=publications');
            exit;
        }

        $errors = $this->validate($_POST);
        if (!empty($errors)) {
            BaseView::setFlash(implode(', ', $errors), 'error');
            header('Location: ?page=admin&section=publications&action=create');
            exit;
        }

        // Prepare data with AUTO-CATEGORIZATION
        $data = [
            'titre' => $_POST['titre'],
            'auteurs' => $_POST['auteurs'],
            'annee' => $_POST['annee'],
            'type' => $_POST['type'],
            'id_thematique' => $_POST['id_thematique'],
            'doi' => !empty($_POST['doi']) ? $_POST['doi'] : null,
            'resume' => !empty($_POST['resume']) ? $_POST['resume'] : null,
            'date_publication' => !empty($_POST['date_publication']) ? $_POST['date_publication'] : null,
            'validee' => 0
        ];

        // AUTO-CATEGORIZE: Detect project based on keywords
        $detectedProject = $this->detectProject($data['titre'], $data['resume'] ?? '');
        if ($detectedProject) {
            $data['id_projet'] = $detectedProject;
        }

        // Handle file upload
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

        $id = $this->model->insert($data);

        if ($id) {
            BaseView::setFlash('Publication créée avec succès ! ' . ($detectedProject ? '(Catégorisée automatiquement)' : ''), 'success');
            header('Location: ?page=admin&section=publications');
        } else {
            BaseView::setFlash('Erreur lors de la création de la publication', 'error');
            header('Location: ?page=admin&section=publications&action=create');
        }
        exit;
    }

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

        $errors = $this->validate($_POST);
        if (!empty($errors)) {
            BaseView::setFlash(implode(', ', $errors), 'error');
            header('Location: ?page=admin&section=publications&action=edit&id=' . $id);
            exit;
        }

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

        // Re-categorize if needed
        $detectedProject = $this->detectProject($data['titre'], $data['resume'] ?? '');
        if ($detectedProject) {
            $data['id_projet'] = $detectedProject;
        }

        // Handle file upload
        if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === 0) {
            $uploadResult = $this->handleFileUpload($_FILES['fichier']);
            if ($uploadResult['success']) {
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

        $success = $this->model->update($id, $data);
        BaseView::setFlash(
            $success ? 'Publication mise à jour avec succès !' : 'Erreur lors de la mise à jour',
            $success ? 'success' : 'error'
        );

        header('Location: ?page=admin&section=publications');
        exit;
    }

    public function validatePublication()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?page=admin&section=publications');
            exit;
        }

        $success = $this->model->validatePublication($id);
        BaseView::setFlash(
            $success ? 'Publication validée avec succès !' : 'Erreur lors de la validation',
            $success ? 'success' : 'error'
        );

        header('Location: ?page=admin&section=publications');
        exit;
    }

    public function delete()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?page=admin&section=publications');
            exit;
        }

        $publication = $this->model->getById($id);
        if (!empty($publication['fichier'])) {
            $file = UPLOADS_PATH . 'publications/' . $publication['fichier'];
            if (file_exists($file)) {
                unlink($file);
            }
        }

        $success = $this->model->delete($id);
        BaseView::setFlash(
            $success ? 'Publication supprimée avec succès !' : 'Erreur lors de la suppression',
            $success ? 'success' : 'error'
        );

        header('Location: ?page=admin&section=publications');
        exit;
    }

    /**
     * AUTO-CATEGORIZATION: Detect project based on title/abstract keywords
     */
    private function detectProject($titre, $resume)
    {
        $text = strtolower($titre . ' ' . $resume);

        // Get all projects with their keywords
        $projets = $this->model->query("
            SELECT id_projet, titre, description, objectifs 
            FROM projets 
            WHERE date_fin IS NULL OR date_fin >= NOW()
            ORDER BY date_debut DESC
        ");

        $bestMatch = null;
        $highestScore = 0;

        foreach ($projets as $projet) {
            $score = 0;
            $projetText = strtolower($projet['titre'] . ' ' . ($projet['description'] ?? '') . ' ' . ($projet['objectifs'] ?? ''));

            // Extract significant words (3+ characters)
            $projetWords = array_unique(array_filter(
                preg_split('/\s+/', $projetText),
                function ($word) {
                    return strlen($word) >= 3; }
            ));

            // Count matching words
            foreach ($projetWords as $word) {
                if (strpos($text, $word) !== false) {
                    $score++;
                }
            }

            // Boost score if project title appears in publication title
            if (strpos($titre, strtolower($projet['titre'])) !== false) {
                $score += 10;
            }

            if ($score > $highestScore && $score >= 3) { // Minimum 3 matching words
                $highestScore = $score;
                $bestMatch = $projet['id_projet'];
            }
        }

        return $bestMatch;
    }

    /**
     * Get enhanced statistics
     */
    private function getEnhancedStatistics()
    {
        $stats = $this->model->getStatistics();

        // Add author statistics (extract principal author)
        $sql = "SELECT 
                    SUBSTRING_INDEX(auteurs, ',', 1) as auteur_principal,
                    COUNT(*) as total
                FROM publications
                GROUP BY auteur_principal
                ORDER BY total DESC
                LIMIT 15";
        $stats['par_auteur'] = $this->model->query($sql);

        return $stats;
    }

    /**
     * Get filtered publications for PDF
     */
    private function getFilteredPublications($filters)
    {
        $sql = "SELECT p.*, t.nom_thematique as domaine_nom
                FROM publications p
                LEFT JOIN thematiques t ON p.id_thematique = t.id_thematique";

        $conditions = [];
        $params = [];

        if (!empty($filters['type'])) {
            $conditions[] = "p.type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['auteur'])) {
            $conditions[] = "p.auteurs LIKE :auteur";
            $params['auteur'] = '%' . $filters['auteur'] . '%';
        }

        if (!empty($filters['annee'])) {
            $conditions[] = "p.annee = :annee";
            $params['annee'] = $filters['annee'];
        }

        if (!empty($filters['projet'])) {
            $conditions[] = "p.id_projet = :projet";
            $params['projet'] = $filters['projet'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY p.annee DESC, p.titre ASC";

        return $this->model->query($sql, $params);
    }

    /**
     * Generate publications PDF
     */
    private function generatePublicationsPdf($publications, $title, $filters)
    {
        require_once(__DIR__ . '/../../libs/tcpdf/tcpdf.php');

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
        $pdf->SetCreator('Laboratoire Universitaire');
        $pdf->SetTitle($title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        // Header
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 10, $title, 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, 'Généré le ' . date('d/m/Y à H:i'), 0, 1, 'C');
        $pdf->Ln(10);

        // Summary
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Résumé', 0, 1, 'L');
        $pdf->SetFillColor(240, 248, 255);
        $pdf->SetFont('helvetica', '', 10);
        $y = $pdf->GetY();
        $pdf->RoundedRect(15, $y, 180, 20, 3, '1111', 'F');
        $pdf->SetXY(20, $y + 5);
        $pdf->Cell(0, 6, 'Nombre total de publications: ' . count($publications), 0, 1, 'L');
        $pdf->Ln(15);

        // Publications list
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Liste des Publications', 0, 1, 'L');
        $pdf->Ln(2);

        foreach ($publications as $index => $pub) {
            if ($pdf->GetY() > 250) {
                $pdf->AddPage();
            }

            $y = $pdf->GetY();
            $pdf->SetDrawColor(200, 200, 200);
            $pdf->RoundedRect(15, $y, 180, 30, 2, '1111', 'D');

            $pdf->SetXY(20, $y + 4);
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(10, 6, ($index + 1) . '.', 0, 0, 'L');
            $titre = mb_substr($pub['titre'], 0, 70) . (mb_strlen($pub['titre']) > 70 ? '...' : '');
            $pdf->Cell(0, 6, $titre, 0, 1, 'L');

            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetXY(30, $y + 11);
            $pdf->Cell(85, 5, 'Auteurs: ' . mb_substr($pub['auteurs'], 0, 40) . '...', 0, 0, 'L');
            $pdf->SetX(115);
            $pdf->Cell(0, 5, 'Type: ' . strtoupper($pub['type']), 0, 1, 'L');

            $pdf->SetXY(30, $y + 17);
            $pdf->Cell(85, 5, 'Année: ' . $pub['annee'], 0, 0, 'L');
            $pdf->SetX(115);
            $pdf->Cell(0, 5, 'Domaine: ' . ($pub['domaine_nom'] ?? 'N/A'), 0, 1, 'L');

            if (!empty($pub['doi'])) {
                $pdf->SetXY(30, $y + 23);
                $pdf->Cell(0, 5, 'DOI: ' . $pub['doi'], 0, 1, 'L');
            }

            $pdf->Ln(8);
        }

        $pdf->Output('rapport_publications_' . date('Y-m-d') . '.pdf', 'I');
        exit;
    }

    private function validate($data)
    {
        $errors = [];
        if (empty($data['titre']))
            $errors[] = 'Le titre est requis';
        if (empty($data['auteurs']))
            $errors[] = 'Les auteurs sont requis';
        if (empty($data['annee']) || !is_numeric($data['annee']))
            $errors[] = 'L\'année est requise et doit être un nombre';
        if (empty($data['type']))
            $errors[] = 'Le type est requis';
        if (empty($data['id_thematique']))
            $errors[] = 'Le domaine est requis';
        return $errors;
    }

    private function handleFileUpload($file)
    {
        $allowedTypes = ['application/pdf'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Seuls les fichiers PDF sont autorisés'];
        }

        if ($file['size'] > 10 * 1024 * 1024) {
            return ['success' => false, 'error' => 'Le fichier est trop volumineux (max 10 MB)'];
        }

        $uploadDir = UPLOADS_PATH . 'publications/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => true, 'filename' => $filename];
        }

        return ['success' => false, 'error' => 'Erreur lors de l\'upload du fichier'];
    }

    private function getDomaines()
    {
        return $this->model->query("SELECT id_thematique, nom_thematique FROM thematiques ORDER BY nom_thematique");
    }
}
?>