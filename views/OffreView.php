<?php
require_once __DIR__ . '/BaseView.php';

// Import Components
require_once __DIR__ . '/components/Section.php';
require_once __DIR__ . '/components/Card.php';
require_once __DIR__ . '/components/Badge.php';
require_once __DIR__ . '/components/Filter.php';
require_once __DIR__ . '/components/FilterBar.php';
require_once __DIR__ . '/components/EmptyState.php';

class OffreView extends BaseView
{
    public function __construct()
    { parent::__construct();
        $this->currentPage = 'offres';
        $this->pageTitle = 'Offres et Opportunités';
    }

    /**
     * Liste des offres
     */
    public function renderListe($offres)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Page Header -->
                <div class="page-header">
                    <h1><i class="fas fa-briefcase"></i> Offres et Opportunités</h1>
                    <p class="subtitle">Découvrez nos opportunités de stages, thèses, bourses et collaborations</p>
                </div>

                <!-- Filters -->
                <?php
                FilterBar::render([
                    'resetText' => 'Toutes les offres',
                    'resetId' => 'reset-filters'
                ], function () {
                    Filter::render([
                        'id' => 'filter-type',
                        'label' => 'Type d\'offre',
                        'icon' => 'fas fa-filter',
                        'placeholder' => 'Tous les types',
                        'options' => [
                            ['value' => 'stage', 'text' => 'Stages'],
                            ['value' => 'these', 'text' => 'Thèses'],
                            ['value' => 'bourse', 'text' => 'Bourses'],
                            ['value' => 'collaboration', 'text' => 'Collaborations']
                        ]
                    ]);
                });
                ?>

                <!-- Offres Container -->
                <div id="offres-container">
                    <?php $this->renderOffresCards($offres); ?>
                </div>
            </div>
        </main>

        <script>
            const typeFilter = document.getElementById('filter-type');
            const resetBtn = document.getElementById('reset-filters');

            if (typeFilter) {
                typeFilter.addEventListener('change', function () {
                    const type = this.value;
                    window.location.href = '?page=offres' + (type ? '&type=' + type : '');
                });
            }

            if (resetBtn) {
                resetBtn.addEventListener('click', function () {
                    window.location.href = '?page=offres';
                });
            }
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Afficher les cartes d'offres
     */
    private function renderOffresCards($offres)
{
    if (empty($offres)) {
        EmptyState::render([
            'icon' => 'fas fa-briefcase',
            'title' => 'Aucune offre disponible',
            'description' => 'Revenez plus tard ou modifiez vos filtres'
        ]);
        return;
    }

    echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem; margin-top: 2rem;">';

    foreach ($offres as $offre) {
        $typeColors = [
            'stage' => '#3b82f6',
            'these' => '#8b5cf6',
            'bourse' => '#10b981',
            'collaboration' => '#f59e0b'
        ];

        $typeIcons = [
            'stage' => 'fas fa-user-graduate',
            'these' => 'fas fa-graduation-cap',
            'bourse' => 'fas fa-hand-holding-usd',
            'collaboration' => 'fas fa-handshake'
        ];

        $color = $typeColors[$offre['type']] ?? '#6b7280';
        $icon = $typeIcons[$offre['type']] ?? 'fas fa-briefcase';
        ?>

        <div class="card" style="border-top: 4px solid <?= $color ?>; display: flex; flex-direction: column;">
            <div class="card-content" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; flex: 1;">
                <!-- Header with Badge and Date -->
                <div style="display: flex; justify-content: space-between; align-items: start; gap: 1rem; flex-wrap: wrap;">
                    <?php
                    Badge::render([
                        'text' => ucfirst($offre['type']),
                        'color' => $color,
                        'icon' => $icon,
                        'size' => 'medium'
                    ]);
                    ?>

                    <?php if ($offre['date_limite']): ?>
                        <span style="font-size: 0.875rem; color: var(--gray-600); display: flex; align-items: center; gap: 0.5rem; white-space: nowrap;">
                            <i class="fas fa-clock"></i>
                            <span>Limite: <?= date('d/m/Y', strtotime($offre['date_limite'])) ?></span>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Title -->
                <h3 class="card-title" style="margin: 0; font-size: 1.25rem; color: var(--dark-color); line-height: 1.4;">
                    <?= htmlspecialchars($offre['titre']) ?>
                </h3>

                <!-- Description -->
                <p class="card-description" style="margin: 0; color: var(--gray-600); line-height: 1.6; flex: 1;">
                    <?= htmlspecialchars(mb_substr($offre['description'], 0, 150)) ?>...
                </p>

                <!-- Link -->
                <div style="padding-top: 1rem; margin-top: auto; border-top: 1px solid var(--gray-200);">
                    <a href="?page=offres&action=details&id=<?= $offre['id_offre'] ?>" 
                       class="card-link" 
                       style="display: inline-flex; align-items: center; gap: 0.5rem; color: <?= $color ?>; font-weight: 600; text-decoration: none; transition: gap 0.3s ease;">
                        Voir les détails
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    echo '</div>';
}

    /**
     * Détails d'une offre
     */
    public function renderDetails($offre)
    {
        $this->pageTitle = $offre['titre'];
        $this->renderHeader();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <a href="?page=offres" class="btn btn-outline" style="margin-bottom: 1.5rem;">
                    <i class="fas fa-arrow-left"></i> Retour aux offres
                </a>

                <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
                    <!-- Header -->
                    <?php
                    $typeColors = [
                        'stage' => '#3b82f6',
                        'these' => '#8b5cf6',
                        'bourse' => '#10b981',
                        'collaboration' => '#f59e0b'
                    ];
                    $color = $typeColors[$offre['type']] ?? '#6b7280';
                    ?>

                    <div style="padding: 2rem; background: linear-gradient(135deg, <?= $color ?>15, <?= $color ?>05);">
                        <?php
                        Badge::render([
                            'text' => ucfirst($offre['type']),
                            'color' => $color,
                            'size' => 'large'
                        ]);
                        ?>

                        <h1 style="margin: 1rem 0; font-size: 2rem; color: var(--dark-color);">
                            <?= htmlspecialchars($offre['titre']) ?>
                        </h1>

                        <div style="display: flex; gap: 2rem; color: var(--gray-600); font-size: 0.9rem;">
                            <span>
                                <i class="fas fa-calendar"></i>
                                Publié le
                                <?= date('d/m/Y', strtotime($offre['date_creation'])) ?>
                            </span>

                            <?php if ($offre['date_limite']): ?>
                                <span style="color: #f59e0b; font-weight: 600;">
                                    <i class="fas fa-clock"></i>
                                    Date limite:
                                    <?= date('d/m/Y', strtotime($offre['date_limite'])) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Content -->
                    <div style="padding: 2rem;">
                        <div style="line-height: 1.8; color: var(--gray-700);">
                            <?= nl2br(htmlspecialchars($offre['description'])) ?>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div style="padding: 1.5rem 2rem; background: #f7fafc; border-top: 1px solid var(--gray-200);">
                        <a href="?page=contact" class="btn btn-primary">
                            <i class="fas fa-envelope"></i> Postuler / Nous contacter
                        </a>
                    </div>
                </div>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }
}
?>