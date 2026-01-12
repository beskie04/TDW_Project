<?php

class HtmlHead
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
    }

    /**
     * Configuration par défaut
     */
    private function getDefaultConfig(): array
    {
        return [
            'title' => 'Mon Site',
            'charset' => 'UTF-8',
            'viewport' => 'width=device-width, initial-scale=1.0',
            'description' => '',
            'keywords' => '',
            'author' => '',
            'favicon' => null,
            'stylesheets' => [],
            'scripts' => [],
            'meta_tags' => [],
            'open_graph' => [], // Pour réseaux sociaux
            'preload' => [], // Ressources à précharger
            'lang' => 'fr'
        ];
    }

    /**
     * Rendu du head complet
     */
    public function render(): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="<?= htmlspecialchars($this->config['lang']) ?>">
        <head>
            <?php $this->renderMetaTags(); ?>
            <?php $this->renderTitle(); ?>
            <?php $this->renderFavicon(); ?>
            <?php $this->renderPreload(); ?>
            <?php $this->renderStylesheets(); ?>
            <?php $this->renderScripts('head'); ?>
            <?php $this->renderOpenGraph(); ?>
        </head>
        <body>
        <?php
    }

    /**
     * Meta tags de base
     */
    private function renderMetaTags(): void
    {
        ?>
        <meta charset="<?= htmlspecialchars($this->config['charset']) ?>">
        <meta name="viewport" content="<?= htmlspecialchars($this->config['viewport']) ?>">
        
        <?php if (!empty($this->config['description'])): ?>
            <meta name="description" content="<?= htmlspecialchars($this->config['description']) ?>">
        <?php endif; ?>
        
        <?php if (!empty($this->config['keywords'])): ?>
            <meta name="keywords" content="<?= htmlspecialchars($this->config['keywords']) ?>">
        <?php endif; ?>
        
        <?php if (!empty($this->config['author'])): ?>
            <meta name="author" content="<?= htmlspecialchars($this->config['author']) ?>">
        <?php endif; ?>

        <?php
        // Meta tags personnalisés
        foreach ($this->config['meta_tags'] as $name => $content) {
            ?>
            <meta name="<?= htmlspecialchars($name) ?>" content="<?= htmlspecialchars($content) ?>">
            <?php
        }
    }

    /**
     * Title
     */
    private function renderTitle(): void
    {
        ?>
        <title><?= htmlspecialchars($this->config['title']) ?></title>
        <?php
    }

    /**
     * Favicon
     */
    private function renderFavicon(): void
    {
        if (!empty($this->config['favicon'])) {
            ?>
            <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($this->config['favicon']) ?>">
            <?php
        }
    }

    /**
     * Préchargement de ressources
     */
    private function renderPreload(): void
    {
        foreach ($this->config['preload'] as $resource) {
            $href = $resource['href'] ?? '';
            $as = $resource['as'] ?? 'style';
            $type = $resource['type'] ?? '';
            
            if (empty($href)) {
                continue;
            }
            ?>
            <link rel="preload" 
                  href="<?= htmlspecialchars($href) ?>" 
                  as="<?= htmlspecialchars($as) ?>"
                  <?php if (!empty($type)): ?>
                  type="<?= htmlspecialchars($type) ?>"
                  <?php endif; ?>>
            <?php
        }
    }

    /**
     * Stylesheets
     */
    private function renderStylesheets(): void
    {
        foreach ($this->config['stylesheets'] as $stylesheet) {
            if (is_string($stylesheet)) {
                $href = $stylesheet;
                $media = 'all';
            } else {
                $href = $stylesheet['href'] ?? '';
                $media = $stylesheet['media'] ?? 'all';
            }

            if (empty($href)) {
                continue;
            }
            ?>
            <link rel="stylesheet" 
                  href="<?= htmlspecialchars($href) ?>" 
                  media="<?= htmlspecialchars($media) ?>">
            <?php
        }
    }

    /**
     * Scripts
     */
    private function renderScripts(string $position = 'head'): void
    {
        foreach ($this->config['scripts'] as $script) {
            if (is_string($script)) {
                $src = $script;
                $scriptPosition = 'head';
                $defer = false;
                $async = false;
            } else {
                $src = $script['src'] ?? '';
                $scriptPosition = $script['position'] ?? 'head';
                $defer = $script['defer'] ?? false;
                $async = $script['async'] ?? false;
            }

            if (empty($src) || $scriptPosition !== $position) {
                continue;
            }
            ?>
            <script src="<?= htmlspecialchars($src) ?>"
                <?php if ($defer): ?>defer<?php endif; ?>
                <?php if ($async): ?>async<?php endif; ?>></script>
            <?php
        }
    }

    /**
     * Open Graph (réseaux sociaux)
     */
    private function renderOpenGraph(): void
    {
        if (empty($this->config['open_graph'])) {
            return;
        }

        foreach ($this->config['open_graph'] as $property => $content) {
            ?>
            <meta property="og:<?= htmlspecialchars($property) ?>" 
                  content="<?= htmlspecialchars($content) ?>">
            <?php
        }
    }

    /**
     * Fermer le body (pour les scripts en fin de page)
     */
    public function closeBody(): void
    {
        $this->renderScripts('body');
        ?>
        </body>
        </html>
        <?php
    }
}