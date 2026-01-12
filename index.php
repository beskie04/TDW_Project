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

    // ==================== NOUVEAU: ROUTES NOTIFICATIONS ====================
    case 'notifications':
        if (!isset($_SESSION['user'])) {
            header('Location: ?page=login');
            exit;
        }

        require_once __DIR__ . '/controllers/NotificationController.php';
        $controller = new NotificationController();

        switch ($action) {
            case 'getUnreadCount':
                $controller->getUnreadCount();
                break;
            case 'getRecent':
                $controller->getRecent();
                break;
            case 'markAsRead':
                $controller->markAsRead();
                break;
            case 'markAllAsRead':
                $controller->markAllAsRead();
                break;
            case 'delete':
                $controller->delete();
                break;
            case 'test':
                $controller->test();
                break;
            default:
                $controller->index();
        }
        break;
    // ======================================================================

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
                    // ==================== NOUVEAU: ENVOI MANUEL DES RAPPELS ====================
                    case 'sendReminders':
                        require_once __DIR__ . '/models/NotificationModel.php';
                        $notificationModel = new NotificationModel();
                        
                        try {
                            // Appeler la procédure stockée pour envoyer les rappels
                            $notificationModel->sendEventReminders();
                            
                            $_SESSION['flash_message'] = 'Rappels envoyés avec succès !';
                            $_SESSION['flash_type'] = 'success';
                        } catch (Exception $e) {
                            $_SESSION['flash_message'] = 'Erreur: ' . $e->getMessage();
                            $_SESSION['flash_type'] = 'error';
                        }
                        
                        header('Location: ?page=admin&section=evenements');
                        exit;
                        break;
                    // ============================================================================
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
                    default:
                        $controller->index();
                }
                break;

           // À remplacer dans ton index.php, section: case 'dashboard':

case 'dashboard':
default:
   
    require_once __DIR__ . '/utils/PermissionHelper.php';
    ?>
    <style>
        .admin-dashboard {
            min-height: 100vh;
            background: #03045e;
            padding: 2rem 0;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            background:  #03045e;
            padding: 0 1rem;
        }
        
        .dashboard-header {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .dashboard-header h1 {
            margin: 0 0 0.5rem 0;
            color: #2d3748;
            font-size: 2rem;
            font-weight: 700;
        }
        
        .dashboard-header p {
            margin: 0;
            color: #718096;
            font-size: 1.1rem;
        }
        
        .admin-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .admin-card {
            display: block;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }
        
        .admin-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: #03045e;
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .admin-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
            border-color: #667eea;
        }
        
        .admin-card:hover::before {
            transform: scaleX(1);
        }
        
        .admin-card i {
            font-size: 3rem;
            background : #03045e;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            display: block;
        }
        
        .admin-card h3 {
            margin: 0 0 0.5rem 0;
            color: #2d3748;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .admin-card p {
            margin: 0;
            color: #718096;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .no-permissions {
            background: white;
            text-align: center;
            padding: 4rem 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-top: 2rem;
        }
        
        .no-permissions i {
            font-size: 5rem;
            color: #cbd5e0;
            margin-bottom: 1.5rem;
            display: block;
        }
        
        .no-permissions h3 {
            color: #4a5568;
            margin: 0 0 0.75rem 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .no-permissions p {
            color: #718096;
            margin: 0;
            font-size: 1.05rem;
        }
        
        .dashboard-footer {
            background: white;
            margin-top: 3rem;
            text-align: center;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .btn-secondary {
            background: #03045e;
            color: white;
            margin-right: 1rem;
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-outline {
            background: transparent;
            color: #03045e;
            border-color: #03045e;
        }
        
        .btn-outline:hover {
            background: #03045e;
            color: white;
        }
        
        @media (max-width: 768px) {
            .admin-cards-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-header h1 {
                font-size: 1.5rem;
            }
            
            .btn {
                display: block;
                margin: 0.5rem 0 !important;
            }
        }
    </style>

    <div class="admin-dashboard">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <h1>Dashboard Administration</h1>
                    <p>Bienvenue <?= htmlspecialchars($_SESSION['user']['prenom']) ?>, vous avez un accès complet à toutes les fonctionnalités.</p>
                <?php else: ?>
                    <h1>Mon Espace de Gestion</h1>
                    <p>Bienvenue <?= htmlspecialchars($_SESSION['user']['prenom']) ?>, voici les actions que vous pouvez effectuer :</p>
                <?php endif; ?>
            </div>

            <?php
            $hasAnyPermission = false;
            
            if ($_SESSION['user']['role'] === 'admin') {
                $hasAnyPermission = true;
                
                echo '<div class="admin-cards-grid">';
                
                $adminCards = [
                    // NOUVELLE CARTE UNIFIÉE
                    [
                        'url' => 'membres', 
                        'icon' => 'fa-users-cog', 
                        'title' => 'Membres & Permissions', 
                        'desc' => 'Gérer les utilisateurs et leurs accès'
                    ],
                    ['url' => 'equipes', 'icon' => 'fa-users-cog', 'title' => 'Gérer les équipes', 'desc' => 'Organiser les équipes de recherche'],
                    ['url' => 'projets', 'icon' => 'fa-project-diagram', 'title' => 'Gérer les projets', 'desc' => 'Créer, modifier, suivre les projets'],
                    ['url' => 'publications', 'icon' => 'fa-file-alt', 'title' => 'Gérer les publications', 'desc' => 'Valider et organiser les publications'],
                    ['url' => 'equipements', 'icon' => 'fa-server', 'title' => 'Gérer les équipements', 'desc' => 'Gestion complète des ressources'],
                    ['url' => 'evenements', 'icon' => 'fa-calendar-alt', 'title' => 'Gérer les événements', 'desc' => 'Créer et gérer les événements'],
                    ['url' => 'annonces', 'icon' => 'fa-bullhorn', 'title' => 'Gérer les annonces', 'desc' => 'Publier des annonces'],
                    ['url' => 'messages', 'icon' => 'fa-envelope', 'title' => 'Gérer les messages', 'desc' => 'Voir les messages de contact'],
                    ['url' => 'offres', 'icon' => 'fa-briefcase', 'title' => 'Gérer les offres', 'desc' => 'Stages, thèses, collaborations']
                ];
                
                foreach ($adminCards as $card) {
                    echo '<a href="?page=admin&section=' . $card['url'] . '" class="admin-card">';
                    echo '<i class="fas ' . $card['icon'] . '"></i>';
                    echo '<h3>' . $card['title'] . '</h3>';
                    echo '<p>' . $card['desc'] . '</p>';
                    echo '</a>';
                }
                
                echo '</div>';
                
            } else {
                $ownPermissions = [];
                
                if (hasPermission('create_own_projet') || hasPermission('edit_own_projet') || hasPermission('delete_own_projet')) {
                    $capabilities = [];
                    if (hasPermission('create_own_projet')) $capabilities[] = 'Créer';
                    if (hasPermission('edit_own_projet')) $capabilities[] = 'Modifier';
                    if (hasPermission('delete_own_projet')) $capabilities[] = 'Supprimer';
                    
                    $ownPermissions[] = [
                        'url' => 'projets',
                        'icon' => 'fa-project-diagram',
                        'title' => 'Mes Projets',
                        'desc' => implode(', ', $capabilities) . ' mes projets'
                    ];
                    $hasAnyPermission = true;
                }
                
                if (hasPermission('create_own_publication') || hasPermission('edit_own_publication') || hasPermission('delete_own_publication')) {
                    $capabilities = [];
                    if (hasPermission('create_own_publication')) $capabilities[] = 'Créer';
                    if (hasPermission('edit_own_publication')) $capabilities[] = 'Modifier';
                    if (hasPermission('delete_own_publication')) $capabilities[] = 'Supprimer';
                    
                    $ownPermissions[] = [
                        'url' => 'publications',
                        'icon' => 'fa-file-alt',
                        'title' => 'Mes Publications',
                        'desc' => implode(', ', $capabilities) . ' mes publications'
                    ];
                    $hasAnyPermission = true;
                }
                
                if ($hasAnyPermission) {
                    echo '<div class="admin-cards-grid">';
                    
                    foreach ($ownPermissions as $perm) {
                        echo '<a href="?page=admin&section=' . $perm['url'] . '" class="admin-card">';
                        echo '<i class="fas ' . $perm['icon'] . '"></i>';
                        echo '<h3>' . $perm['title'] . '</h3>';
                        echo '<p>' . $perm['desc'] . '</p>';
                        echo '</a>';
                    }
                    
                    echo '</div>';
                }
            }
            
            if (!$hasAnyPermission) {
                echo '<div class="no-permissions">';
                echo '<i class="fas fa-lock"></i>';
                echo '<h3>Aucune permission de gestion</h3>';
                echo '<p style="margin-bottom: 1rem;">Vous n\'avez pas encore accès aux fonctionnalités de gestion.</p>';
                echo '<p>Contactez un administrateur si vous pensez que c\'est une erreur.</p>';
                echo '</div>';
            }
            ?>

            <div class="dashboard-footer">
                <a href="?page=accueil" class="btn btn-secondary">Retour à l'accueil</a>
                <a href="?page=logout" class="btn btn-outline">Déconnexion</a>
            </div>
        </div>
    </div>
    <?php
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