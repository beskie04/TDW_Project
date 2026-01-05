<?php
require_once __DIR__ . '/BaseView.php';

// Import Generic Framework Components
require_once __DIR__ . '/components/Card.php';
require_once __DIR__ . '/components/Grid.php';
require_once __DIR__ . '/components/Section.php';
require_once __DIR__ . '/components/Filter.php';
require_once __DIR__ . '/components/FilterBar.php';
require_once __DIR__ . '/components/Badge.php';
require_once __DIR__ . '/components/Breadcrumb.php';
require_once __DIR__ . '/components/InfoList.php';
require_once __DIR__ . '/components/Avatar.php';
require_once __DIR__ . '/components/EmptyState.php';

class ProjetView extends BaseView
{
    public function __construct()
    {
        $this->currentPage = 'projets';
        $this->pageTitle = 'Projets de Recherche';
    }

    /**
     * Render project list page
     */
    public function renderListe($projets, $thematiques, $statuts, $responsables)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Page Header -->
                <div class="page-header">
                    <h1><i class="fas fa-project-diagram"></i> Catalogue des Projets de Recherche</h1>
                    <p class="subtitle">Découvrez les projets de recherche menés par notre laboratoire</p>
                </div>

                <!-- Filters -->
                <?php
                FilterBar::render([
                    'resetText' => 'Réinitialiser',
                    'resetId' => 'reset-filters'
                ], function () use ($thematiques, $responsables, $statuts) {
                    // Thematique Filter
                    Filter::render([
                        'id' => 'filter-thematique',
                        'label' => 'Thématique',
                        'icon' => 'fas fa-tag',
                        'placeholder' => 'Toutes les thématiques',
                        'options' => array_map(function ($t) {
                            return ['value' => $t['id_thematique'], 'text' => $t['nom_thematique']];
                        }, $thematiques)
                    ]);

                    // Responsable Filter
                    Filter::render([
                        'id' => 'filter-responsable',
                        'label' => 'Responsable',
                        'icon' => 'fas fa-user-tie',
                        'placeholder' => 'Tous les responsables',
                        'options' => array_map(function ($r) {
                            return ['value' => $r['id_membre'], 'text' => $r['nom'] . ' ' . $r['prenom']];
                        }, $responsables)
                    ]);

                    // Statut Filter
                    Filter::render([
                        'id' => 'filter-statut',
                        'label' => 'Statut',
                        'icon' => 'fas fa-tasks',
                        'placeholder' => 'Tous les statuts',
                        'options' => array_map(function ($s) {
                            return ['value' => $s['id_statut'], 'text' => $s['nom_statut']];
                        }, $statuts)
                    ]);
                });
                ?>

                <!-- Projects Container -->
                <div id="projets-container">
                    <?php $this->renderProjetsCards($projets); ?>
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
     * Render project cards grouped by thematic
     */
    public function renderProjetsCards($projets)
    {
        if (empty($projets)) {
            EmptyState::render([
                'icon' => 'fas fa-project-diagram',
                'title' => 'Aucun projet trouvé',
                'description' => 'Essayez de modifier vos filtres pour voir plus de résultats'
            ]);
            return;
        }

        // Group by thematic
        $projetsParThematique = [];
        foreach ($projets as $projet) {
            $themId = $projet['id_thematique'] ?? 0;
            $themNom = $projet['thematique_nom'] ?? 'Non classé';

            if (!isset($projetsParThematique[$themId])) {
                $projetsParThematique[$themId] = [
                    'nom' => $themNom,
                    'projets' => []
                ];
            }
            $projetsParThematique[$themId]['projets'][] = $projet;
        }

        // Render each thematic group
        foreach ($projetsParThematique as $groupe) {
            Section::render([
                'title' => $groupe['nom'],
                'icon' => 'fas fa-folder',
                'cssClass' => 'thematique-section'
            ], function () use ($groupe) {
                // Count badge
                echo '<span class="projet-count" style="margin-left: auto; color: var(--gray-600);">';
                echo count($groupe['projets']) . ' projet(s)';
                echo '</span>';

                // Projects grid
                Grid::render(['minWidth' => '350px', 'gap' => '1.5rem'], function () use ($groupe) {
                    foreach ($groupe['projets'] as $projet) {
                        $this->renderProjetCard($projet);
                    }
                });
            });
        }
    }

    /**
     * Render single project card
     */
    private function renderProjetCard($projet)
    {
        // Get project members
        require_once __DIR__ . '/../models/ProjetModel.php';
        $projetModel = new ProjetModel();
        $membres = $projetModel->getMembres($projet['id_projet']);

        // Prepare footer items
        $footerItems = [
            [
                'icon' => 'fas fa-user-tie',
                'text' => ($projet['responsable_nom'] ?? '') . ' ' . ($projet['responsable_prenom'] ?? '')
            ]
        ];

        if (!empty($membres)) {
            $nomsMembres = array_slice(array_map(function ($m) {
                return $m['nom'] . ' ' . $m['prenom'];
            }, $membres), 0, 3);

            $membresText = implode(', ', $nomsMembres);
            if (count($membres) > 3) {
                $membresText .= ' +' . (count($membres) - 3);
            }

            $footerItems[] = [
                'icon' => 'fas fa-users',
                'text' => $membresText
            ];
        }

        $footerItems[] = [
            'icon' => 'fas fa-money-bill-wave',
            'text' => $projet['type_financement_nom'] ?? 'Non défini'
        ];

        // Render card with custom header
        ?>
        <div class="card">
            <!-- Custom header with badges -->
            <div
                style="padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; border-bottom: 1px solid var(--gray-200);">
                <?php
                Badge::render([
                    'text' => $projet['thematique_nom'] ?? 'Non défini',
                    'variant' => 'primary',
                    'size' => 'small'
                ]);

                Badge::render([
                    'text' => $projet['statut_nom'] ?? 'Non défini',
                    'variant' => $this->getStatusVariant($projet['statut_nom'] ?? ''),
                    'size' => 'small'
                ]);
                ?>
            </div>

            <!-- Card content -->
            <div class="card-content">
                <h3 class="card-title">
                    <a href="?page=projets&action=details&id=<?= $projet['id_projet'] ?>"
                        style="text-decoration: none; color: inherit;">
                        <?= htmlspecialchars($projet['titre']) ?>
                    </a>
                </h3>

                <p class="card-description">
                    <?= htmlspecialchars(mb_substr($projet['description'] ?? '', 0, 150)) ?>...
                </p>

                <div class="card-footer">
                    <?php foreach ($footerItems as $item): ?>
                        <span class="card-footer-item">
                            <i class="<?= htmlspecialchars($item['icon']) ?>"></i>
                            <?= htmlspecialchars($item['text']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>

                <a href="?page=projets&action=details&id=<?= $projet['id_projet'] ?>" class="card-link">
                    Voir les détails
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Render project details page
     */
    public function renderDetails($projet, $membres, $publications, $partenaires = [])
    {
        $this->pageTitle = $projet['titre'] . ' - Projet de Recherche';
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Breadcrumb -->
                <?php
                Breadcrumb::render([
                    ['text' => 'Accueil', 'url' => '?page=accueil'],
                    ['text' => 'Projets', 'url' => '?page=projets'],
                    ['text' => $projet['titre']]
                ]);
                ?>

                <div class="projet-details">
                    <!-- Header -->
                    <div class="details-header" style="margin-bottom: 2rem;">
                        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                            <?php
                            Badge::render([
                                'text' => $projet['thematique_nom'] ?? 'Non défini',
                                'variant' => 'primary',
                                'size' => 'large'
                            ]);

                            Badge::render([
                                'text' => $projet['statut_nom'] ?? 'Non défini',
                                'variant' => $this->getStatusVariant($projet['statut_nom'] ?? ''),
                                'size' => 'large'
                            ]);
                            ?>
                        </div>

                        <h1 style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--dark-color);">
                            <?= htmlspecialchars($projet['titre']) ?>
                        </h1>

                        <p style="color: var(--gray-600); display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-calendar"></i>
                            <?= date('d/m/Y', strtotime($projet['date_debut'])) ?>
                            <?php if ($projet['date_fin']): ?>
                                -
                                <?= date('d/m/Y', strtotime($projet['date_fin'])) ?>
                            <?php else: ?>
                                - En cours
                            <?php endif; ?>
                        </p>
                    </div>

                    <!-- Main Content Grid -->
                    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem; align-items: start;">
                        <!-- Left Column: Main Content -->
                        <div>
                            <!-- Description -->
                            <?php
                            Section::render([
                                'title' => 'Description',
                                'icon' => 'fas fa-align-left'
                            ], function () use ($projet) {
                                echo '<div style="padding: 1.5rem; background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
                                echo '<p style="line-height: 1.8; color: var(--gray-700);">';
                                echo nl2br(htmlspecialchars($projet['description'] ?? ''));
                                echo '</p></div>';
                            });
                            ?>

                            <!-- Objectifs -->
                            <?php if (!empty($projet['objectifs'])): ?>
                                <?php
                                Section::render([
                                    'title' => 'Objectifs',
                                    'icon' => 'fas fa-bullseye'
                                ], function () use ($projet) {
                                    echo '<div style="padding: 1.5rem; background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
                                    echo '<p style="line-height: 1.8; color: var(--gray-700);">';
                                    echo nl2br(htmlspecialchars($projet['objectifs']));
                                    echo '</p></div>';
                                });
                                ?>
                            <?php endif; ?>

                            <!-- Team Members -->
                            <?php if (!empty($membres)): ?>
                                <?php
                                Section::render([
                                    'title' => 'Équipe du projet',
                                    'icon' => 'fas fa-users'
                                ], function () use ($membres) {
                                    Grid::render(['minWidth' => '280px', 'gap' => '1rem'], function () use ($membres) {
                                        foreach ($membres as $membre) {
                                            $this->renderMembreCard($membre);
                                        }
                                    });
                                });
                                ?>
                            <?php endif; ?>

                            <!-- Partners Section - NEW! -->
                            <?php if (!empty($partenaires)): ?>
                                <?php
                                Section::render([
                                    'title' => 'Partenaires du projet',
                                    'icon' => 'fas fa-handshake'
                                ], function () use ($partenaires) {
                                    Grid::render(['minWidth' => '280px', 'gap' => '1rem'], function () use ($partenaires) {
                                        foreach ($partenaires as $partenaire) {
                                            $this->renderPartenaireCard($partenaire);
                                        }
                                    });
                                });
                                ?>
                            <?php endif; ?>

                            <!-- Publications -->
                            <?php if (!empty($publications)): ?>
                                <?php
                                Section::render([
                                    'title' => 'Publications associées',
                                    'icon' => 'fas fa-file-alt'
                                ], function () use ($publications) {
                                    echo '<div style="display: flex; flex-direction: column; gap: 1rem;">';
                                    foreach ($publications as $pub) {
                                        $this->renderPublicationCard($pub);
                                    }
                                    echo '</div>';
                                });
                                ?>
                            <?php endif; ?>
                        </div>

                        <!-- Right Column: Sidebar -->
                        <div>
                            <?php
                            // Prepare info items
                            $infoItems = [
                                [
                                    'label' => 'Responsable',
                                    'value' => ($projet['responsable_nom'] ?? '') . ' ' . ($projet['responsable_prenom'] ?? '')
                                ],
                                [
                                    'label' => 'Financement',
                                    'value' => $projet['type_financement_nom'] ?? 'Non défini'
                                ]
                            ];

                            if ($projet['budget']) {
                                $infoItems[] = [
                                    'label' => 'Budget',
                                    'value' => number_format($projet['budget'], 0, ',', ' ') . ' DZD'
                                ];
                            }

                            // Add partner count if any
                            if (!empty($partenaires)) {
                                $infoItems[] = [
                                    'label' => 'Partenaires',
                                    'value' => count($partenaires) . ' partenaire(s)'
                                ];
                            }

                            InfoList::render($infoItems, [
                                'title' => 'Informations',
                                'titleIcon' => 'fas fa-info-circle'
                            ]);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    /**
     * Render member card (for project team)
     */
    private function renderMembreCard($membre)
    {
        $photoUrl = $membre['photo'] ? UPLOADS_URL . 'photos/' . $membre['photo'] : null;
        ?>
        <div
            style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; gap: 1rem; align-items: center;">
            <?php
            Avatar::render([
                'src' => $photoUrl,
                'alt' => $membre['nom'] . ' ' . $membre['prenom'],
                'size' => 'large'
            ]);
            ?>
            <div style="flex: 1;">
                <h4 style="margin: 0 0 0.25rem 0; color: var(--dark-color);">
                    <?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?>
                </h4>
                <p style="margin: 0; color: var(--primary-color); font-weight: 600; font-size: 0.9rem;">
                    <?= htmlspecialchars($membre['role_projet'] ?? 'Membre') ?>
                </p>
                <p style="margin: 0.25rem 0 0 0; color: var(--gray-600); font-size: 0.85rem;">
                    <?= htmlspecialchars($membre['grade'] ?? '') ?>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Render partner card - NEW!
     */
    private function renderPartenaireCard($partenaire)
    {
        $logoUrl = !empty($partenaire['logo']) ? UPLOADS_URL . 'logos/' . $partenaire['logo'] : null;
        ?>
        <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <?php if ($logoUrl): ?>
                <div
                    style="display: flex; justify-content: center; margin-bottom: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars($partenaire['nom']) ?>"
                        style="max-height: 80px; max-width: 100%; object-fit: contain;">
                </div>
            <?php else: ?>
                <div
                    style="display: flex; justify-content: center; align-items: center; margin-bottom: 1rem; padding: 2rem; background: linear-gradient(135deg, var(--primary-color), var(--accent-color)); border-radius: 8px;">
                    <i class="fas fa-handshake" style="font-size: 3rem; color: white;"></i>
                </div>
            <?php endif; ?>

            <h4 style="margin: 0 0 0.5rem 0; color: var(--dark-color); font-size: 1.1rem; text-align: center;">
                <?= htmlspecialchars($partenaire['nom']) ?>
            </h4>

            <div style="display: flex; justify-content: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                <?php
                Badge::render([
                    'text' => $partenaire['type_partenaire'] ?? 'Partenaire',
                    'variant' => 'primary',
                    'size' => 'small'
                ]);

                if (!empty($partenaire['role_partenaire'])) {
                    Badge::render([
                        'text' => $partenaire['role_partenaire'],
                        'variant' => 'info',
                        'size' => 'small'
                    ]);
                }
                ?>
            </div>

            <?php if (!empty($partenaire['description'])): ?>
                <p style="margin: 0.75rem 0 0 0; color: var(--gray-600); font-size: 0.9rem; line-height: 1.5; text-align: center;">
                    <?= htmlspecialchars(mb_substr($partenaire['description'], 0, 100)) ?>
                    <?= strlen($partenaire['description']) > 100 ? '...' : '' ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($partenaire['site_web'])): ?>
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="<?= htmlspecialchars($partenaire['site_web']) ?>" target="_blank" rel="noopener noreferrer"
                        style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                        <i class="fas fa-external-link-alt"></i>
                        Visiter le site
                    </a>
                </div>
            <?php endif; ?>

            <?php if (!empty($partenaire['date_debut']) || !empty($partenaire['date_fin'])): ?>
                <div
                    style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--gray-200); font-size: 0.85rem; color: var(--gray-600); text-align: center;">
                    <i class="fas fa-calendar"></i>
                    <?php if (!empty($partenaire['date_debut'])): ?>
                        <?= date('d/m/Y', strtotime($partenaire['date_debut'])) ?>
                    <?php endif; ?>
                    <?php if (!empty($partenaire['date_fin'])): ?>
                        -
                        <?= date('d/m/Y', strtotime($partenaire['date_fin'])) ?>
                    <?php elseif (!empty($partenaire['date_debut'])): ?>
                        - En cours
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render publication card
     */
    private function renderPublicationCard($pub)
    {
        ?>
        <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <?php
            Badge::render([
                'text' => $pub['type'],
                'variant' => 'info',
                'size' => 'small'
            ]);
            ?>
            <h4 style="margin: 0.75rem 0 0.5rem 0; color: var(--dark-color);">
                <?= htmlspecialchars($pub['titre']) ?>
            </h4>
            <p style="margin: 0 0 0.5rem 0; color: var(--gray-600); font-size: 0.9rem;">
                <?= htmlspecialchars($pub['auteurs']) ?>
            </p>
            <p style="margin: 0; color: var(--gray-500); font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-calendar"></i>
                <?= htmlspecialchars($pub['annee']) ?>
            </p>
        </div>
        <?php
    }

    /**
     * Get badge variant based on status
     */
    private function getStatusVariant($statut)
    {
        $statut = strtolower($statut);

        if (strpos($statut, 'cours') !== false)
            return 'success';
        if (strpos($statut, 'termin') !== false)
            return 'default';
        if (strpos($statut, 'soumis') !== false)
            return 'warning';

        return 'default';
    }
}
?>