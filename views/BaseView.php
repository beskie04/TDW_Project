<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../utils/PermissionHelper.php';
require_once __DIR__ . '/components/Footer.php';
require_once __DIR__ . '/components/Navigation.php';
require_once __DIR__ . '/components/FlashMessage.php';
require_once __DIR__ . '/components/HtmlHead.php';

class BaseView
{
    protected $pageTitle = 'Laboratoire Universitaire';
    protected $currentPage = '';
    protected string $title = '';
    protected array $styles = [];
    protected array $scripts = [];
    protected ?Footer $footer = null;
    protected ?Navigation $navigation = null;
    protected ?HtmlHead $htmlHead = null;

    public function __construct()
    {
        // Footer
        $footerConfig = require __DIR__ . '/config/footer.php';
        $this->footer = new Footer($footerConfig);

        // Navigation
        $navConfig = require __DIR__ . '/config/navigation.php';
        $navConfig['current_page'] = $this->currentPage;
        $user = $_SESSION['user'] ?? null;
        $this->navigation = new Navigation($navConfig, $user);
        

        // HTML Head

        $headConfig = require  __DIR__ . '/config/htmlhead.php';
        $this->htmlHead = new HtmlHead($headConfig);
    }

    /**
     * Afficher l'en-tête HTML
     */
    protected function renderHeader()
    {
        // Mettre à jour le titre avant le rendu
        $headConfig = require __DIR__  . '/config/htmlhead.php';
        $headConfig['title'] = $this->pageTitle;
        $this->htmlHead = new HtmlHead($headConfig);
        
        $this->htmlHead->render();
         $this->renderTopBar();
        $this->renderNavigation();
    }
/**
 *  Afficher la barre supérieure 
 */
protected function renderTopBar()
{
    require_once __DIR__ . '/components/TopBar.php';
    TopBar::renderFromDatabase();
}

    /**
     * Afficher la navigation
     */
    protected function renderNavigation()
    {
        $navConfig = require __DIR__  . '/config/navigation.php';
        $navConfig['current_page'] = $this->currentPage;
        $user = $_SESSION['user'] ?? null;
        
        $nav = new Navigation($navConfig, $user);
        $nav->render();
    }

    /**
     * Afficher un message flash
     */
    protected function renderFlashMessage()
    {
        FlashMessage::render([
            'show_icon' => true,
            'show_close' => true,
            'auto_close' => true,
            'auto_close_delay' => 5000
        ]);
    }

    /**
     * Définir un message flash
     */
    public static function setFlash($message, $type = 'success')
    {
        FlashMessage::set($message, $type);
    }

    /**
     * Afficher le pied de page
     */
    protected function renderFooter(): void
    {
        $this->footer->render();
    }
}