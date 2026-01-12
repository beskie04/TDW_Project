<?php

class Navigation
{
    private array $config;
    private ?array $user;

    public function __construct(array $config = [], ?array $user = null)
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->user = $user;
    }

    /**
     * Configuration par défaut
     */
    private function getDefaultConfig(): array
    {
        return [
            'logo' => [
                'text' => 'Mon Site',
                'icon' => 'fas fa-home',
                'url' => '/'
            ],
            'menu_items' => [],
            'user_menu' => [
                'login' => [
                    'text' => 'Connexion',
                    'url' => '?page=login',
                    'icon' => null,
                    'show_when' => 'guest' // 'guest', 'authenticated', 'always'
                ],
                'profile' => [
                    'text' => 'Profil',
                    'url' => '?page=profil',
                    'icon' => 'fas fa-user',
                    'show_when' => 'authenticated'
                ],
                'logout' => [
                    'text' => 'Déconnexion',
                    'url' => '?page=logout',
                    'icon' => 'fas fa-sign-out-alt',
                    'show_when' => 'authenticated'
                ]
            ],
            'conditional_items' => [], // Items based on custom conditions
            'mobile_enabled' => true,
            'container_class' => 'container',
            'current_page' => '' // For active state
        ];
    }

    /**
     * Rendu de la navigation
     */
    public function render(): void
    {
        $isAuthenticated = !empty($this->user);
        ?>
        <nav class="main-nav">
            <div class="<?= htmlspecialchars($this->config['container_class']) ?>">
                <div class="nav-wrapper">
                    <?php $this->renderLogo(); ?>
                    <?php $this->renderMenu($isAuthenticated); ?>
                    
                    <!-- NOTIFICATION BELL - NOUVEAU -->
                    <?php if ($isAuthenticated): ?>
                        <?php $this->renderNotificationBell(); ?>
                    <?php endif; ?>
                    
                    <?php if ($this->config['mobile_enabled']): ?>
                        <?php $this->renderMobileToggle(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
        <?php
    }

    /**
     * Logo
     */
    private function renderLogo(): void
    {
        $logo = $this->config['logo'];
        ?>
        <div class="logo">
            <a href="<?= htmlspecialchars($logo['url']) ?>">
                <?php if (!empty($logo['icon'])): ?>
                    <i class="<?= htmlspecialchars($logo['icon']) ?>"></i>
                <?php endif; ?>
                <?= htmlspecialchars($logo['text']) ?>
            </a>
        </div>
        <?php
    }

    /**
     * Menu principal
     */
    private function renderMenu(bool $isAuthenticated): void
    {
        ?>
        <ul class="nav-menu">
            <?php
            // Menu items standard
            foreach ($this->config['menu_items'] as $item) {
                if ($this->shouldShowItem($item, $isAuthenticated)) {
                    $this->renderMenuItem($item);
                }
            }

            // Items conditionnels (basés sur des callbacks)
            foreach ($this->config['conditional_items'] as $item) {
                if ($this->evaluateCondition($item)) {
                    $this->renderMenuItem($item);
                }
            }

            // Menu utilisateur
            foreach ($this->config['user_menu'] as $item) {
                if ($this->shouldShowUserMenuItem($item, $isAuthenticated)) {
                    $this->renderMenuItem($item);
                }
            }
            ?>
        </ul>
        <?php
    }

    /**
     * NOUVEAU: Rendu de la cloche de notification
     */
    private function renderNotificationBell(): void
    {
        // Charger le composant NotificationBell
        $bellPath = __DIR__ . '/NotificationBell.php';
        if (file_exists($bellPath)) {
            require_once $bellPath;
            NotificationBell::render();
        }
    }

    /**
     * Rendu d'un item de menu
     */
    private function renderMenuItem(array $item): void
    {
        $isActive = $this->isActive($item);
        $activeClass = $isActive ? 'active' : '';
        $customClass = $item['class'] ?? '';
        $classes = trim("$activeClass $customClass");
        ?>
        <li>
            <a href="<?= htmlspecialchars($item['url']) ?>"
               <?php if (!empty($classes)): ?>class="<?= htmlspecialchars($classes) ?>"<?php endif; ?>>
                <?php if (!empty($item['icon'])): ?>
                    <i class="<?= htmlspecialchars($item['icon']) ?>"></i>
                <?php endif; ?>
                <?= htmlspecialchars($item['text']) ?>
            </a>
        </li>
        <?php
    }

    /**
     * Mobile toggle
     */
    private function renderMobileToggle(): void
    {
        ?>
        <div class="mobile-toggle">
            <i class="fas fa-bars"></i>
        </div>
        <?php
    }

    /**
     * Vérifie si un item doit être affiché
     */
    private function shouldShowItem(array $item, bool $isAuthenticated): bool
    {
        $showWhen = $item['show_when'] ?? 'always';

        switch ($showWhen) {
            case 'guest':
                return !$isAuthenticated;
            case 'authenticated':
                return $isAuthenticated;
            case 'always':
            default:
                return true;
        }
    }

    /**
     * Vérifie si un item du menu utilisateur doit être affiché
     */
    private function shouldShowUserMenuItem(array $item, bool $isAuthenticated): bool
    {
        return $this->shouldShowItem($item, $isAuthenticated);
    }

    /**
     * Évalue une condition personnalisée
     */
    private function evaluateCondition(array $item): bool
    {
        // Si l'item a une callback de condition
        if (isset($item['condition']) && is_callable($item['condition'])) {
            return $item['condition']($this->user);
        }

        // Sinon, utiliser show_when standard
        return $this->shouldShowItem($item, !empty($this->user));
    }

    /**
     * Détermine si un item est actif
     */
    private function isActive(array $item): bool
    {
        $currentPage = $this->config['current_page'];
        
        if (empty($currentPage)) {
            return false;
        }

        // Méthode 1: Comparaison directe avec 'page'
        if (isset($item['page']) && $item['page'] === $currentPage) {
            return true;
        }

        // Méthode 2: Extraction depuis l'URL
        if (isset($item['url'])) {
            $pattern = '/[?&]page=([^&]+)/';
            if (preg_match($pattern, $item['url'], $matches)) {
                return $matches[1] === $currentPage;
            }
        }

        return false;
    }
}