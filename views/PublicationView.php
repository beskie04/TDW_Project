<?php
require_once __DIR__ . '/BaseView.php';

// Import Generic Framework Components
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
                <div id="publications-container">
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

        ListContainer::render(['gap' => '1.5rem'], function () use ($publications) {
            foreach ($publications as $pub) {
                $this->renderPublicationCard($pub);
            }
        });
    }

    /**
     * Render single publication card - FIXED VERSION
     */
    private function renderPublicationCard($pub)
    {
        // Format the full date
        $datePublication = 'Non spécifiée';
        if (!empty($pub['date_publication'])) {
            $datePublication = date('d/m/Y', strtotime($pub['date_publication']));
        } elseif (!empty($pub['annee'])) {
            // If only year is available, show just the year
            $datePublication = $pub['annee'];
        }

        ?>
        <div class="card" style="flex-direction: row; align-items: stretch;">
            <!-- Left Side: Badges -->
            <div
                style="padding: 1.5rem; background: var(--gray-50, #f9fafb); border-right: 1px solid var(--gray-200); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem; min-width: 120px;">
                <?php
                Badge::render([
                    'text' => strtoupper($pub['type']),
                    'variant' => $this->getTypeVariant($pub['type']),
                    'size' => 'small'
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
            <div class="card-content" style="flex: 1;">
                <h3 class="card-title" style="font-size: 1.35rem; margin-bottom: 0.75rem;">
                    <?= htmlspecialchars($pub['titre']) ?>
                </h3>

                <p
                    style="color: var(--gray-600); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; font-size: 0.95rem;">
                    <i class="fas fa-user"></i>
                    <?= htmlspecialchars($pub['auteurs']) ?>
                </p>

                <?php if (!empty($pub['resume'])): ?>
                    <p class="card-description" style="margin-bottom: 1rem;">
                        <?= htmlspecialchars(mb_substr($pub['resume'], 0, 200)) ?>...
                    </p>
                <?php endif; ?>

                <!-- Meta Information -->
                <div class="card-footer"
                    style="border-top: none; padding-top: 0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
                        <?php if (!empty($pub['doi'])): ?>
                            <span class="card-footer-item">
                                <i class="fas fa-link"></i>
                                DOI:
                                <?= htmlspecialchars($pub['doi']) ?>
                            </span>
                        <?php endif; ?>

                        <?php if (!empty($pub['domaine_nom'])): ?>
                            <span class="card-footer-item">
                                <i class="fas fa-layer-group"></i>
                                <?= htmlspecialchars($pub['domaine_nom']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Download Link - ALWAYS SHOW -->
                    <div style="display: flex; gap: 0.75rem; margin-left: auto;">
                        <?php if (!empty($pub['fichier'])): ?>
                            <a href="<?= UPLOADS_URL . 'publications/' . $pub['fichier'] ?>" class="btn btn-primary btn-sm"
                                target="_blank" download
                                style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: var(--primary-color); color: white; text-decoration: none; border-radius: 6px; font-size: 0.9rem; transition: all 0.3s ease;">
                                <i class="fas fa-download"></i>
                                Télécharger
                            </a>
                        <?php else: ?>
                            <span
                                style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: var(--gray-300); color: var(--gray-600); border-radius: 6px; font-size: 0.9rem; cursor: not-allowed;">
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

        if (strpos($type, 'article') !== false)
            return 'primary';
        if (strpos($type, 'conf') !== false || strpos($type, 'communication') !== false)
            return 'success';
        if (strpos($type, 'rapport') !== false)
            return 'info';
        if (strpos($type, 'thèse') !== false || strpos($type, 'these') !== false)
            return 'warning';
        if (strpos($type, 'poster') !== false)
            return 'danger';

        return 'default';
    }
}
?>