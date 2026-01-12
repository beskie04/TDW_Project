<?php
require_once __DIR__ . '/BaseView.php';

require_once __DIR__ . '/components/Slider.php';
require_once __DIR__ . '/components/Section.php';
require_once __DIR__ . '/components/Card.php';
require_once __DIR__ . '/components/Grid.php';
require_once __DIR__ . '/components/TextBlock.php';
require_once __DIR__ . '/components/Badge.php';
require_once __DIR__ . '/components/Avatar.php';

class AccueilView extends BaseView
{ 
  
    public function __construct()
    {
        parent::__construct();
        $this->currentPage = 'accueil';
        $this->pageTitle = 'Accueil - Laboratoire Universitaire';
    }

   public function render($actualitesSlider, $actualites, $organigramme, $evenements, $currentPage, $totalPages, $partenaires)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main>
            <!-- Hero Slider -->
            <?php
            // ⭐ FIX: Vérifier que $actualitesSlider est bien un tableau
            if (!is_array($actualitesSlider)) {
                $actualitesSlider = [];
            }

            // ⭐ FIX: Adapter les slides selon les données reçues
            $slides = array_map(function ($actu) {
                // Déterminer le chemin de l'image selon le type
                $imagePath = null;
                if (!empty($actu['image'])) {
                    // Si c'est un événement
                    if (isset($actu['type']) && $actu['type'] === 'evenement') {
                        $imagePath = UPLOADS_URL . 'evenements/' . $actu['image'];
                    } else {
                        $imagePath = UPLOADS_URL . 'actualites/' . $actu['image'];
                    }
                }

                return [
                    'image' => $imagePath,
                    'badge' => isset($actu['type']) ? ucfirst($actu['type']) : 'Actualité',
                    'title' => $actu['titre'] ?? 'Sans titre',
                    'description' => $actu['description'] ?? '',
                    'link' => !empty($actu['lien']) ? ['url' => $actu['lien'], 'text' => 'En savoir plus'] : null
                ];
            }, $actualitesSlider);

            // ⭐ Si pas de slides, afficher un message
            if (empty($slides)) {
                echo '<div style="background: linear-gradient(135deg, var(--primary-color), var(--accent-color)); 
                             padding: 4rem 2rem; text-align: center; color: white;">
                        <h1 style="margin: 0 0 1rem 0; font-size: 2.5rem;">Bienvenue au Laboratoire</h1>
                        <p style="font-size: 1.25rem; margin: 0;">Centre d\'Excellence en Recherche Informatique</p>
                      </div>';
            } else {
                Slider::render($slides, [
                    'height' => '600px',
                    'autoplayDelay' => 5000
                ]);
            }
            ?>

            <div class="content-wrapper">
                <div class="container">

                    <!-- SECTION 1: Actualités Scientifiques -->
                    <?php $this->renderActualitesSection($actualites ?? []); ?>

                    <!-- SECTION 2: Présentation et Organigramme -->
                    <?php $this->renderPresentationSection($organigramme ?? []); ?>

                    <!-- SECTION 3: Événements à venir -->
                    <?php $this->renderEvenementsSection($evenements ?? [], $currentPage, $totalPages); ?>

                    <!-- SECTION 4: Partenaires -->
                    <?php $this->renderPartenairesSection($partenaires ?? []); ?>

                </div>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

/**
     * SECTION 1: Actualités scientifiques (VERSION CORRIGÉE)
     */
    private function renderActualitesSection($actualites)
    {
        Section::render([
            'title' => 'Actualités Scientifiques',
            'icon' => 'fas fa-newspaper',
            'cssClass' => 'home-section'
        ], function () use ($actualites) {
            if (empty($actualites) || !is_array($actualites)) {
                echo '<p style="text-align: center; color: var(--gray-600); padding: 2rem;">Aucune actualité pour le moment</p>';
                return;
            }

            Grid::render(['minWidth' => '350px', 'gap' => '1.5rem'], function () use ($actualites) {
                foreach ($actualites as $actu) {
                    // ⭐ Sécurité: vérifier que $actu est bien un tableau
                    if (!is_array($actu)) continue;
                    
                    // ⭐ Déterminer le lien
                    $link = $this->getLinkForActualite($actu);
                    $badgeColor = $this->getBadgeColorForType($actu['type'] ?? 'autre');
                    
                    // Date de publication
                    $dateStr = isset($actu['date_publication']) 
                        ? date('d/m/Y', strtotime($actu['date_publication'])) 
                        : 'Date inconnue';
                    
                    Card::render([
                        'image' => !empty($actu['image']) ? UPLOADS_URL . 'evenements/' . $actu['image'] : null,
                        'imageHeight' => '200px',
                        'badge' => ucfirst($actu['type'] ?? 'Actualité'),
                        'badgeColor' => $badgeColor,
                        'title' => $actu['titre'] ?? 'Sans titre',
                        'description' => isset($actu['description']) ? mb_substr($actu['description'], 0, 120) . '...' : '',
                        'footer' => [
                            ['icon' => 'fas fa-calendar', 'text' => $dateStr]
                        ],
                        'link' => $link ? [
                            'url' => $link,
                            'text' => 'Lire la suite',
                            'icon' => 'fas fa-arrow-right'
                        ] : null
                    ]);
                }
            });
        });
    }

    /**
     * Helper: Récupérer le lien correct selon le type
     */
    private function getLinkForActualite($actu)
    {
        if (!is_array($actu)) return null;

        // Si un lien est déjà fourni
        if (!empty($actu['lien']) && $actu['lien'] !== '#') {
            return $actu['lien'];
        }

        // Construire le lien selon le type
        $type = strtolower($actu['type'] ?? '');
        $id = $actu['id_entite'] ?? null;

        if (!$id) return null;

        switch ($type) {
            case 'projet':
                return "?page=projets&action=details&id={$id}";
            
            case 'publication':
                return "?page=publications&action=details&id={$id}";
            
            case 'evenement':
                return "?page=evenements&action=details&id={$id}";
            
            case 'annonce':
                return "#";
            
            default:
                return null;
        }
    }

    /**
     * Helper: Couleur du badge selon le type
     */
    private function getBadgeColorForType($type)
    {
        $colors = [
            'projet' => '#3b82f6',
            'publication' => '#10b981',
            'evenement' => '#f59e0b',
            'annonce' => '#ef4444',
            'collaboration' => '#8b5cf6',
            'soutenance' => '#ec4899'
        ];

        return $colors[strtolower($type)] ?? '#6b7280';
    }
   private function renderPresentationSection($organigramme)
{
    Section::render([
        'title' => 'À propos du Laboratoire',
        'icon' => 'fas fa-info-circle',
        'cssClass' => 'home-section'
    ], function () use ($organigramme) {
        ?>
        <!-- Présentation du laboratoire -->
        <div style="background: <?= BG_WHITE ?>; 
                    padding: 2rem; 
                    border-radius: 12px; 
                    box-shadow: 0 1px 3px rgba(0,0,0,0.1); 
                    margin-bottom: 2rem;">
            <p style="line-height: 1.8; 
                      color: <?= TEXT_DARK ?>; 
                      margin: 0 0 1rem 0; 
                      font-size: 1.05rem;">
                Le <strong>Laboratoire de Recherche en Informatique</strong> de l'École Supérieure d'Informatique
                est un centre d'excellence dédié à l'innovation et à la recherche de pointe dans divers domaines de
                l'informatique. Nos équipes travaillent sur des problématiques actuelles telles que l'intelligence
                artificielle, la cybersécurité, le cloud computing et les systèmes embarqués.
            </p>
            <p style="line-height: 1.8; 
                      color: <?= TEXT_DARK ?>; 
                      margin: 0; 
                      font-size: 1.05rem;">
                Fort d'une équipe de chercheurs expérimentés et de doctorants talentueux, le laboratoire collabore
                avec des partenaires académiques et industriels nationaux et internationaux.
            </p>
        </div>

        <!-- Organigramme -->
        <div style="margin-top: 2rem;">
            <h3 style="display: flex; 
                       align-items: center; 
                       gap: 0.5rem; 
                       margin: 0 0 1.5rem 0; 
                       color: <?= TEXT_DARK ?>; 
                       font-size: 1.5rem;">
                <i class="fas fa-sitemap"></i>
                Organigramme
            </h3>

            <?php if (empty($organigramme)): ?>
                <p style="text-align: center; 
                          color: <?= TEXT_GRAY ?>; 
                          padding: 2rem;">
                    Organigramme à venir
                </p>
            <?php else: ?>
                <div style="display: grid; 
                            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
                            gap: 1.5rem;">
                    <?php foreach ($organigramme as $membre): ?>
                        <div style="background: <?= BG_WHITE ?>; 
                                    padding: 1.5rem; 
                                    border-radius: 12px; 
                                    box-shadow: 0 1px 3px rgba(0,0,0,0.1); 
                                    border-left: 4px solid <?= $this->getPosteColor($membre['poste']) ?>;">
                            <div style="display: flex; 
                                        gap: 1rem; 
                                        align-items: center;">
                                <?php
                                $photoUrl = !empty($membre['photo']) ? UPLOADS_URL . 'photos/' . $membre['photo'] : null;
                                Avatar::render([
                                    'src' => $photoUrl,
                                    'alt' => $membre['nom'] . ' ' . $membre['prenom'],
                                    'size' => 'large'
                                ]);
                                ?>
                                <div style="flex: 1;">
                                    <p style="margin: 0 0 0.25rem 0; 
                                              font-size: 0.875rem; 
                                              color: <?= PRIMARY_COLOR ?>; 
                                              font-weight: 600;">
                                        <?= htmlspecialchars($membre['poste']) ?>
                                    </p>
                                    <h4 style="margin: 0 0 0.25rem 0; 
                                               color: <?= TEXT_DARK ?>; 
                                               font-size: 1.1rem;">
                                        <?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?>
                                    </h4>
                                    <p style="margin: 0; 
                                              color: <?= TEXT_GRAY ?>; 
                                              font-size: 0.875rem;">
                                        <?= htmlspecialchars($membre['grade']) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    });
}

    /**
     * SECTION 3: Événements à venir avec pagination
     */
    private function renderEvenementsSection($evenements, $currentPage, $totalPages)
    {
        Section::render([
            'title' => 'Événements à venir',
            'icon' => 'fas fa-calendar-alt',
            'link' => ['url' => '?page=evenements', 'text' => 'Tous les événements', 'icon' => 'fas fa-arrow-right'],
            'cssClass' => 'home-section'
        ], function () use ($evenements, $currentPage, $totalPages) {
            if (empty($evenements)) {
                echo '<p style="text-align: center; color: var(--gray-600); padding: 2rem;">Aucun événement prévu pour le moment</p>';
                return;
            }

            // Cartes d'événements
            Grid::render(['minWidth' => '350px', 'gap' => '1.5rem'], function () use ($evenements) {
                foreach ($evenements as $event) {
                    $dateDebut = date('d/m/Y', strtotime($event['date_debut']));
                    $dateFin = $event['date_fin'] ? date('d/m/Y', strtotime($event['date_fin'])) : null;

                    Card::render([
                        'image' => !empty($event['image']) ? UPLOADS_URL . 'evenements/' . $event['image'] : null,
                        'imageHeight' => '180px',
                        'badge' => ucfirst($event['id_type_evenement']),
                        'badgeColor' => 'var(--accent-color)',
                        'title' => $event['titre'],
                        'description' => mb_substr($event['description'], 0, 100) . '...',
                        'footer' => [
                            [
                                'icon' => 'fas fa-calendar',
                                'text' => $dateFin ? "$dateDebut - $dateFin" : $dateDebut
                            ],
                            [
                                'icon' => 'fas fa-map-marker-alt',
                                'text' => $event['lieu'] ?? 'À définir'
                            ]
                        ],
                        'link' => [
                            'url' => '?page=evenements&action=details&id=' . $event['id_evenement'],
                            'text' => 'Plus d\'infos',
                            'icon' => 'fas fa-arrow-right'
                        ]
                    ]);
                }
            });

            // Pagination
            if ($totalPages > 1) {
                $this->renderPagination($currentPage, $totalPages);
            }
        });
    }

    /**
     * SECTION 4: Partenaires institutionnels et industriels
     */
    private function renderPartenairesSection($partenaires)
    {
        Section::render([
            'title' => 'Nos Partenaires',
            'icon' => 'fas fa-handshake',
            'cssClass' => 'home-section partenaires-section'
        ], function () use ($partenaires) {
            if (empty($partenaires)) {
                echo '<p style="text-align: center; color: var(--gray-600); padding: 2rem;">Aucun partenaire pour le moment</p>';
                return;
            }

            // Grouper par type
            $partenairesGroupes = [];
            foreach ($partenaires as $p) {
                $type = $p['type_partenaire'] ?? 'Autre';
                if (!isset($partenairesGroupes[$type])) {
                    $partenairesGroupes[$type] = [];
                }
                $partenairesGroupes[$type][] = $p;
            }

            foreach ($partenairesGroupes as $type => $partenairesList) {
                ?>
                <div style="margin-bottom: 2rem;">
                    <h3
                        style="margin: 0 0 1rem 0; color: var(--dark-color); font-size: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="<?= $this->getPartenaireIcon($type) ?>"></i>
                        <?= ucfirst($type) ?>s
                        
                    </h3>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                        <?php foreach ($partenairesList as $part): ?>
                            <div
                                style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); text-align: center;">
                                <?php if (!empty($part['logo'])): ?>
                                    <div
                                        style="display: flex; justify-content: center; align-items: center; height: 80px; margin-bottom: 1rem;">
                                        <img src="<?= UPLOADS_URL . 'logos/' . htmlspecialchars($part['logo']) ?>"
                                            alt="<?= htmlspecialchars($part['nom']) ?>"
                                            style="max-height: 80px; max-width: 100%; object-fit: contain;">
                                    </div>
                                <?php else: ?>
                                    <div style="display: flex; justify-content: center; align-items: center; height: 80px; 
                                                background: linear-gradient(135deg, var(--primary-color), var(--accent-color)); 
                                                border-radius: 12px; margin-bottom: 1rem;">
                                        <i class="<?= $this->getPartenaireIcon($type) ?>" style="font-size: 2.5rem; color: white;"></i>
                                    </div>
                                <?php endif; ?>

                                <h4 style="margin: 0 0 0.5rem 0; color: var(--dark-color); font-size: 1rem;">
                                    <?= htmlspecialchars($part['nom']) ?>
                                </h4>

                                <?php if (!empty($part['pays']) || !empty($part['ville'])): ?>
                                    <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?= htmlspecialchars(trim(($part['ville'] ?? '') . ', ' . ($part['pays'] ?? ''), ', ')) ?>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($part['site_web'])): ?>
                                    <a href="<?= htmlspecialchars($part['site_web']) ?>" target="_blank"
                                        style="display: inline-flex; align-items: center; gap: 0.25rem; margin-top: 0.75rem; 
                                              color: var(--primary-color); text-decoration: none; font-size: 0.875rem;">
                                        <i class="fas fa-external-link-alt"></i>
                                        Visiter
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php
            }
        });
    }

    /**
     * Pagination pour événements
     */
    private function renderPagination($currentPage, $totalPages)
    {
        ?>
        <div style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; margin-top: 2rem;">
            <?php if ($currentPage > 1): ?>
                <a href="?page=accueil&page_events=<?= $currentPage - 1 ?>" class="btn btn-outline btn-sm">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=accueil&page_events=<?= $i ?>"
                    class="btn btn-sm <?= $i === $currentPage ? 'btn-primary' : 'btn-outline' ?>" style="min-width: 40px;">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=accueil&page_events=<?= $currentPage + 1 ?>" class="btn btn-outline btn-sm">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Helpers pour les couleurs - MAINTENANT UTILISE LES CONSTANTES
     */
    private function getActualiteColor($type)
    {
        $colors = [
            'projet' => COLOR_PROJET,
            'publication' => COLOR_PUBLICATION,
            'collaboration' => COLOR_COLLABORATION,
            'soutenance' => COLOR_SOUTENANCE,
            'evenement' => COLOR_EVENEMENT
        ];
        return $colors[strtolower($type)] ?? PRIMARY_COLOR;
    }

    private function getPosteColor($poste)
    {
        if (strpos(strtolower($poste), 'directeur') !== false) {
            return COLOR_DIRECTEUR;
        }
        if (strpos(strtolower($poste), 'chef') !== false) {
            return COLOR_CHEF;
        }
        return COLOR_MEMBRE_DEFAULT;
    }

    private function getPartenaireIcon($type)
    {
        $icons = [
            'universite' => 'fas fa-university',
            'entreprise' => 'fas fa-building',
            'organisme' => 'fas fa-globe'
        ];
        return $icons[strtolower($type)] ?? 'fas fa-handshake';
    }
}
?>