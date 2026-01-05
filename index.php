<?php
// ⚠️ IMPORTANT: Start session first!
session_start();

require_once __DIR__ . '/config/constants.php';

// Routeur simple
$page = $_GET['page'] ?? 'accueil';
$action = $_GET['action'] ?? 'index';

// Routes disponibles
switch ($page) {
    case 'login':
        require_once __DIR__ . '/controllers/LoginController.php';
        $controller = new LoginController();

        switch ($action) {
            case 'login':
                $controller->login();
                break;
            default:
                $controller->index();
        }
        break;

    case 'logout':
        require_once __DIR__ . '/controllers/LoginController.php';
        $controller = new LoginController();
        $controller->logout();
        break;

    case 'profil':
        require_once __DIR__ . '/controllers/ProfilController.php';
        $controller = new ProfilController();

        switch ($action) {
            case 'edit':
                $controller->edit();
                break;
            case 'update':
                $controller->update();
                break;
            case 'updatePhoto':
                $controller->updatePhoto();
                break;
            case 'documents':
                $controller->documents();
                break;
            case 'uploadDocument':
                $controller->uploadDocument();
                break;
            case 'deleteDocument':
                $controller->deleteDocument();
                break;
            case 'changePassword':
                $controller->changePassword();
                break;
            case 'getDocuments':
                $controller->getDocuments();
                break;
            default:
                $controller->index();
        }
        break;

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

            case 'historique':
                $controller->historique();
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

            case 'tous':
                $controller->tousLesMembres();
                break;

            case 'publications-equipe':
                $controller->publicationsEquipe();
                break;
            default:
                $controller->index();
        }
        break;
    case 'evenements':
        require_once __DIR__ . '/controllers/EvenementController.php';
        $controller = new EvenementController();

        switch ($action) {
            case 'details':
                $controller->details();
                break;
            case 'filter':
                $controller->filter();
                break;
            case 'inscrire':
                $controller->inscrire();
                break;
            case 'annuler':
                $controller->annuler();
                break;
            default:
                $controller->index();
        }
        break;
    case 'offres':
        require_once __DIR__ . '/controllers/OffreController.php';
        $controller = new OffreController();

        switch ($action) {
            case 'details':
                $controller->details();
                break;
            default:
                $controller->index();
        }
        break;

    case 'contact':
        require_once __DIR__ . '/controllers/ContactController.php';
        $controller = new ContactController();

        switch ($action) {
            case 'send':
                $controller->send();
                break;
            default:
                $controller->index();
        }
        break;

    case 'admin':
        // ⭐ CHANGÉ: Ne plus bloquer tous les non-admins, juste vérifier si connecté
        // Les permissions spécifiques seront vérifiées dans chaque contrôleur
        if (!isset($_SESSION['user'])) {
            require_once __DIR__ . '/views/BaseView.php';
            BaseView::setFlash('Accès refusé. Vous devez être connecté.', 'error');
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
                    case 'stats':
                        $controller->stats();
                        break;
                    case 'generate_pdf':
                        $controller->generate_pdf();
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
                    case 'stats':
                        $controller->stats();
                        break;
                    case 'generate_pdf':
                        $controller->generate_pdf();
                        break;
                    default:
                        $controller->index();
                }
                break;

            case 'equipements':
                require_once __DIR__ . '/controllers/admin/AdminEquipementController.php';
                $controller = new AdminEquipementController();

                switch ($action) {
                    case 'historique':
                        $controller->historique();
                        break;
                    case 'demandes':
                        $controller->demandes();
                        break;
                    case 'approuverDemande':
                        $controller->approuverDemande();
                        break;
                    case 'rejeterDemande':
                        $controller->rejeterDemande();
                        break;
                    case 'genererRapport':
                        $controller->genererRapport();
                        break;
                    case 'details':
                        $controller->details();
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
                    case 'store':
                        $controller->store();
                        break;
                    case 'update':
                        $controller->update();
                        break;
                    case 'toggleStatus':
                        $controller->toggleStatus();
                        break;
                    case 'delete':
                        $controller->delete();
                        break;
                    default:
                        $controller->index();
                }
                break;

            case 'equipes':
                require_once __DIR__ . '/controllers/admin/AdminEquipeController.php';
                $controller = new AdminEquipeController();

                switch ($action) {
                    case 'details':
                        $controller->details();
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
                    case 'addMembre':
                        $controller->addMembre();
                        break;
                    case 'removeMembre':
                        $controller->removeMembre();
                        break;
                    default:
                        $controller->index();
                }
                break;
            case 'evenements':
                require_once __DIR__ . '/controllers/admin/AdminEvenementController.php';
                $controller = new AdminEvenementController();

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
                    case 'togglePublish':
                        $controller->togglePublish();
                        break;
                    case 'inscriptions':
                        $controller->inscriptions();
                        break;
                    case 'updateParticipantStatus':
                        $controller->updateParticipantStatus();
                        break;
                    case 'deleteParticipant':
                        $controller->deleteParticipant();
                        break;
                    default:
                        $controller->index();
                }
                break;

            case 'annonces':
                require_once __DIR__ . '/controllers/admin/AdminAnnonceController.php';
                $controller = new AdminAnnonceController();

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
                    case 'togglePublish':
                        $controller->togglePublish();
                        break;
                    default:
                        $controller->index();
                }
                break;
            case 'messages':
                require_once __DIR__ . '/controllers/admin/AdminContactController.php';
                $controller = new AdminContactController();

                switch ($action) {
                    case 'details':
                        $controller->details();
                        break;
                    case 'markAsRead':
                        $controller->markAsRead();
                        break;
                    case 'archive':
                        $controller->archive();
                        break;
                    case 'delete':
                        $controller->delete();
                        break;
                    default:
                        $controller->index();
                }
                break;

            case 'offres':
                require_once __DIR__ . '/controllers/admin/AdminOffreController.php';
                $controller = new AdminOffreController();

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
                    case 'toggleStatus':
                        $controller->toggleStatus();
                        break;
                    case 'delete':
                        $controller->delete();
                        break;
                    default:
                        $controller->index();
                }
                break;
            case 'permissions':
                // ⭐ AJOUT: Cette section est SEULEMENT pour les vrais admins
                if ($_SESSION['user']['role'] !== 'admin') {
                    require_once __DIR__ . '/views/BaseView.php';
                    BaseView::setFlash('Accès refusé. Cette section est réservée aux administrateurs.', 'error');
                    header('Location: ?page=admin');
                    exit;
                }

                require_once __DIR__ . '/controllers/admin/AdminPermissionController.php';
                $controller = new AdminPermissionController();

                switch ($action) {
                    case 'roles':
                        $controller->roles();
                        break;
                    case 'configRole':
                        $controller->configRole();
                        break;
                    case 'saveRoleConfig':
                        $controller->saveRoleConfig();
                        break;
                    case 'createRole':
                        $controller->createRole();
                        break;
                    case 'storeRole':
                        $controller->storeRole();
                        break;
                    case 'deleteRole':
                        $controller->deleteRole();
                        break;
                    case 'userPermissions':
                        $controller->userPermissions();
                        break;
                    case 'saveUserPermissions':
                        $controller->saveUserPermissions();
                        break;
                    case 'resetUserPermissions':
                        $controller->resetUserPermissions();
                        break;
                    case 'logs':
                        $controller->logs();
                        break;
                    default:
                        $controller->index();
                }
                break;

            case 'dashboard':
            default:
                // ⭐ Utiliser le code du dashboard personnalisé que j'ai donné au-dessus
                require_once __DIR__ . '/utils/PermissionHelper.php';

                echo '<div class="admin-dashboard">';
                echo '<div class="container">';

                if ($_SESSION['user']['role'] === 'admin') {
                    echo '<h1>Dashboard Admin</h1>';
                    echo '<p>Bienvenue ' . htmlspecialchars($_SESSION['user']['prenom']) . '</p>';
                } else {
                    echo '<h1>Mon Espace de Gestion</h1>';
                    echo '<p>Bienvenue ' . htmlspecialchars($_SESSION['user']['prenom']) . ', voici les sections que vous pouvez gérer :</p>';
                }

                echo '<div class="admin-cards-grid">';

                // Cartes conditionnelles (voir artifact précédent)
                if (hasPermission('view_membres')) {
                    echo '<a href="?page=admin&section=membres" class="admin-card">';
                    echo '<i class="fas fa-users"></i>';
                    echo '<h3>Gérer les membres</h3>';
                    echo '</a>';
                }

                if (hasPermission('view_equipes')) {
                    echo '<a href="?page=admin&section=equipes" class="admin-card">';
                    echo '<i class="fas fa-users-cog"></i>';
                    echo '<h3>Gérer les équipes</h3>';
                    echo '</a>';
                }

                if (hasPermission('view_projets')) {
                    echo '<a href="?page=admin&section=projets" class="admin-card">';
                    echo '<i class="fas fa-project-diagram"></i>';
                    echo '<h3>Gérer les projets</h3>';
                    echo '</a>';
                }

                if (hasPermission('view_publications')) {
                    echo '<a href="?page=admin&section=publications" class="admin-card">';
                    echo '<i class="fas fa-file-alt"></i>';
                    echo '<h3>Gérer les publications</h3>';
                    echo '</a>';
                }

                if (hasPermission('view_equipements')) {
                    echo '<a href="?page=admin&section=equipements" class="admin-card">';
                    echo '<i class="fas fa-server"></i>';
                    echo '<h3>Gérer les équipements</h3>';
                    echo '</a>';
                }

                if (hasPermission('view_evenements')) {
                    echo '<a href="?page=admin&section=evenements" class="admin-card">';
                    echo '<i class="fas fa-calendar-alt"></i>';
                    echo '<h3>Gérer les événements</h3>';
                    echo '</a>';
                }

                if (hasPermission('view_annonces')) {
                    echo '<a href="?page=admin&section=annonces" class="admin-card">';
                    echo '<i class="fas fa-bullhorn"></i>';
                    echo '<h3>Gérer les annonces</h3>';
                    echo '</a>';
                }

                if (hasPermission('view_messages')) {
                    echo '<a href="?page=admin&section=messages" class="admin-card">';
                    echo '<i class="fas fa-envelope"></i>';
                    echo '<h3>Gérer les messages</h3>';
                    echo '</a>';
                }

                if (hasPermission('view_offres')) {
                    echo '<a href="?page=admin&section=offres" class="admin-card">';
                    echo '<i class="fas fa-briefcase"></i>';
                    echo '<h3>Gérer les offres</h3>';
                    echo '</a>';
                }

                if ($_SESSION['user']['role'] === 'admin') {
                    echo '<a href="?page=admin&section=permissions" class="admin-card">';
                    echo '<i class="fas fa-shield-alt"></i>';
                    echo '<h3>Gérer les Permissions</h3>';
                    echo '</a>';
                }

                echo '</div>';

                // Message si aucune permission
                if (
                    !hasPermission('view_membres') &&
                    !hasPermission('view_equipes') &&
                    !hasPermission('view_projets') &&
                    !hasPermission('view_publications') &&
                    !hasPermission('view_equipements') &&
                    !hasPermission('view_evenements') &&
                    !hasPermission('view_annonces') &&
                    !hasPermission('view_messages') &&
                    !hasPermission('view_offres')
                ) {

                    echo '<div style="text-align: center; padding: 3rem; background: white; border-radius: 12px; margin-top: 2rem;">';
                    echo '<i class="fas fa-lock" style="font-size: 4rem; color: var(--gray-400); margin-bottom: 1rem;"></i>';
                    echo '<h3 style="color: var(--gray-600);">Aucun accès configuré</h3>';
                    echo '<p style="color: var(--gray-500);">Vous n\'avez pas encore d\'accès aux fonctionnalités de gestion. Contactez un administrateur.</p>';
                    echo '</div>';
                }

                echo '<a href="?page=logout" class="btn btn-outline" style="margin-top: 2rem;">Déconnexion</a>';
                echo '</div>';
                echo '</div>';

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