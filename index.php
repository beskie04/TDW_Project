<?php
require_once __DIR__ . '/config/constants.php';

// Routeur simple
$page = $_GET['page'] ?? 'accueil';
$action = $_GET['action'] ?? 'index';

// Routes disponibles
switch ($page) {
    case 'projets':
        require_once __DIR__ . '/controllers/ProjetController.php';
        $controller = new ProjetController();

        switch ($action) {
            case 'filter':
                $controller->filter();
                break;
            case 'details':
                $controller->details();
                break;
            default:
                $controller->index();
        }
        break;

    case 'publications':
        require_once __DIR__ . '/controllers/PublicationController.php';
        $controller = new PublicationController();

        switch ($action) {
            case 'filter':
                $controller->filter();
                break;
            default:
                $controller->index();
        }
        break;

    case 'equipements':
        require_once __DIR__ . '/controllers/EquipementController.php';
        $controller = new EquipementController();

        switch ($action) {
            case 'details':
                $controller->details();
                break;
            case 'reserver':
                $controller->reserver();
                break;
            case 'confirmer_reservation':
                $controller->confirmer_reservation();
                break;
            case 'annuler':
                $controller->annuler();
                break;
            case 'filter':
                $controller->filter();
                break;
            default:
                $controller->index();
        }
        break;

    case 'membres':
        require_once __DIR__ . '/controllers/MembreController.php';
        $controller = new MembreController();

        switch ($action) {
            case 'equipe':
                $controller->equipe();
                break;
            case 'biographie':
                $controller->biographie();
                break;
            case 'publications':
                $controller->publications();
                break;
            default:
                $controller->index();
        }
        break;

    case 'contact':
        // TODO: À implémenter
        echo "Contact - À venir";
        break;

    case 'login':
        require_once __DIR__ . '/controllers/LoginController.php';
        $controller = new LoginController();

        if ($action === 'authenticate') {
            $controller->login();
        } else {
            $controller->index();
        }
        break;

    case 'logout':
        session_destroy();
        header('Location: ?page=accueil');
        exit;
        break;

    case 'admin':
        // Vérifier si admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: ?page=login');
            exit;
        }

        $section = $_GET['section'] ?? 'dashboard';
        $action = $_GET['action'] ?? 'index';

        switch ($section) {
            case 'projets':
                require_once __DIR__ . '/controllers/admin/AdminProjetController.php';
                $controller = new AdminProjetController();

                switch ($action) {
                    case 'create':
                        $controller->create();
                        break;
                    case 'store':
                        $controller->store();
                        break;
                    case 'edit':
                        $controller->edit();
                        break;
                    case 'update':
                        $controller->update();
                        break;
                    case 'delete':
                        $controller->delete();
                        break;
                    default:
                        $controller->index();
                }
                break;

            case 'publications':
                require_once __DIR__ . '/controllers/admin/AdminPublicationController.php';
                $controller = new AdminPublicationController();

                switch ($action) {
                    case 'pending':
                        $controller->pending();
                        break;
                    case 'validatePublication':
                        $controller->validatePublication();
                        break;
                    case 'create':
                        $controller->create();
                        break;
                    case 'store':
                        $controller->store();
                        break;
                    case 'edit':
                        $controller->edit();
                        break;
                    case 'update':
                        $controller->update();
                        break;
                    case 'delete':
                        $controller->delete();
                        break;
                    default:
                        $controller->index();
                }
                break;

            case 'equipements':
                require_once __DIR__ . '/controllers/admin/AdminEquipementController.php';
                $controller = new AdminEquipementController();

                switch ($action) {
                    case 'create':
                        $controller->create();
                        break;
                    case 'store':
                        $controller->store();
                        break;
                    case 'edit':
                        $controller->edit();
                        break;
                    case 'update':
                        $controller->update();
                        break;
                    case 'delete':
                        $controller->delete();
                        break;
                    case 'annuler_reservation':
                        $controller->annuler_reservation();
                        break;
                    default:
                        $controller->index();
                }
                break;

            case 'membres':
                require_once __DIR__ . '/controllers/admin/AdminMembreController.php';
                $controller = new AdminMembreController();

                switch ($action) {
                    case 'create':
                        $controller->create();
                        break;
                    case 'store':
                        $controller->store();
                        break;
                    case 'edit':
                        $controller->edit();
                        break;
                    case 'update':
                        $controller->update();
                        break;
                    case 'delete':
                        $controller->delete();
                        break;
                    default:
                        $controller->index();
                }
                break;

            case 'dashboard':
            default:
                // TODO: Dashboard admin
                echo '<h1>Dashboard Admin</h1>';
                echo '<a href="?page=admin&section=projets">Gérer les projets</a>';
                break;
        }
        break;

    case 'accueil':
    default:
        require_once __DIR__ . '/controllers/AccueilController.php';
        $controller = new AccueilController();
        $controller->index();
        break;
}
?>