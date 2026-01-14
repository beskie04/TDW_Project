<?php

class TopBar
{
    private static bool $stylesRendered = false;
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
            'logo' => [
                'icon' => 'fas fa-flask',
                'text' => 'Lab Universitaire',
                'url' => '?page=accueil'
            ],
            'university' => [
                'name' => 'Laboratoire Universitaire',
                'url' => 'https://www.esi.dz',
                'icon' => 'fas fa-university'
            ],
            'social_links' => [
                'facebook' => null,
                'twitter' => null,
                'linkedin' => null,
                'youtube' => null
            ],
            'contact' => [
                'address' => null,
                'phone' => null,
                'email' => null
            ]
        ];
    }

    /**
     * Render avec configuration
     */
    public function render(): void
    {
        if (!self::$stylesRendered) {
            $this->renderStyles();
            self::$stylesRendered = true;
        }

        ?>
        <div class="top-bar">
            <div class="container">
                <div class="top-bar-wrapper">
                    <!-- Logo à gauche -->
                    <div class="top-bar-logo">
                        <a href="<?= htmlspecialchars($this->config['logo']['url']) ?>">
                            <i class="<?= htmlspecialchars($this->config['logo']['icon']) ?>"></i>
                            <span><?= htmlspecialchars($this->config['logo']['text']) ?></span>
                        </a>
                    </div>

                    <!-- Liens sociaux et site université à droite -->
                    <div class="top-bar-links">
                        <!-- Site de l'université -->
                        <?php if (!empty($this->config['university']['url'])): ?>
                            <a href="<?= htmlspecialchars($this->config['university']['url']) ?>" 
                               target="_blank" 
                               class="university-link" 
                               title="Site officiel de <?= htmlspecialchars($this->config['university']['name']) ?>">
                                <i class="<?= htmlspecialchars($this->config['university']['icon']) ?>"></i>
                                <span><?= htmlspecialchars($this->config['university']['name']) ?></span>
                            </a>
                        <?php endif; ?>

                        <!-- Réseaux sociaux -->
                        <div class="social-links">
                            <?php if (!empty($this->config['social_links']['facebook'])): ?>
                                <a href="<?= htmlspecialchars($this->config['social_links']['facebook']) ?>" 
                                   target="_blank" 
                                   title="Facebook">
                                    <i class="fab fa-facebook"></i>
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($this->config['social_links']['twitter'])): ?>
                                <a href="<?= htmlspecialchars($this->config['social_links']['twitter']) ?>" 
                                   target="_blank" 
                                   title="Twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($this->config['social_links']['linkedin'])): ?>
                                <a href="<?= htmlspecialchars($this->config['social_links']['linkedin']) ?>" 
                                   target="_blank" 
                                   title="LinkedIn">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($this->config['social_links']['youtube'])): ?>
                                <a href="<?= htmlspecialchars($this->config['social_links']['youtube']) ?>" 
                                   target="_blank" 
                                   title="YouTube">
                                    <i class="fab fa-youtube"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render statique avec données de la DB
     */
    public static function renderFromDatabase(): void
    {
        require_once __DIR__ . '/../../models/SettingsModel.php';
        
        $settingsModel = new SettingsModel();
        $settings = $settingsModel->getAllSettings();
        
        // Construire la config depuis les settings
        $config = [
            'logo' => [
                'icon' => 'fas fa-flask',
                'text' => 'Lab Universitaire',
                'url' => '?page=accueil'
            ],
            'university' => [
                'name' => 'Laboratoire Universitaire',
                'url' => 'https://www.esi.dz',
                'icon' => 'fas fa-university'
            ],
            'social_links' => [
                'facebook' => $settings['reseaux_facebook'] ?? null,
                'twitter' => $settings['reseaux_twitter'] ?? null,
                'linkedin' => $settings['reseaux_linkedin'] ?? null,
               
            ],
            'contact' => [
                'address' => $settings['contact_adresse'] ?? null,
                'phone' => $settings['contact_telephone'] ?? null,
                'email' => $settings['contact_email'] ?? null,
                'fax' => $settings['contact_fax'] ?? null
            ]
        ];
        
        // Vérifier si logo_universite existe dans les settings
        if (!empty($settings['logo_universite'])) {
            $config['logo']['image'] = $settings['logo_universite'];
        }
        
        $topBar = new self($config);
        $topBar->render();
    }

    private function renderStyles(): void
    {
        ?>
        <style>
        .top-bar {
            background: #f8f9fa;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.75rem 0;
            font-size: 0.9rem;
        }

        .top-bar-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-bar-logo a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: #1e40af;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .top-bar-logo i {
            font-size: 1.5rem;
        }

        .top-bar-links {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .university-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: #4b5563;
            font-weight: 600;
            padding: 0.5rem 1rem;
            background: white;
            border-radius: 6px;
            transition: all 0.2s;
            border: 1px solid #e5e7eb;
        }

        .university-link:hover {
            color: #1e40af;
            border-color: #1e40af;
            background: #eff6ff;
        }

        .university-link i {
            font-size: 1.1rem;
        }

        .social-links {
            display: flex;
            gap: 0.75rem;
        }

        .social-links a {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #6b7280;
            background: white;
            border-radius: 50%;
            transition: all 0.2s;
            border: 1px solid #e5e7eb;
        }

        .social-links a:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .social-links a[title="Facebook"]:hover {
            color: #1877f2;
            border-color: #1877f2;
            background: #eff6ff;
        }

        .social-links a[title="Twitter"]:hover {
            color: #1da1f2;
            border-color: #1da1f2;
            background: #eff6ff;
        }

        .social-links a[title="LinkedIn"]:hover {
            color: #0a66c2;
            border-color: #0a66c2;
            background: #eff6ff;
        }

        .social-links a[title="YouTube"]:hover {
            color: #ff0000;
            border-color: #ff0000;
            background: #fef2f2;
        }

        @media (max-width: 768px) {
            .top-bar-wrapper {
                flex-direction: column;
                gap: 1rem;
            }

            .top-bar-logo {
                width: 100%;
                text-align: center;
            }

            .top-bar-links {
                width: 100%;
                justify-content: center;
            }

            .university-link span {
                display: none;
            }
        }
        </style>
        <?php
    }
}