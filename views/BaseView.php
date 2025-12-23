<?php
require_once __DIR__ . '/../config/constants.php';

class BaseView
{
    protected $pageTitle = 'Laboratoire Universitaire';
    protected $currentPage = '';

    /**
     * Afficher l'en-tête HTML
     */
    protected function renderHeader()
    {
        ?>
        <!DOCTYPE html>
        <html lang="fr">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= htmlspecialchars($this->pageTitle) ?></title>
            <link rel="stylesheet" href="<?= ASSETS_URL ?>css/style.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        </head>

        <body>
            <?php $this->renderNavigation(); ?>
            <?php
    }

    /**
     * Afficher la navigation
     */
    protected function renderNavigation()
    {
        $isAdmin = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
        $isLoggedIn = isset($_SESSION['user']);
        ?>
            <nav class="main-nav">
                <div class="container">
                    <div class="nav-wrapper">
                        <div class="logo">
                            <a href="?page=accueil">
                                <i class="fas fa-flask"></i> Lab Universitaire
                            </a>
                        </div>

                        <ul class="nav-menu">
                            <li><a href="?page=accueil"
                                    class="<?= $this->currentPage === 'accueil' ? 'active' : '' ?>">Accueil</a></li>
                            <li><a href="?page=projets"
                                    class="<?= $this->currentPage === 'projets' ? 'active' : '' ?>">Projets</a></li>
                            <li><a href="?page=publications"
                                    class="<?= $this->currentPage === 'publications' ? 'active' : '' ?>">Publications</a></li>
                            <li><a href="?page=equipements"
                                    class="<?= $this->currentPage === 'equipements' ? 'active' : '' ?>">Équipements</a></li>
                            <li><a href="?page=membres"
                                    class="<?= $this->currentPage === 'membres' ? 'active' : '' ?>">Membres</a></li>
                            <li><a href="?page=contact"
                                    class="<?= $this->currentPage === 'contact' ? 'active' : '' ?>">Contact</a></li>

                            <?php if ($isAdmin): ?>
                                <li><a href="?page=admin" class="admin-link"><i class="fas fa-cog"></i> Administration</a></li>
                            <?php endif; ?>

                            <?php if ($isLoggedIn): ?>
                                <li><a href="?page=profil"><i class="fas fa-user"></i> Profil</a></li>
                                <li><a href="?page=logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                            <?php else: ?>
                                <li><a href="?page=login" class="btn-login">Connexion</a></li>
                            <?php endif; ?>
                        </ul>

                        <div class="mobile-toggle">
                            <i class="fas fa-bars"></i>
                        </div>
                    </div>
                </div>
            </nav>
            <?php
    }

    /**
     * Afficher le pied de page
     */
    protected function renderFooter()
    {
        ?>
            <footer class="main-footer">
                <div class="container">
                    <div class="footer-content">
                        <div class="footer-section">
                            <h3>À propos</h3>
                            <p>Laboratoire de recherche en informatique de l'École Supérieure d'Informatique.</p>
                        </div>

                        <div class="footer-section">
                            <h3>Liens rapides</h3>
                            <ul>
                                <li><a href="?page=accueil">Accueil</a></li>
                                <li><a href="?page=projets">Projets</a></li>
                                <li><a href="?page=publications">Publications</a></li>
                                <li><a href="?page=membres">Membres</a></li>
                            </ul>
                        </div>

                        <div class="footer-section">
                            <h3>Contact</h3>
                            <p><i class="fas fa-map-marker-alt"></i> Alger, Algérie</p>
                            <p><i class="fas fa-phone"></i> +213 XXX XXX XXX</p>
                            <p><i class="fas fa-envelope"></i> contact@lab-esi.dz</p>
                        </div>

                        <div class="footer-section">
                            <div class="logo">
                                <a href="?page=accueil">
                                    <i class="fas fa-flask"></i> Lab
                                    Universitaire
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="footer-bottom">
                        <p>&copy; <?= date('Y') ?> Laboratoire Universitaire - École Supérieure d'Informatique. Tous droits
                            réservés.</p>
                    </div>
                </div>
            </footer>

            <script src="<?= ASSETS_URL ?>js/main.js"></script>
        </body>

        </html>
        <?php
    }

    /**
     * Afficher un message flash
     */
    protected function renderFlashMessage()
    {
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            $type = $_SESSION['flash_type'] ?? 'info';
            ?>
            <div class="flash-message flash-<?= $type ?>">
                <span><?= htmlspecialchars($message) ?></span>
                <button class="close-flash">&times;</button>
            </div>
            <?php
            unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        }
    }

    /**
     * Définir un message flash
     */
    public static function setFlash($message, $type = 'success')
    {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
}
?>