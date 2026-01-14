<?php
require_once __DIR__ . '/BaseView.php';

// Import  Components
require_once __DIR__ . '/components/Filter.php';
require_once __DIR__ . '/components/FilterBar.php';
require_once __DIR__ . '/components/SearchInput.php';
require_once __DIR__ . '/components/Badge.php';
require_once __DIR__ . '/components/EmptyState.php';
require_once __DIR__ . '/components/ListContainer.php';

class PublicationView extends BaseView
{
    public function __construct()
    {
        parent::__construct();
        $this->currentPage = 'publications';
        $this->pageTitle = 'Publications';
    }

    /**
     * Render publications list page
     */
    public function renderListe($publications, $years, $types, $domaines, $auteurs)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Page Header -->
                <div class="page-header">
                    <h1><i class="fas fa-file-alt"></i> Base Documentaire et Publications</h1>
                    <p class="subtitle">Consultez les publications du laboratoire</p>
                </div>

                <!-- Filters -->
                <?php
                FilterBar::render([
                    'resetText' => 'Réinitialiser',
                    'resetId' => 'reset-filters'
                ], function () use ($years, $types, $domaines, $auteurs) {
                    // Year Filter
                    Filter::render([
                        'id' => 'filter-annee',
                        'label' => 'Année',
                        'icon' => 'fas fa-calendar',
                        'placeholder' => 'Toutes les années',
                        'options' => array_map(function ($y) {
                            return ['value' => $y['annee'], 'text' => $y['annee']];
                        }, $years)
                    ]);

                    // Type Filter
                    Filter::render([
                        'id' => 'filter-type',
                        'label' => 'Type',
                        'icon' => 'fas fa-tag',
                        'placeholder' => 'Tous les types',
                        'options' => array_map(function ($key, $label) {
                            return ['value' => $key, 'text' => $label];
                        }, array_keys($types), array_values($types))
                    ]);

                    // Domain Filter
                    Filter::render([
                        'id' => 'filter-domaine',
                        'label' => 'Domaine',
                        'icon' => 'fas fa-layer-group',
                        'placeholder' => 'Tous les domaines',
                        'options' => array_map(function ($dom) {
                            return ['value' => $dom['id_thematique'], 'text' => $dom['nom_thematique']];
                        }, $domaines)
                    ]);

                    // Author Filter
                    Filter::render([
                        'id' => 'filter-auteur',
                        'label' => 'Auteur',
                        'icon' => 'fas fa-user',
                        'placeholder' => 'Tous les auteurs',
                        'options' => array_map(function ($auteur) {
                            return ['value' => $auteur, 'text' => $auteur];
                        }, $auteurs)
                    ]);

                    // Advanced Search
                    SearchInput::render([
                        'id' => 'search-input',
                        'label' => 'Recherche avancée',
                        'icon' => 'fas fa-search',
                        'placeholder' => 'Titre, auteurs, résumé...'
                    ]);
                });
                ?>

                <!-- Publications List -->
                <div id="publications-container" style="margin-top: <?= SPACING_XL ?>;">
                    <?php $this->renderPublicationsList($publications); ?>
                </div>

                <!-- Loading State -->
                <div id="loading" class="loading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Chargement...
                </div>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    /**
     * Render publications list
     */
    public function renderPublicationsList($publications)
    {
        if (empty($publications)) {
            EmptyState::render([
                'icon' => 'fas fa-file-alt',
                'title' => 'Aucune publication trouvée',
                'description' => 'Essayez de modifier vos filtres ou votre recherche pour voir plus de résultats'
            ]);
            return;
        }

        ListContainer::render(['gap' => SPACING_LG], function () use ($publications) {
            foreach ($publications as $pub) {
                $this->renderPublicationCard($pub);
            }
        });
    }

    /**
     * Render single publication card 
     */
    private function renderPublicationCard($pub)
    {
        // Format the full date
        $datePublication = 'Non spécifiée';
        if (!empty($pub['date_publication'])) {
            $datePublication = date('d/m/Y', strtotime($pub['date_publication']));
        } elseif (!empty($pub['annee'])) {
            $datePublication = $pub['annee'];
        }
        ?>
        <div class="card" 
             style="display: flex; 
                    flex-direction: row; 
                    align-items: stretch; 
                    background: <?= BG_WHITE ?>; 
                    border-radius: <?= RADIUS_LG ?>; 
                    box-shadow: <?= SHADOW_MD ?>; 
                    overflow: hidden;
                    transition: transform 0.2s ease, box-shadow 0.2s ease;">
            
            <!-- Left Side: Badges -->
            <div style="padding: <?= SPACING_LG ?>; 
                        background: <?= BG_GRAY_LIGHT ?>; 
                        border-right: 1px solid <?= BORDER_GRAY ?>; 
                        display: flex; 
                        flex-direction: column; 
                        align-items: center; 
                        justify-content: center; 
                        gap: <?= SPACING_MD ?>; 
                        min-width: 140px;">
                <?php
                Badge::render([
                    'text' => strtoupper($pub['type']),
                    'variant' => $this->getTypeVariant($pub['type']),
                    'size' => 'medium'
                ]);

                Badge::render([
                    'text' => $datePublication,
                    'variant' => 'default',
                    'size' => 'small',
                    'icon' => 'fas fa-calendar'
                ]);
                ?>
            </div>

            <!-- Right Side: Content -->
            <div style="flex: 1; 
                        padding: <?= SPACING_LG ?>; 
                        display: flex; 
                        flex-direction: column; 
                        gap: <?= SPACING_MD ?>;">
                
                <!-- Title -->
                <h3 style="margin: 0; 
                           font-size: 1.35rem; 
                           font-weight: 600; 
                           line-height: 1.4; 
                           color: <?= TEXT_DARK ?>;">
                    <?= htmlspecialchars($pub['titre']) ?>
                </h3>

                <!-- Authors -->
                <p style="margin: 0; 
                          color: <?= TEXT_GRAY ?>; 
                          display: flex; 
                          align-items: center; 
                          gap: <?= SPACING_SM ?>; 
                          font-size: 0.95rem;">
                    <i class="fas fa-user" style="color: <?= PRIMARY_COLOR ?>;"></i>
                    <?= htmlspecialchars($pub['auteurs']) ?>
                </p>

                <!-- Resume -->
                <?php if (!empty($pub['resume'])): ?>
                    <p style="margin: 0; 
                              color: <?= TEXT_GRAY ?>; 
                              line-height: 1.6; 
                              font-size: 0.95rem;">
                        <?= htmlspecialchars(mb_substr($pub['resume'], 0, 200)) ?>...
                    </p>
                <?php endif; ?>

                <!-- Meta Information & Actions -->
                <div style="display: flex; 
                            align-items: center; 
                            justify-content: space-between; 
                            flex-wrap: wrap; 
                            gap: <?= SPACING_MD ?>; 
                            padding-top: <?= SPACING_MD ?>; 
                            border-top: 1px solid <?= BORDER_GRAY ?>; 
                            margin-top: auto;">
                    
                    <!-- Left: Meta Info -->
                    <div style="display: flex; 
                                align-items: center; 
                                gap: <?= SPACING_LG ?>; 
                                flex-wrap: wrap;">
                        <?php if (!empty($pub['doi'])): ?>
                            <span style="display: flex; 
                                         align-items: center; 
                                         gap: <?= SPACING_SM ?>; 
                                         color: <?= TEXT_GRAY ?>; 
                                         font-size: 0.875rem;">
                                <i class="fas fa-link" style="color: <?= PRIMARY_COLOR ?>;"></i>
                                <span style="font-family: monospace;">
                                    DOI: <?= htmlspecialchars($pub['doi']) ?>
                                </span>
                            </span>
                        <?php endif; ?>

                        <?php if (!empty($pub['domaine_nom'])): ?>
                            <span style="display: flex; 
                                         align-items: center; 
                                         gap: <?= SPACING_SM ?>; 
                                         color: <?= TEXT_GRAY ?>; 
                                         font-size: 0.875rem;">
                                <i class="fas fa-layer-group" style="color: <?= PRIMARY_COLOR ?>;"></i>
                                <?= htmlspecialchars($pub['domaine_nom']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Right: Download Button -->
                    <div style="margin-left: auto;">
                        <?php if (!empty($pub['fichier'])): ?>
                            <a href="<?= UPLOADS_URL . 'publications/' . $pub['fichier'] ?>" 
                               target="_blank" 
                               download
                               style="display: inline-flex; 
                                      align-items: center; 
                                      gap: <?= SPACING_SM ?>; 
                                      padding: <?= SPACING_SM ?> <?= SPACING_MD ?>; 
                                      background: <?= PRIMARY_COLOR ?>; 
                                      color: <?= TEXT_WHITE ?>; 
                                      text-decoration: none; 
                                      border-radius: <?= RADIUS_SM ?>; 
                                      font-size: 0.9rem; 
                                      font-weight: 600;
                                      transition: all 0.2s ease;
                                      white-space: nowrap;">
                                <i class="fas fa-download"></i>
                                Télécharger
                            </a>
                        <?php else: ?>
                            <span style="display: inline-flex; 
                                         align-items: center; 
                                         gap: <?= SPACING_SM ?>; 
                                         padding: <?= SPACING_SM ?> <?= SPACING_MD ?>; 
                                         background: <?= BG_GRAY ?>; 
                                         color: <?= TEXT_GRAY ?>; 
                                         border-radius: <?= RADIUS_SM ?>; 
                                         font-size: 0.9rem; 
                                         cursor: not-allowed;
                                         white-space: nowrap;">
                                <i class="fas fa-ban"></i>
                                Non disponible
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get badge variant based on publication type 
     */
    private function getTypeVariant($type)
    {
        $type = strtolower($type);

        if (strpos($type, 'article') !== false) return 'primary';
        if (strpos($type, 'conf') !== false || strpos($type, 'communication') !== false) return 'success';
        if (strpos($type, 'rapport') !== false) return 'info';
        if (strpos($type, 'thèse') !== false || strpos($type, 'these') !== false) return 'warning';
        if (strpos($type, 'poster') !== false) return 'pink';

        return 'default';
    }
}
?>