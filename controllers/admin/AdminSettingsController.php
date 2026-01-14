<?php
require_once __DIR__ . '/../../models/SettingsModel.php';
require_once __DIR__ . '/../../views/admin/AdminSettingsView.php';

class AdminSettingsController
{
    private $model;
    private $view;

    public function __construct()
    {
        // Check if user is admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: ?page=admin');
            exit;
        }

        $this->model = new SettingsModel();
        $this->view = new AdminSettingsView();
    }

    /**
     * Display settings form
     */
    public function index()
    {
        $settings = $this->model->getAllSettings();
        $this->view->render($settings);
    }

    /**
     * Update settings
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=settings');
            exit;
        }

        // Get all POST data
        $settingsToUpdate = [
            // Site Info
            'site_title' => $_POST['site_title'] ?? '',
            'site_description' => $_POST['site_description'] ?? '',
            'site_keywords' => $_POST['site_keywords'] ?? '',
            'site_author' => $_POST['site_author'] ?? '',
            'site_url' => $_POST['site_url'] ?? '',
            'theme_color' => $_POST['theme_color'] ?? '#3b82f6',
            
            // Contact Info
            'contact_adresse' => $_POST['contact_adresse'] ?? '',
            'contact_telephone' => $_POST['contact_telephone'] ?? '',
            'contact_email' => $_POST['contact_email'] ?? '',
            'contact_fax' => $_POST['contact_fax'] ?? '',
            
            // Social Networks
            'reseaux_facebook' => $_POST['reseaux_facebook'] ?? '',
            'reseaux_twitter' => $_POST['reseaux_twitter'] ?? '',
            'reseaux_linkedin' => $_POST['reseaux_linkedin'] ?? '',
            
            // Opening Hours
            'horaires_ouverture' => $_POST['horaires_ouverture'] ?? '',
            
            // Open Graph
            'og_title' => $_POST['og_title'] ?? '',
            'og_description' => $_POST['og_description'] ?? ''
        ];

        // Update settings
        $success = $this->model->updateMultipleSettings($settingsToUpdate);

        if ($success) {
            require_once __DIR__ . '/../../views/BaseView.php';
            BaseView::setFlash('Paramètres mis à jour avec succès', 'success');
        } else {
            require_once __DIR__ . '/../../views/BaseView.php';
            BaseView::setFlash('Erreur lors de la mise à jour', 'error');
        }

        header('Location: ?page=admin&section=settings');
        exit;
    }
}