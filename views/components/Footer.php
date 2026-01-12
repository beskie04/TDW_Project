<?php



class Footer
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
            'about' => [
                'title' => 'À propos',
                'description' => ''
            ],
            'quick_links' => [
                'title' => 'Liens rapides',
                'links' => []
            ],
            'contact' => [
                'title' => 'Contact',
                'address' => '',
                'phone' => '',
                'email' => ''
            ],
            'logo' => [
                'text' => 'Mon Site',
                'icon' => 'fas fa-home',
                'url' => '?page=accueil'
            ],
            'copyright' => [
                'text' => 'Tous droits réservés.',
                'show_year' => true
            ],
            'social_links' => [], // Optionnel: liens réseaux sociaux
            'custom_sections' => [] // Sections personnalisées supplémentaires
        ];
    }

    /**
     * Rendu du footer
     */
    public function render(): void
    {
        ?>
        <footer class="main-footer">
            <div class="container">
                <div class="footer-content">
                    <?php $this->renderAboutSection(); ?>
                    <?php $this->renderQuickLinksSection(); ?>
                    <?php $this->renderContactSection(); ?>
                    <?php $this->renderCustomSections(); ?>
                    <?php $this->renderLogoSection(); ?>
                    <?php $this->renderSocialSection(); ?>
                </div>

                <?php $this->renderCopyright(); ?>
            </div>
        </footer>

        <script src="<?= ASSETS_URL ?>js/main.js"></script>
        </body>
        </html>
        <?php
    }

    /**
     * Section À propos
     */
    private function renderAboutSection(): void
    {
        if (empty($this->config['about']['description'])) {
            return;
        }
        ?>
        <div class="footer-section">
            <h3><?= htmlspecialchars($this->config['about']['title']) ?></h3>
            <p><?= htmlspecialchars($this->config['about']['description']) ?></p>
        </div>
        <?php
    }

    /**
     * Section Liens rapides
     */
    private function renderQuickLinksSection(): void
    {
        if (empty($this->config['quick_links']['links'])) {
            return;
        }
        ?>
        <div class="footer-section">
            <h3><?= htmlspecialchars($this->config['quick_links']['title']) ?></h3>
            <ul>
                <?php foreach ($this->config['quick_links']['links'] as $link): ?>
                    <li>
                        <a href="<?= htmlspecialchars($link['url']) ?>">
                            <?= htmlspecialchars($link['text']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }

    /**
     * Section Contact
     */
    private function renderContactSection(): void
    {
        $contact = $this->config['contact'];
        if (empty($contact['address']) && empty($contact['phone']) && empty($contact['email'])) {
            return;
        }
        ?>
        <div class="footer-section">
            <h3><?= htmlspecialchars($contact['title']) ?></h3>
            <?php if (!empty($contact['address'])): ?>
                <p>
                    <i class="fas fa-map-marker-alt"></i>
                    <?= htmlspecialchars($contact['address']) ?>
                </p>
            <?php endif; ?>
            <?php if (!empty($contact['phone'])): ?>
                <p>
                    <i class="fas fa-phone"></i>
                    <?= htmlspecialchars($contact['phone']) ?>
                </p>
            <?php endif; ?>
            <?php if (!empty($contact['email'])): ?>
                <p>
                    <i class="fas fa-envelope"></i>
                    <a href="mailto:<?= htmlspecialchars($contact['email']) ?>">
                        <?= htmlspecialchars($contact['email']) ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Sections personnalisées
     */
    private function renderCustomSections(): void
    {
        if (empty($this->config['custom_sections'])) {
            return;
        }

        foreach ($this->config['custom_sections'] as $section) {
            ?>
            <div class="footer-section">
                <?php if (!empty($section['title'])): ?>
                    <h3><?= htmlspecialchars($section['title']) ?></h3>
                <?php endif; ?>
                <?= $section['content'] ?>
            </div>
            <?php
        }
    }

    /**
     * Section Logo
     */
    private function renderLogoSection(): void
    {
        $logo = $this->config['logo'];
        ?>
        <div class="footer-section">
            <div class="logo">
                <a href="<?= htmlspecialchars($logo['url']) ?>">
                    <i class="<?= htmlspecialchars($logo['icon']) ?>"></i>
                    <?= htmlspecialchars($logo['text']) ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Section Réseaux sociaux (optionnel)
     */
    private function renderSocialSection(): void
    {
        if (empty($this->config['social_links'])) {
            return;
        }
        ?>
        <div class="footer-section">
            <h3>Suivez-nous</h3>
            <div class="social-links">
                <?php foreach ($this->config['social_links'] as $social): ?>
                    <a href="<?= htmlspecialchars($social['url']) ?>"
                       target="_blank"
                       rel="noopener noreferrer"
                       title="<?= htmlspecialchars($social['name']) ?>">
                        <i class="<?= htmlspecialchars($social['icon']) ?>"></i>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Copyright
     */
    private function renderCopyright(): void
    {
        $copyright = $this->config['copyright'];
        ?>
        <div class="footer-bottom">
            <p>
                &copy;
                <?php if ($copyright['show_year']): ?>
                    <?= date('Y') ?>
                <?php endif; ?>
                <?= htmlspecialchars($copyright['text']) ?>
            </p>
        </div>
        <?php
    }
}