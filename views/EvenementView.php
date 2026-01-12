<?php
require_once __DIR__ . '/BaseView.php';

// Import components
require_once __DIR__ . '/components/Card.php';
require_once __DIR__ . '/components/Grid.php';
require_once __DIR__ . '/components/Section.php';
require_once __DIR__ . '/components/Filter.php';
require_once __DIR__ . '/components/FilterBar.php';
require_once __DIR__ . '/components/Badge.php';
require_once __DIR__ . '/components/Breadcrumb.php';
require_once __DIR__ . '/components/EmptyState.php';

class EvenementView extends BaseView
{
    public function __construct()
    { parent::__construct();
        $this->currentPage = 'evenements';
        $this->pageTitle = 'Événements';
    }

    /**
     * Render events list page
     */
    public function renderListe($evenements, $types)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Page Header -->
                <div class="page-header">
                    <h1><i class="fas fa-calendar-alt"></i> Événements du Laboratoire</h1>
                    <p class="subtitle">Conférences, ateliers, séminaires et soutenances</p>
                </div>

                <!-- Filters -->
                <?php
                FilterBar::render([
                    'resetText' => 'Réinitialiser',
                    'resetId' => 'reset-filters'
                ], function () use ($types) {
                    // Type Filter
                    Filter::render([
                        'id' => 'filter-type',
                        'label' => 'Type d\'événement',
                        'icon' => 'fas fa-tag',
                        'placeholder' => 'Tous les types',
                        'options' => array_map(function ($t) {
                            return ['value' => $t['id_type_evenement'], 'text' => $t['nom_type']];
                        }, $types)
                    ]);

                    // Status Filter
                    Filter::render([
                        'id' => 'filter-statut',
                        'label' => 'Statut',
                        'icon' => 'fas fa-clock',
                        'placeholder' => 'Tous les statuts',
                        'options' => [
                            ['value' => 'à venir', 'text' => 'À venir'],
                            ['value' => 'en cours', 'text' => 'En cours'],
                            ['value' => 'terminé', 'text' => 'Terminé']
                        ]
                    ]);
 });
                 
                ?>

                <!-- Events Container -->
<div id="evenements-container" style="margin-top: 2rem;">
    <?php $this->renderEvenementsCards($evenements); ?>
</div>

                <!-- Loading State -->
                <div id="loading" class="loading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Chargement...
                </div>
            </div>
        </main>

      <script>
    // Filter functionality
    document.addEventListener('DOMContentLoaded', function () {
        const filterType = document.getElementById('filter-type');
        const filterStatut = document.getElementById('filter-statut');
        const resetBtn = document.getElementById('reset-filters');
        const container = document.getElementById('evenements-container');
        const loading = document.getElementById('loading');

        function applyFilters() {
            const type = filterType.value;
            const statut = filterStatut.value;

            loading.style.display = 'block';
            container.style.opacity = '0.5';

            const params = new URLSearchParams({
                type: type,
                statut: statut
            });

            fetch(`?page=evenements&action=filter&${params}`)
                .then(response => response.json())
                .then(data => {
                    container.innerHTML = data.html;
                    loading.style.display = 'none';
                    container.style.opacity = '1';
                })
                .catch(error => {
                    console.error('Error:', error);
                    loading.style.display = 'none';
                    container.style.opacity = '1';
                });
        }

        filterType.addEventListener('change', applyFilters);
        filterStatut.addEventListener('change', applyFilters);

        resetBtn.addEventListener('click', function () {
            filterType.value = '';
            filterStatut.value = '';
            applyFilters();
        });
    });
</script>

        <?php
        $this->renderFooter();
    }

    /**
     * Render event cards
     */
    public function renderEvenementsCards($evenements)
    {
        if (empty($evenements)) {
            EmptyState::render([
                'icon' => 'fas fa-calendar-times',
                'title' => 'Aucun événement trouvé',
                'description' => 'Essayez de modifier vos filtres pour voir plus de résultats'
            ]);
            return;
        }

        Grid::render(['minWidth' => '350px', 'gap' => '1.5rem'], function () use ($evenements) {
            foreach ($evenements as $event) {
                $this->renderEventCard($event);
            }
        });
    }

    /**
     * Render single event card
     */
  private function renderEventCard($event)
{
    $imageUrl = !empty($event['image']) ? UPLOADS_URL . 'evenements/' . $event['image'] : null;
    $dateDebut = new DateTime($event['date_debut']);
    $dateFin = $event['date_fin'] ? new DateTime($event['date_fin']) : null;

    $capaciteText = '';
    if ($event['capacite_max']) {
        $capaciteText = $event['nb_inscrits'] . '/' . $event['capacite_max'] . ' inscrits';
    } else {
        $capaciteText = $event['nb_inscrits'] . ' inscrit(s)';
    }

    ?>
    <div class="card" style="display: flex; flex-direction: column;">
        <!-- Header with badges -->
        <div style="padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; border-bottom: 1px solid var(--gray-200);">
            <?php
            Badge::render([
                'text' => $event['type_nom'] ?? 'Événement',
                'color' => $event['type_couleur'] ?? '#007bff',
                'size' => 'small'
            ]);

            Badge::render([
                'text' => ucfirst($event['statut']),
                'variant' => $this->getStatusVariant($event['statut']),
                'size' => 'small'
            ]);
            ?>
        </div>

        <?php if ($imageUrl): ?>
            <div class="card-image" style="background-image: url('<?= htmlspecialchars($imageUrl) ?>'); height: 200px; background-size: cover; background-position: center;"></div>
        <?php endif; ?>

        <div class="card-content" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; flex: 1;">
            <!-- Title -->
            <h3 class="card-title" style="margin: 0; font-size: 1.25rem; line-height: 1.4;">
                <a href="?page=evenements&action=details&id=<?= $event['id_evenement'] ?>"
                    style="text-decoration: none; color: var(--dark-color);">
                    <?= htmlspecialchars($event['titre']) ?>
                </a>
            </h3>

            <!-- Description -->
            <p class="card-description" style="margin: 0; color: var(--gray-600); line-height: 1.6; flex: 1;">
                <?= htmlspecialchars(mb_substr($event['description'] ?? '', 0, 120)) ?>...
            </p>

            <!-- Footer Info -->
            <div class="card-footer" style="display: flex; flex-direction: column; gap: 0.75rem; padding: 1rem 0; border-top: 1px solid var(--gray-200); margin-top: auto;">
                <span style="display: flex; align-items: center; gap: 0.5rem; color: var(--gray-600); font-size: 0.9rem;">
                    <i class="fas fa-calendar" style="width: 16px;"></i>
                    <?= $dateDebut->format('d/m/Y H:i') ?>
                </span>

                <?php if ($event['lieu']): ?>
                    <span style="display: flex; align-items: center; gap: 0.5rem; color: var(--gray-600); font-size: 0.9rem;">
                        <i class="fas fa-map-marker-alt" style="width: 16px;"></i>
                        <?= htmlspecialchars($event['lieu']) ?>
                    </span>
                <?php endif; ?>

                <span style="display: flex; align-items: center; gap: 0.5rem; color: var(--gray-600); font-size: 0.9rem;">
                    <i class="fas fa-users" style="width: 16px;"></i>
                    <?= htmlspecialchars($capaciteText) ?>
                </span>
            </div>

            <!-- Link -->
            <a href="?page=evenements&action=details&id=<?= $event['id_evenement'] ?>" 
               class="card-link" 
               style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--primary-color); font-weight: 600; text-decoration: none;">
                Voir les détails
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    <?php
}
    /**
     * Render event details page
     */
    public function renderDetails($evenement, $participants, $isInscrit)
    {
        $this->pageTitle = $evenement['titre'] . ' - Événement';
        $this->renderHeader();
        $this->renderFlashMessage();

        $dateDebut = new DateTime($evenement['date_debut']);
        $dateFin = $evenement['date_fin'] ? new DateTime($evenement['date_fin']) : null;
        $imageUrl = !empty($evenement['image']) ? UPLOADS_URL . 'evenements/' . $evenement['image'] : null;

        $isAvenir = $evenement['statut'] === 'à venir';
        $isFull = $evenement['capacite_max'] && $evenement['nb_inscrits'] >= $evenement['capacite_max'];
        $canRegister = $isAvenir && !$isFull && !$isInscrit && isset($_SESSION['user']);

        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Breadcrumb -->
                <?php
                Breadcrumb::render([
                    ['text' => 'Accueil', 'url' => '?page=accueil'],
                    ['text' => 'Événements', 'url' => '?page=evenements'],
                    ['text' => $evenement['titre']]
                ]);
                ?>

                <div class="event-details">
                    <?php if ($imageUrl): ?>
                        <div
                            style="width: 100%; height: 400px; background: url('<?= htmlspecialchars($imageUrl) ?>') center/cover; border-radius: 12px; margin-bottom: 2rem;">
                        </div>
                    <?php endif; ?>

                    <!-- Header -->
                    <div class="details-header" style="margin-bottom: 2rem;">
                        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                            <?php
                            Badge::render([
                                'text' => $evenement['type_nom'] ?? 'Événement',
                                'color' => $evenement['type_couleur'] ?? '#007bff',
                                'size' => 'large'
                            ]);

                            Badge::render([
                                'text' => ucfirst($evenement['statut']),
                                'variant' => $this->getStatusVariant($evenement['statut']),
                                'size' => 'large'
                            ]);
                            ?>
                        </div>

                        <h1 style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--dark-color);">
                            <?= htmlspecialchars($evenement['titre']) ?>
                        </h1>

                        <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; color: var(--gray-600);">
                            <span style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-calendar"></i>
                                <?= $dateDebut->format('d/m/Y à H:i') ?>
                                <?php if ($dateFin): ?>
                                    -
                                    <?= $dateFin->format('d/m/Y à H:i') ?>
                                <?php endif; ?>
                            </span>

                            <?php if ($evenement['lieu']): ?>
                                <span style="display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars($evenement['lieu']) ?>
                                </span>
                            <?php endif; ?>

                            <span style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-users"></i>
                                <?= $evenement['nb_inscrits'] ?> inscrit(s)
                                <?php if ($evenement['capacite_max']): ?>
                                    /
                                    <?= $evenement['capacite_max'] ?> places
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <!-- Main Content Grid -->
                    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem; align-items: start;">
                        <!-- Left Column -->
                        <div>
                            <!-- Description -->
                            <?php
                            Section::render([
                                'title' => 'Description',
                                'icon' => 'fas fa-align-left'
                            ], function () use ($evenement) {
                                echo '<div style="padding: 1.5rem; background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
                                echo '<p style="line-height: 1.8; color: var(--gray-700); white-space: pre-wrap;">';
                                echo nl2br(htmlspecialchars($evenement['description'] ?? ''));
                                echo '</p></div>';
                            });
                            ?>

                            <!-- Location Details -->
                            <?php if ($evenement['adresse']): ?>
                                <?php
                                Section::render([
                                    'title' => 'Lieu et accès',
                                    'icon' => 'fas fa-map-marker-alt'
                                ], function () use ($evenement) {
                                    echo '<div style="padding: 1.5rem; background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
                                    echo '<p style="line-height: 1.8; color: var(--gray-700);">';
                                    echo nl2br(htmlspecialchars($evenement['adresse']));
                                    echo '</p></div>';
                                });
                                ?>
                            <?php endif; ?>

                            <!-- Participants -->
                            <?php if (!empty($participants)): ?>
                                <?php
                                Section::render([
                                    'title' => 'Participants inscrits',
                                    'icon' => 'fas fa-users'
                                ], function () use ($participants) {
                                    echo '<div style="padding: 1.5rem; background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
                                    echo '<div style="display: flex; flex-wrap: wrap; gap: 1rem;">';

                                    foreach ($participants as $participant) {
                                        $nom = $participant['nom'] ?? 'Participant';
                                        $prenom = $participant['prenom'] ?? '';

                                        $photoUrl = !empty($participant['photo']) ? UPLOADS_URL . 'photos/' . $participant['photo'] : null;

                                        echo '<div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background: #f8f9fa; border-radius: 8px; min-width: 200px;">';

                                        if ($photoUrl) {
                                            echo '<img src="' . htmlspecialchars($photoUrl) . '" alt="' . htmlspecialchars($nom) . '" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">';
                                        } else {
                                            echo '<div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold;">';
                                            echo strtoupper(substr($nom, 0, 1) . substr($prenom, 0, 1));
                                            echo '</div>';
                                        }

                                        echo '<div style="flex: 1;">';
                                        echo '<div style="font-weight: 600; color: var(--dark-color);">' . htmlspecialchars($nom . ' ' . $prenom) . '</div>';
                                        if (!empty($participant['grade'])) {
                                            echo '<div style="font-size: 0.85rem; color: var(--gray-600);">' . htmlspecialchars($participant['grade']) . '</div>';
                                        }
                                        echo '</div>';
                                        echo '</div>';
                                    }

                                    echo '</div></div>';
                                });
                                ?>
                            <?php endif; ?>
                        </div>

                        <!-- Right Column: Sidebar -->
                        <div>
                            <!-- Registration Box -->
                            <div
                                style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 1.5rem;">
                                <h3 style="margin: 0 0 1rem 0; color: var(--dark-color);">
                                    <i class="fas fa-ticket-alt"></i> Inscription
                                </h3>

                                <?php if ($isInscrit): ?>
                                    <div
                                        style="padding: 1rem; background: #d4edda; border-left: 4px solid #28a745; border-radius: 4px; margin-bottom: 1rem;">
                                        <p style="margin: 0; color: #155724;">
                                            <i class="fas fa-check-circle"></i> Vous êtes inscrit à cet événement
                                        </p>
                                    </div>
                                    <form method="POST" action="?page=evenements&action=annuler"
                                        onsubmit="return confirm('Êtes-vous sûr de vouloir annuler votre inscription ?');">
                                        <input type="hidden" name="id_evenement" value="<?= $evenement['id_evenement'] ?>">
                                        <button type="submit" class="btn btn-outline" style="width: 100%;">
                                            <i class="fas fa-times"></i> Annuler l'inscription
                                        </button>
                                    </form>
                                <?php elseif (!isset($_SESSION['user'])): ?>
                                    <p style="color: var(--gray-600); margin-bottom: 1rem;">Connectez-vous pour vous inscrire à cet
                                        événement.</p>
                                    <a href="?page=login" class="btn btn-primary"
                                        style="width: 100%; text-align: center; display: block;">
                                        <i class="fas fa-sign-in-alt"></i> Se connecter
                                    </a>
                                <?php elseif ($canRegister): ?>
                                    <form method="POST" action="?page=evenements&action=inscrire">
                                        <input type="hidden" name="id_evenement" value="<?= $evenement['id_evenement'] ?>">
                                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                                            <i class="fas fa-check"></i> S'inscrire à l'événement
                                        </button>
                                    </form>
                                <?php elseif ($isFull): ?>
                                    <div
                                        style="padding: 1rem; background: #f8d7da; border-left: 4px solid #dc3545; border-radius: 4px;">
                                        <p style="margin: 0; color: #721c24;">
                                            <i class="fas fa-exclamation-triangle"></i> Événement complet
                                        </p>
                                    </div>
                                <?php elseif (!$isAvenir): ?>
                                    <div
                                        style="padding: 1rem; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                                        <p style="margin: 0; color: #856404;">
                                            <i class="fas fa-info-circle"></i> Les inscriptions sont fermées
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <?php if ($evenement['lien_inscription']): ?>
                                    <a href="<?= htmlspecialchars($evenement['lien_inscription']) ?>" target="_blank"
                                        class="btn btn-outline"
                                        style="width: 100%; text-align: center; display: block; margin-top: 0.5rem;">
                                        <i class="fas fa-external-link-alt"></i> Lien d'inscription externe
                                    </a>
                                <?php endif; ?>
                            </div>

                            <!-- Organizer Info -->
                            <?php if ($evenement['organisateur_nom']): ?>
                                <div
                                    style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                    <h3 style="margin: 0 0 1rem 0; color: var(--dark-color);">
                                        <i class="fas fa-user-tie"></i> Organisateur
                                    </h3>

                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <?php
                                        $orgPhotoUrl = !empty($evenement['organisateur_photo']) ? UPLOADS_URL . 'photos/' . $evenement['organisateur_photo'] : null;

                                        if ($orgPhotoUrl): ?>
                                            <img src="<?= htmlspecialchars($orgPhotoUrl) ?>"
                                                alt="<?= htmlspecialchars($evenement['organisateur_nom']) ?>"
                                                style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                                        <?php else: ?>
                                            <div
                                                style="width: 60px; height: 60px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.5rem;">
                                                <?= strtoupper(substr($evenement['organisateur_nom'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>

                                        <div style="flex: 1;">
                                            <div style="font-weight: 600; color: var(--dark-color);">
                                                <?= htmlspecialchars($evenement['organisateur_nom'] . ' ' . $evenement['organisateur_prenom']) ?>
                                            </div>
                                            <?php if ($evenement['organisateur_email']): ?>
                                                <a href="mailto:<?= htmlspecialchars($evenement['organisateur_email']) ?>"
                                                    style="color: var(--primary-color); font-size: 0.9rem; text-decoration: none;">
                                                    <i class="fas fa-envelope"></i>
                                                    <?= htmlspecialchars($evenement['organisateur_email']) ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    /**
     * Get badge variant for status
     */
    private function getStatusVariant($statut)
    {
        switch ($statut) {
            case 'à venir':
                return 'info';
            case 'en cours':
                return 'success';
            case 'terminé':
                return 'default';
            case 'annulé':
                return 'danger';
            default:
                return 'default';
        }
    }
}
?>