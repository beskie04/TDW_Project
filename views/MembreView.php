<?php
require_once __DIR__ . '/BaseView.php';

// Import Generic Framework Components
require_once __DIR__ . '/components/TextBlock.php';
require_once __DIR__ . '/components/Section.php';
require_once __DIR__ . '/components/Grid.php';
require_once __DIR__ . '/components/Card.php';
require_once __DIR__ . '/components/Badge.php';
require_once __DIR__ . '/components/Avatar.php';
require_once __DIR__ . '/components/Button.php';
require_once __DIR__ . '/components/Breadcrumb.php';
require_once __DIR__ . '/components/ProfileCard.php';
require_once __DIR__ . '/components/Tag.php';
require_once __DIR__ . '/components/ListContainer.php';
require_once __DIR__ . '/components/Slider.php';
require_once __DIR__ . '/components/Filter.php';
require_once __DIR__ . '/components/FilterBar.php';

class MembreView extends BaseView
{
    public function __construct()
    {
           parent::__construct();
        $this->currentPage = 'membres';
        $this->pageTitle = 'Membres et Équipes';
    }

    /**
     * Main page: Presentation, Organization Chart, and Teams
     */
    public function renderIndex($equipes, $directeur)
    {
        
        require_once __DIR__ . '/../models/ThematiqueModel.php';
        require_once __DIR__ . '/../models/MembreModel.php';

        $thematiqueModel = new ThematiqueModel();
        $membreModel = new MembreModel();

        $thematiques = $thematiqueModel->getAllActives();
        $autresMembres = $membreModel->getAllExceptDirecteur();

        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Page Header -->
                <div class="page-header">
                    <h1><i class="fas fa-users"></i> Présentation, Organigramme et Équipes</h1>
                </div>

                <!-- Lab Presentation -->
                <?php
                TextBlock::render([
                    'title' => 'Présentation du Laboratoire',
                    'icon' => 'fas fa-flask',
                    'content' => [
                        'Le Laboratoire de Recherche en Informatique de l\'École Supérieure d\'Informatique est un centre d\'excellence dédié à l\'innovation et à la recherche de pointe dans divers domaines de l\'informatique. Nos équipes travaillent sur des problématiques actuelles telles que l\'intelligence artificielle, la cybersécurité, le cloud computing, les réseaux et les systèmes embarqués.',
                        'Fort d\'une équipe de chercheurs expérimentés et de doctorants talentueux, le laboratoire collabore avec des partenaires académiques et industriels nationaux et internationaux pour produire des résultats de recherche de haut niveau et former la prochaine génération d\'experts en informatique.'
                    ]
                ]);
                ?>
                <div style="margin: 3rem 0;"></div>

                <!-- Thématiques de recherche -->
                <?php if (!empty($thematiques)): ?>
                    <?php
                    Section::render([
                        'title' => 'Thématiques de Recherche',
                        'icon' => 'fas fa-lightbulb'
                    ], function () use ($thematiques) {
                        echo '<div style="display: grid; gap: 1.5rem;">';
                        foreach ($thematiques as $thematique) {
                            ?>
                            <div
                                style="padding: 1.5rem; background: white; border-left: 4px solid var(--primary-color); border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                <h4 style="margin: 0 0 0.75rem 0; color: var(--primary-color); font-size: 1.1rem;">
                                    <?= htmlspecialchars($thematique['nom_thematique']) ?>
                                </h4>
                                <p style="margin: 0; line-height: 1.6; color: var(--gray-700);">
                                    <?= htmlspecialchars($thematique['description']) ?>
                                </p>
                            </div>
                            <?php
                        }
                        echo '</div>';
                    });
                    ?>
                <?php endif; ?>
               <div style="margin: 3rem 0;"></div>
                <!-- Liste des noms d'équipes -->
                <?php
                Section::render([
                    'title' => 'Nos Équipes de Recherche',
                    'icon' => 'fas fa-users-cog'
                ], function () use ($equipes) {
                    echo '<div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">';
                    foreach ($equipes as $equipe) {
                        Tag::render([
                            'text' => $equipe['nom'],
                            'href' => '?page=membres&action=equipe&id=' . $equipe['id'],
                            'variant' => 'primary',
                            'size' => 'large'
                        ]);
                    }
                    echo '</div>';
                });
                ?>
                 <div style="margin: 3rem 0;"></div>
                <!-- Organigramme avec Directeur + Slider -->
                <?php
                Section::render([
                    'title' => 'Organigramme du Laboratoire',
                    'icon' => 'fas fa-sitemap'
                ], function () use ($directeur, $autresMembres) {
                    // Directeur
                    if ($directeur) {
                        $photoUrl = $directeur['photo'] ? UPLOADS_URL . 'photos/' . $directeur['photo'] : null;

                        echo '<div style="margin-bottom: 3rem;">';
                        ProfileCard::render([
                            'photo' => $photoUrl,
                            'name' => $directeur['nom'] . ' ' . $directeur['prenom'],
                            'title' => $directeur['poste'],
                            'subtitle' => $directeur['grade'],
                            'email' => $directeur['email'],
                            'layout' => 'horizontal',
                            'size' => 'large',
                            'actions' => [
                                [
                                    'text' => 'Biographie',
                                    'icon' => 'fas fa-user',
                                    'href' => '?page=membres&action=biographie&id=' . $directeur['id_membre']
                                ],
                                [
                                    'text' => 'Publications',
                                    'icon' => 'fas fa-file-alt',
                                    'href' => '?page=membres&action=publications&id=' . $directeur['id_membre']
                                ]
                            ]
                        ]);
                        echo '</div>';
                    }

                   

                                              // Bouton "Voir tous"
                        echo '<div style="text-align: center; margin-top: 2.5rem;">';
                        Button::render([
                            'text' => 'Voir tous les membres',
                            'icon' => 'fas fa-users',
                            'variant' => 'primary',
                            'href' => '?page=membres&action=tous',
                            'size' => 'large'
                        ]);
                        echo '</div>';
                });
                ?>

                <!-- Research Teams (Cards) -->
                <?php
                Section::render([
                    'title' => 'Détails des Équipes',
                    'icon' => 'fas fa-users-cog'
                ], function () use ($equipes) {
                    Grid::render(['minWidth' => '350px', 'gap' => '2rem'], function () use ($equipes) {
                        foreach ($equipes as $equipe) {
                            $this->renderEquipeCard($equipe);
                        }
                    });
                });
                ?>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    private function renderEquipeCard($equipe)
{
    $chefPhotoUrl = $equipe['chef_photo'] ? UPLOADS_URL . 'photos/' . $equipe['chef_photo'] : null;
    ?>
    <div class="card">
        <!-- Header -->
        <div style="padding: 1.5rem 1.5rem; border-bottom: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
            <h3 style="margin: 0; font-size: 1.35rem; color: var(--dark-color); flex: 1;">
                <?= htmlspecialchars($equipe['nom']) ?>
            </h3>
            <?php
            Badge::render([
                'text' => $equipe['nb_membres'] . ' membres',
                'variant' => 'info',
                'size' => 'small'
            ]);
            ?>
        </div>

        <!-- Content -->
        <div class="card-content" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.25rem;">
            <!-- Description -->
            <p class="card-description" style="margin: 0; color: var(--gray-700); line-height: 1.7; font-size: 0.95rem;">
                <?= htmlspecialchars($equipe['description'] ?? '') ?>
            </p>

            <?php if ($equipe['chef_nom']): ?>
                <!-- Team Leader -->
                <div style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem; background: var(--gray-50, #f9fafb); border-radius: 8px;">
                    <?php
                    Avatar::render([
                        'src' => $chefPhotoUrl,
                        'alt' => $equipe['chef_nom'],
                        'size' => 'medium'
                    ]);
                    ?>
                    <div>
                        <strong style="display: block; color: var(--gray-500); font-size: 0.8rem; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            Chef d'équipe
                        </strong>
                        <p style="margin: 0; color: var(--dark-color); font-weight: 600; font-size: 1rem;">
                            <?= htmlspecialchars($equipe['chef_nom'] . ' ' . $equipe['chef_prenom']) ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action -->
            <div style="padding-top: 1rem; margin-top: auto; border-top: 1px solid var(--gray-200);">
                <?php
                Button::render([
                    'text' => 'Voir l\'équipe',
                    'icon' => 'fas fa-arrow-right',
                    'variant' => 'primary',
                    'href' => '?page=membres&action=equipe&id=' . $equipe['id'],
                    'block' => true
                ]);
                ?>
            </div>
        </div>
    </div>
    <?php
}

    /**
     * Team details page
     */
    public function renderEquipe($equipe, $membres, $publications)
    {
        $this->pageTitle = $equipe['nom'];
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Breadcrumb -->
                <?php
                Breadcrumb::render([
                    ['text' => 'Accueil', 'url' => '?page=accueil'],
                    ['text' => 'Membres', 'url' => '?page=membres'],
                    ['text' => $equipe['nom']]
                ]);
                ?>

                <!-- Team Header -->
                <div style="margin-bottom: 2rem;">
                    <h1 style="font-size: 2.5rem; margin-bottom: 0.75rem; color: var(--dark-color);">
                        <?= htmlspecialchars($equipe['nom']) ?>
                    </h1>
                    <p style="font-size: 1.1rem; color: var(--gray-600); line-height: 1.6;">
                        <?= htmlspecialchars($equipe['description'] ?? '') ?>
                    </p>
                </div>

                <!-- Team Leader -->
                <?php if ($equipe['chef_nom']): ?>
                    <?php
                    Section::render([
                        'title' => 'Chef d\'équipe',
                        'icon' => 'fas fa-user-tie'
                    ], function () use ($equipe) {
                        $chefPhotoUrl = $equipe['chef_photo'] ? UPLOADS_URL . 'photos/' . $equipe['chef_photo'] : null;

                        ProfileCard::render([
                            'photo' => $chefPhotoUrl,
                            'name' => $equipe['chef_nom'] . ' ' . $equipe['chef_prenom'],
                            'subtitle' => $equipe['chef_grade'] ?? '',
                            'email' => $equipe['chef_email'],
                            'layout' => 'horizontal',
                            'size' => 'medium',
                            'actions' => [
                                [
                                    'text' => 'Biographie',
                                    'icon' => 'fas fa-user',
                                    'href' => '?page=membres&action=biographie&id=' . $equipe['chef_id']
                                ],
                                [
                                    'text' => 'Publications',
                                    'icon' => 'fas fa-file-alt',
                                    'href' => '?page=membres&action=publications&id=' . $equipe['chef_id']
                                ]
                            ]
                        ]);
                    });
                    ?>
                <?php endif; ?>

                <!-- Team Members -->
                <?php
                Section::render([
                    'title' => 'Membres de l\'équipe',
                    'icon' => 'fas fa-users'
                ], function () use ($membres, $equipe) {
                    Grid::render(['minWidth' => '280px', 'gap' => '1.5rem'], function () use ($membres, $equipe) {
                        foreach ($membres as $membre) {
                            // Don't display team leader twice
                            if ($membre['id_membre'] != $equipe['chef_id']) {
                                $this->renderMembreCard($membre);
                            }
                        }
                    });
                });
                ?>

                <!-- Team Publications -->
                <!-- Team Publications -->
                <?php
                Section::render([
                    'title' => 'Publications de l\'équipe',
                    'icon' => 'fas fa-file-alt'
                ], function () use ($equipe) {
                    echo '<div style="text-align: center;">';

                    Button::render([
                        'text' => 'Voir toutes les publications de l\'équipe',
                        'icon' => 'fas fa-arrow-right',
                        'variant' => 'primary',
                        'href' => '?page=membres&action=publications-equipe&id=' . $equipe['id']
                    ]);

                    echo '</div>';
                });
                ?>

            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    /**
     * Render member card
     */
    private function renderMembreCard($membre)
    {
        $photoUrl = $membre['photo'] ? UPLOADS_URL . 'photos/' . $membre['photo'] : null;
        ?>
        <div class="card" style="text-align: center;">
            <div class="card-content">
                <div style="display: flex; justify-content: center; margin-bottom: 1rem;">
                    <?php
                    Avatar::render([
                        'src' => $photoUrl,
                        'alt' => $membre['nom'] . ' ' . $membre['prenom'],
                        'size' => 'large'
                    ]);
                    ?>
                </div>

                <h4 style="margin: 0 0 0.5rem 0; font-size: 1.1rem; color: var(--dark-color);">
                    <?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?>
                </h4>

                <?php if (!empty($membre['poste'])): ?>
                    <p style="margin: 0 0 0.25rem 0; color: var(--primary-color); font-weight: 600; font-size: 0.9rem;">
                        <?= htmlspecialchars($membre['poste']) ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($membre['grade'])): ?>
                    <p style="margin: 0; color: var(--gray-600); font-size: 0.85rem;">
                        <?= htmlspecialchars($membre['grade']) ?>
                    </p>
                <?php endif; ?>

                <!-- Actions -->
                <div
                    style="display: flex; justify-content: center; gap: 0.75rem; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--gray-200);">
                    <a href="?page=membres&action=biographie&id=<?= $membre['id_membre'] ?>" title="Biographie"
                        style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background: var(--primary-color); color: white; transition: all 0.3s ease; text-decoration: none;">
                        <i class="fas fa-user"></i>
                    </a>
                    <a href="?page=membres&action=publications&id=<?= $membre['id_membre'] ?>" title="Publications"
                        style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background: var(--primary-color); color: white; transition: all 0.3s ease; text-decoration: none;">
                        <i class="fas fa-file-alt"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Member biography page
     */
    public function renderBiographie($membre, $equipes, $publications)
    {
        $this->pageTitle = $membre['nom'] . ' ' . $membre['prenom'];
        $this->renderHeader();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Breadcrumb -->
                <?php
                Breadcrumb::render([
                    ['text' => 'Accueil', 'url' => '?page=accueil'],
                    ['text' => 'Membres', 'url' => '?page=membres'],
                    ['text' => $membre['nom'] . ' ' . $membre['prenom']]
                ]);
                ?>

                <!-- Profile Header -->
                <?php
                $photoUrl = $membre['photo'] ? UPLOADS_URL . 'photos/' . $membre['photo'] : null;

                ProfileCard::render([
                    'photo' => $photoUrl,
                    'name' => $membre['nom'] . ' ' . $membre['prenom'],
                    'title' => $membre['poste'] ?? '',
                    'subtitle' => $membre['grade'] ?? '',
                    'email' => $membre['email'],
                    'layout' => 'horizontal',
                    'size' => 'large',
                    'cssClass' => 'bio-header'
                ]);
                ?>

                <!-- Bio Content -->
                <div style="margin-top: 2rem;">
                    <?php if ($membre['biographie']): ?>
                        <?php
                        Section::render([
                            'title' => 'Biographie',
                            'icon' => 'fas fa-user'
                        ], function () use ($membre) {
                            echo '<div style="padding: 1.5rem; background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
                            echo '<p style="line-height: 1.8; color: var(--gray-700); margin: 0;">';
                            echo nl2br(htmlspecialchars($membre['biographie']));
                            echo '</p></div>';
                        });
                        ?>
                    <?php endif; ?>

                    <?php if ($membre['domaine_recherche']): ?>
                        <?php
                        Section::render([
                            'title' => 'Domaines de recherche',
                            'icon' => 'fas fa-search'
                        ], function () use ($membre) {
                            echo '<div style="padding: 1.5rem; background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
                            echo '<p style="line-height: 1.8; color: var(--gray-700); margin: 0;">';
                            echo nl2br(htmlspecialchars($membre['domaine_recherche']));
                            echo '</p></div>';
                        });
                        ?>
                    <?php endif; ?>

                    <?php if (!empty($equipes)): ?>
                        <?php
                        Section::render([
                            'title' => 'Équipes',
                            'icon' => 'fas fa-users'
                        ], function () use ($equipes) {
                            echo '<div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">';
                            foreach ($equipes as $eq) {
                                Tag::render([
                                    'text' => $eq['nom'],
                                    'href' => '?page=membres&action=equipe&id=' . $eq['id'],
                                    'variant' => 'primary',
                                    'size' => 'medium'
                                ]);
                            }
                            echo '</div>';
                        });
                        ?>
                    <?php endif; ?>

                    <?php
                    Section::render([
                        'title' => 'Publications (' . count($publications) . ')',
                        'icon' => 'fas fa-file-alt'
                    ], function () use ($membre) {
                        Button::render([
                            'text' => 'Voir toutes les publications',
                            'icon' => 'fas fa-arrow-right',
                            'variant' => 'primary',
                            'href' => '?page=membres&action=publications&id=' . $membre['id_membre']
                        ]);
                    });
                    ?>
                </div>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    /**
     * Member publications page
     */
    public function renderPublications($membre, $publications)
    {
        $this->pageTitle = 'Publications de ' . $membre['nom'] . ' ' . $membre['prenom'];
        $this->renderHeader();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Breadcrumb -->
                <?php
                Breadcrumb::render([
                    ['text' => 'Accueil', 'url' => '?page=accueil'],
                    ['text' => 'Membres', 'url' => '?page=membres'],
                    ['text' => $membre['nom'] . ' ' . $membre['prenom'], 'url' => '?page=membres&action=biographie&id=' . $membre['id_membre']],
                    ['text' => 'Publications']
                ]);
                ?>

                <!-- Page Header -->
                <div class="page-header">
                    <h1>
                        <i class="fas fa-file-alt"></i>
                        Publications de
                        <?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?>
                    </h1>
                    <p class="subtitle">
                        <?= count($publications) ?> publication(s)
                    </p>
                </div>

                <!-- Publications List -->
                <div>
                    <?php
                    require_once __DIR__ . '/PublicationView.php';
                    $pubView = new PublicationView();
                    $pubView->renderPublicationsList($publications);
                    ?>
                </div>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    /**
     * Page "Tous les membres" avec filtres
     */
    public function renderTousLesMembres($membres, $grades, $postes, $equipes, $filters)
    {
        $this->pageTitle = 'Tous les membres';
        $this->renderHeader();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Breadcrumb -->
                <?php
                Breadcrumb::render([
                    ['text' => 'Accueil', 'url' => '?page=accueil'],
                    ['text' => 'Membres', 'url' => '?page=membres'],
                    ['text' => 'Tous les membres']
                ]);
                ?>

                <!-- Page Header -->
                <div class="page-header">
                    <h1><i class="fas fa-users"></i> Tous les membres du laboratoire</h1>
                    <p class="subtitle">
                        <?= count($membres) ?> membre(s)
                    </p>
                </div>

                <!-- Filters -->
                <?php
                FilterBar::render([], function () use ($grades, $postes, $equipes) {
                    // Grade Filter
                    $gradeOptions = [];
                    foreach ($grades as $g) {
                        $gradeOptions[] = ['value' => $g['grade'], 'text' => $g['grade']];
                    }
                    Filter::render([
                        'id' => 'filter-grade',
                        'label' => 'Grade',
                        'icon' => 'fas fa-graduation-cap',
                        'options' => $gradeOptions,
                        'placeholder' => 'Tous les grades'
                    ]);

                    // Poste Filter
                    $posteOptions = [];
                    foreach ($postes as $p) {
                        $posteOptions[] = ['value' => $p['poste'], 'text' => $p['poste']];
                    }
                    Filter::render([
                        'id' => 'filter-poste',
                        'label' => 'Poste',
                        'icon' => 'fas fa-briefcase',
                        'options' => $posteOptions,
                        'placeholder' => 'Tous les postes'
                    ]);

                });
                ?>

                <!-- Members Grid -->
                <?php
                Grid::render(['minWidth' => '280px', 'gap' => '1.5rem'], function () use ($membres) {
                    foreach ($membres as $membre) {
                        $this->renderMembreCard($membre);
                    }
                });
                ?>
            </div>
        </main>

        <script>
            // Filter handling
            const filters = {
                grade: document.getElementById('filter-grade'),
                poste: document.getElementById('filter-poste'),
                reset: document.getElementById('reset-filters')
            };

            function applyFilters() {
                const params = new URLSearchParams(window.location.search);
                params.set('page', 'membres');
                params.set('action', 'tous');

                if (filters.grade.value) params.set('grade', filters.grade.value);
                else params.delete('grade');

                if (filters.poste.value) params.set('poste', filters.poste.value);
                else params.delete('poste');

               

                window.location.href = '?' + params.toString();
            }

            filters.grade.addEventListener('change', applyFilters);
            filters.poste.addEventListener('change', applyFilters);
           

            filters.reset?.addEventListener('click', () => {
                window.location.href = '?page=membres&action=tous';
            });

            // Set current filter values
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('grade')) filters.grade.value = urlParams.get('grade');
            if (urlParams.has('poste')) filters.poste.value = urlParams.get('poste');
            
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Publications d'une équipe complète
     */
    public function renderPublicationsEquipe($equipe, $publications)
    {
        $this->pageTitle = 'Publications de ' . $equipe['nom'];
        $this->renderHeader();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Breadcrumb -->
                <?php
                Breadcrumb::render([
                    ['text' => 'Accueil', 'url' => '?page=accueil'],
                    ['text' => 'Membres', 'url' => '?page=membres'],
                    ['text' => $equipe['nom'], 'url' => '?page=membres&action=equipe&id=' . $equipe['id']],
                    ['text' => 'Publications']
                ]);
                ?>

                <!-- Page Header -->
                <div class="page-header">
                    <h1>
                        <i class="fas fa-file-alt"></i>
                        Publications de l'équipe
                        <?= htmlspecialchars($equipe['nom']) ?>
                    </h1>
                    <p class="subtitle">
                        <?= count($publications) ?> publication(s)
                    </p>
                </div>

                <!-- Publications List -->
                <div>
                    <?php
                    require_once __DIR__ . '/PublicationView.php';
                    $pubView = new PublicationView();
                    $pubView->renderPublicationsList($publications);
                    ?>
                </div>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }
}
?>