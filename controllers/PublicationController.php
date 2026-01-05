<?php
// ============================================================================
// PublicationController.php - FINAL BULLETPROOF VERSION
// ============================================================================
require_once __DIR__ . '/../models/PublicationModel.php';
require_once __DIR__ . '/../views/PublicationView.php';

class PublicationController
{
    private $model;
    private $view;

    public function __construct()
    {
        $this->model = new PublicationModel();
        $this->view = new PublicationView();
    }

    public function index()
    {
        $publications = $this->model->getAllValidated();
        $years = $this->model->getYears();
        $types = TYPES_PUBLICATIONS;
        $domaines = $this->model->getDomaines();
        $auteurs = $this->model->getAuteurs();

        $this->view->renderListe($publications, $years, $types, $domaines, $auteurs);
    }

    public function filter()
    {
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Start fresh
        ob_start();

        // Set JSON header
        header('Content-Type: application/json; charset=utf-8');

        try {
            // Build clean filters - ONLY include non-empty values
            $filters = [];

            if (isset($_GET['annee']) && $_GET['annee'] !== '' && $_GET['annee'] !== null) {
                $filters['annee'] = trim($_GET['annee']);
            }

            if (isset($_GET['type']) && $_GET['type'] !== '' && $_GET['type'] !== null) {
                $filters['type'] = trim($_GET['type']);
            }

            if (isset($_GET['domaine']) && $_GET['domaine'] !== '' && $_GET['domaine'] !== null) {
                $filters['domaine'] = trim($_GET['domaine']);
            }

            if (isset($_GET['auteur']) && $_GET['auteur'] !== '' && $_GET['auteur'] !== null) {
                $filters['auteur'] = trim($_GET['auteur']);
            }

            if (isset($_GET['search']) && $_GET['search'] !== '' && $_GET['search'] !== null) {
                $filters['search'] = trim($_GET['search']);
            }

            // Get filtered publications
            $publications = $this->model->filter($filters);

            // Capture HTML
            ob_start();
            $this->view->renderPublicationsList($publications);
            $html = ob_get_clean();

            // Clear outer buffer
            ob_end_clean();

            // Output JSON
            echo json_encode([
                'success' => true,
                'html' => $html,
                'count' => count($publications),
                'filters_applied' => $filters
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        } catch (Exception $e) {
            while (ob_get_level()) {
                ob_end_clean();
            }

            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'count' => 0,
                'html' => '<div style="padding: 2rem; text-align: center; color: #ef4444;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <h3>Erreur lors du filtrage</h3>
                    <p>' . htmlspecialchars($e->getMessage()) . '</p>
                    </div>'
            ], JSON_UNESCAPED_UNICODE);
        }

        exit;
    }
}