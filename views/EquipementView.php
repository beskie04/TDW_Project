<?php
require_once __DIR__ . '/BaseView.php';

// Import  Components
require_once __DIR__ . '/components/Filter.php';
require_once __DIR__ . '/components/FilterBar.php';
require_once __DIR__ . '/components/SearchInput.php';
require_once __DIR__ . '/components/Badge.php';
require_once __DIR__ . '/components/Button.php';
require_once __DIR__ . '/components/Alert.php';
require_once __DIR__ . '/components/Grid.php';
require_once __DIR__ . '/components/EmptyState.php';
require_once __DIR__ . '/components/Breadcrumb.php';
require_once __DIR__ . '/components/Section.php';
require_once __DIR__ . '/components/InfoList.php';
require_once __DIR__ . '/components/FormGroup.php';
require_once __DIR__ . '/components/FormInput.php';
require_once __DIR__ . '/components/FormActions.php';
require_once __DIR__ . '/components/StatCard.php';
require_once __DIR__ . '/components/Table.php';

class EquipementView extends BaseView
{
    public function __construct()
    {
         parent::__construct();
        $this->currentPage = 'equipements';
        $this->pageTitle = 'Équipements et Ressources';
    }

    /**
     * Render equipment list page
     */
    public function renderListe($equipements, $types, $etats, $mesReservations = [], $stats = [], $mesEquipementsReserves = [])
    {
        $this->renderHeader();
        $this->renderFlashMessage();

        $isLoggedIn = isset($_SESSION['user']);
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1><i class="fas fa-tools"></i> Gestion des Équipements et Ressources</h1>
                        <p class="subtitle">Consultez et réservez les équipements du laboratoire</p>
                    </div>
                    <?php if ($isLoggedIn): ?>
                        <?php
                        Button::render([
                            'text' => 'Mon historique',
                            'icon' => 'fas fa-history',
                            'variant' => 'secondary',
                            'href' => '?page=equipements&action=historique'
                        ]);
                        ?>
                    <?php endif; ?>
                </div>

               <!-- Statistics Cards  -->
<?php if (!empty($stats)): ?>
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <?php
        // Calculate available equipment
        $libres = 0;
        foreach ($stats['par_etat'] as $etat) {
            if ($etat['etat'] === 'libre') {
                $libres = $etat['total'];
                break;
            }
        }
        ?>
        
        <!-- Total Equipment Card -->
        <div class="stat-card stat-primary">
            <div class="stat-icon">
                <i class="fas fa-tools"></i>
            </div>
            <div class="stat-content">
                <h3><?= $stats['total'] ?></h3>
                <p>Total Équipements</p>
            </div>
        </div>

        <!-- In Use Card -->
        <div class="stat-card stat-success">
            <div class="stat-icon">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="stat-content">
                <h3><?= $stats['en_utilisation'] ?></h3>
                <p>En utilisation</p>
                <small><?= $stats['taux_occupation'] ?>% occupation</small>
            </div>
        </div>

        <!-- Available Card -->
        <div class="stat-card stat-info">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?= $libres ?></h3>
                <p>Disponibles</p>
                <small>équipements libres</small>
            </div>
        </div>
    </div>

    <style>
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            flex-shrink: 0;
        }

        .stat-primary .stat-icon {
            background: #ffffff;
        }

        .stat-success .stat-icon {
            background: #ffffff;
        }

        .stat-info .stat-icon {
            background: #ffffff;
        }

        .stat-content {
            flex: 1;
        }

        .stat-content h3 {
            font-size: 2rem;
            font-weight: bold;
            color: #1f2937;
            margin: 0 0 0.25rem 0;
            line-height: 1;
        }

        .stat-content p {
            color: #6b7280;
            margin: 0;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .stat-content small {
            display: block;
            color: #f9fbfe;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
    </style>
<?php endif; ?>
                <!-- My Reservations Alert -->
                <?php if ($isLoggedIn && !empty($mesReservations)): ?>
                    <?php
                    Alert::render([
                        'title' => 'Mes réservations en cours (' . count($mesReservations) . ')',
                        'icon' => 'fas fa-calendar-check',
                        'variant' => 'info'
                    ], function () use ($mesReservations) {
                        Grid::render(['minWidth' => '300px', 'gap' => '1rem'], function () use ($mesReservations) {
                            foreach ($mesReservations as $res) {
                                $this->renderReservationCard($res);
                            }
                        });
                    });
                    ?>
                <?php endif; ?>

                <!-- Filters -->
                <?php
                FilterBar::render([
                    'resetText' => 'Réinitialiser',
                    'resetId' => 'reset-filters'
                ], function () use ($types, $etats) {
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

                    // Status Filter
                    Filter::render([
                        'id' => 'filter-etat',
                        'label' => 'État',
                        'icon' => 'fas fa-circle',
                        'placeholder' => 'Tous les états',
                        'options' => array_map(function ($key, $label) {
                            return ['value' => $key, 'text' => $label];
                        }, array_keys($etats), array_values($etats))
                    ]);

                    // Search
                    SearchInput::render([
                        'id' => 'search-input',
                        'label' => 'Rechercher',
                        'placeholder' => 'Nom, description...'
                    ]);
                });
                ?>

                <!-- Equipment Grid -->
                <div id="equipements-container" style="margin-top: 2rem;">
                    <?php $this->renderEquipementsList($equipements, $isLoggedIn, $mesEquipementsReserves); ?>
                </div>
                <!-- Loading State -->
                <div id="loading" class="loading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Chargement...
                </div>
            </div>
        </main>

        <script>
            function annulerReservation(id) {
                if (confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')) {
                    window.location.href = '?page=equipements&action=annuler&id=' + id;
                }
            }
        </script>
    <script>
    // Filter functionality
    const typeFilter = document.getElementById('filter-type');
    const etatFilter = document.getElementById('filter-etat');
    const searchInput = document.getElementById('search-input');
    const resetBtn = document.getElementById('reset-filters');
    const container = document.getElementById('equipements-container');
    const loading = document.getElementById('loading');

    // Debounce function for search
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Apply filters
    function applyFilters() {
        const type = typeFilter?.value || '';
        const etat = etatFilter?.value || '';
        const search = searchInput?.value || '';

        // Show loading
        container.style.opacity = '0.5';
        loading.style.display = 'block';

        // Build query string
        const params = new URLSearchParams({
            page: 'equipements',
            action: 'filter',
            type: type,
            etat: etat,
            search: search
        });

        // Fetch filtered results
        fetch('?' + params.toString())
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    container.innerHTML = data.html;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            })
            .finally(() => {
                container.style.opacity = '1';
                loading.style.display = 'none';
            });
    }

    // Event listeners
    if (typeFilter) {
        typeFilter.addEventListener('change', applyFilters);
    }

    if (etatFilter) {
        etatFilter.addEventListener('change', applyFilters);
    }

    if (searchInput) {
        searchInput.addEventListener('input', debounce(applyFilters, 500));
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            if (typeFilter) typeFilter.value = '';
            if (etatFilter) etatFilter.value = '';
            if (searchInput) searchInput.value = '';
            applyFilters();
        });
    }
</script>
        <?php
        $this->renderFooter();
    }

    /**
     * Render historique page
     */
    public function renderHistorique($reservations, $demandes)
    {
        $this->pageTitle = 'Mon Historique';
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Breadcrumb -->
                <?php
                Breadcrumb::render([
                    ['text' => 'Accueil', 'url' => '?page=accueil'],
                    ['text' => 'Équipements', 'url' => '?page=equipements'],
                    ['text' => 'Mon Historique']
                ]);
                ?>

                <!-- Page Header -->
                <div class="page-header">
                    <h1><i class="fas fa-history"></i> Mon Historique de Réservations</h1>
                </div>

                <!-- Demandes Prioritaires -->
                <?php if (!empty($demandes)): ?>
                    <?php
                    Section::render([
                        'title' => 'Demandes Prioritaires (' . count($demandes) . ')',
                        'icon' => 'fas fa-star'
                    ], function () use ($demandes) {
                        ?>
                        <div class="card">
                            <div class="card-content" style="padding: 0;">
                                <?php
                                Table::render(
                                    [
                                        'headers' => ['Équipement', 'Période', 'Statut', 'Justification', 'Date demande'],
                                        'rows' => $demandes,
                                        'striped' => true,
                                        'hoverable' => true
                                    ],
                                    function ($demande) {
                                        $statutVariant = [
                                            'en_attente' => 'warning',
                                            'approuvee' => 'success',
                                            'rejetee' => 'danger'
                                        ][$demande['statut']] ?? 'default';

                                        $statutLabel = [
                                            'en_attente' => 'En attente',
                                            'approuvee' => 'Approuvée',
                                            'rejetee' => 'Rejetée'
                                        ][$demande['statut']] ?? $demande['statut'];
                                        ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($demande['equipement_nom']) ?></strong>
                                            <br>
                                            <small style="color: var(--gray-600);">
                                                <?= htmlspecialchars(TYPES_EQUIPEMENTS[$demande['equipement_type']] ?? $demande['equipement_type']) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y H:i', strtotime($demande['date_debut'])) ?>
                                            <br>
                                            <small style="color: var(--gray-600);">
                                                <?= date('d/m/Y H:i', strtotime($demande['date_fin'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php
                                                Badge::render([
                                                    'text' => $statutLabel,
                                                    'variant' => $statutVariant
                                                ]);
                                                ?>
                                            <?php if ($demande['reponse_admin']): ?>
                                                <br>
                                                <small style="color: var(--gray-600); margin-top: 0.5rem; display: block;">
                                                    <?= htmlspecialchars($demande['reponse_admin']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td style="max-width: 300px;">
                                            <?= htmlspecialchars(substr($demande['justification'], 0, 100)) ?>
                                            <?= strlen($demande['justification']) > 100 ? '...' : '' ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($demande['created_at'])) ?></td>
                                    </tr>
                                    <?php
                                    }
                                );
                                ?>
                            </div>
                        </div>
                        <?php
                    });
                    ?>
                <?php endif; ?>

                <!-- Historique Réservations -->
                <?php
                Section::render([
                    'title' => 'Historique des Réservations (' . count($reservations) . ')',
                    'icon' => 'fas fa-calendar-alt'
                ], function () use ($reservations) {
                    ?>
                    <div class="card">
                        <div class="card-content" style="padding: 0;">
                            <?php if (!empty($reservations)): ?>
                                <?php
                                Table::render(
                                    [
                                        'headers' => ['Équipement', 'Type', 'Période', 'Statut', 'Créée le'],
                                        'rows' => $reservations,
                                        'striped' => true,
                                        'hoverable' => true
                                    ],
                                    function ($res) {
                                        $statutVariant = [
                                            'active' => 'success',
                                            'annulee' => 'danger',
                                            'terminee' => 'default'
                                        ][$res['statut']] ?? 'default';

                                        $isPast = strtotime($res['date_fin']) < time();
                                        ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($res['equipement_nom']) ?></strong>
                                        </td>
                                        <td>
                                            <?php
                                                Badge::render([
                                                    'text' => TYPES_EQUIPEMENTS[$res['equipement_type']] ?? $res['equipement_type'],
                                                    'variant' => 'primary',
                                                    'size' => 'small'
                                                ]);
                                                ?>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <i class="fas fa-calendar" style="color: var(--gray-500);"></i>
                                                <div>
                                                    <?= date('d/m/Y H:i', strtotime($res['date_debut'])) ?>
                                                    <br>
                                                    <small style="color: var(--gray-600);">
                                                        <?= date('d/m/Y H:i', strtotime($res['date_fin'])) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                                $statutLabel = $res['statut'];
                                                if ($res['statut'] === 'active' && $isPast) {
                                                    $statutLabel = 'Terminée';
                                                    $statutVariant = 'default';
                                                }

                                                Badge::render([
                                                    'text' => ucfirst($statutLabel),
                                                    'variant' => $statutVariant
                                                ]);
                                                ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($res['created_at'])) ?></td>
                                    </tr>
                                    <?php
                                    }
                                );
                                ?>
                            <?php else: ?>
                                <?php
                                EmptyState::render([
                                    'icon' => 'fas fa-calendar',
                                    'title' => 'Aucune réservation',
                                    'description' => 'Vous n\'avez pas encore effectué de réservation'
                                ]);
                                ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                });
                ?>

                <!-- Back Button -->
                <div style="text-align: center; margin-top: 2rem;">
                    <?php
                    Button::render([
                        'text' => 'Retour aux équipements',
                        'icon' => 'fas fa-arrow-left',
                        'variant' => 'secondary',
                        'href' => '?page=equipements'
                    ]);
                    ?>
                </div>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    /**
     * Render reservation card 
     */
    private function renderReservationCard($res)
    {
        ?>
        <div
            style="background: white; padding: 1.25rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #06b6d4;">
            <h4 style="margin: 0 0 0.75rem 0; color: var(--dark-color); font-size: 1.1rem;">
                <?= htmlspecialchars($res['equipement_nom']) ?>
            </h4>

            <p
                style="margin: 0 0 1rem 0; color: var(--gray-600); display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem;">
                <i class="fas fa-calendar"></i>
                <?= date('d/m/Y H:i', strtotime($res['date_debut'])) ?> -
                <?= date('d/m/Y H:i', strtotime($res['date_fin'])) ?>
            </p>

            <?php
            Button::render([
                'text' => 'Annuler',
                'icon' => 'fas fa-times',
                'variant' => 'danger',
                'size' => 'small',
                'onClick' => 'annulerReservation(' . $res['id'] . ')'
            ]);
            ?>
        </div>
        <?php
    }

    /**
     * Render equipment list
     */
    public function renderEquipementsList($equipements, $isLoggedIn = false, $mesEquipementsReserves = [])
    {
        if (empty($equipements)) {
            EmptyState::render([
                'icon' => 'fas fa-tools',
                'title' => 'Aucun équipement trouvé',
                'description' => 'Essayez de modifier vos filtres pour voir plus de résultats'
            ]);
            return;
        }

        Grid::render(['minWidth' => '320px', 'gap' => '1.5rem'], function () use ($equipements, $isLoggedIn, $mesEquipementsReserves) {
            foreach ($equipements as $eq) {
                $this->renderEquipementCard($eq, $isLoggedIn, $mesEquipementsReserves);
            }
        });
    }

    private function renderEquipementCard($eq, $isLoggedIn, $mesEquipementsReserves = [])
{
    $etatVariant = $this->getEtatVariant($eq['etat']);
    $etatLabel = ETATS_EQUIPEMENTS[$eq['etat']] ?? $eq['etat'];
    $typeLabel = TYPES_EQUIPEMENTS[$eq['type']] ?? $eq['type'];

    // Check if user already has a reservation for this equipment
    $userHasReservation = in_array($eq['id'], $mesEquipementsReserves);
    ?>
    <div class="card">
        <!-- Header with badges -->
        <div style="padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; border-bottom: 1px solid var(--gray-200);">
            <?php
            Badge::render([
                'text' => $typeLabel,
                'variant' => 'primary',
                'size' => 'small'
            ]);

            Badge::render([
                'text' => $etatLabel,
                'variant' => $etatVariant,
                'size' => 'small'
            ]);
            ?>
        </div>

        <!-- Content -->
        <div class="card-content" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
            <!-- Title -->
            <h3 class="card-title" style="margin: 0; font-size: 1.25rem; color: var(--dark-color);">
                <?= htmlspecialchars($eq['nom']) ?>
            </h3>

            <!-- Description -->
            <p class="card-description" style="margin: 0; color: var(--gray-600); line-height: 1.6; font-size: 0.95rem;">
                <?= htmlspecialchars($eq['description'] ?? '') ?>
            </p>

            <!-- Specifications -->
            <?php if (!empty($eq['specifications'])): ?>
                <div style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 1rem; background: var(--gray-50, #f9fafb); border-radius: 6px; border-left: 3px solid var(--primary-color);">
                    <i class="fas fa-info-circle" style="color: var(--primary-color); margin-top: 0.2rem; font-size: 1rem;"></i>
                    <p style="margin: 0; color: var(--gray-700); font-size: 0.9rem; line-height: 1.5;">
                        <?= htmlspecialchars($eq['specifications']) ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Actions -->
            <div style="display: flex; gap: 1rem; align-items: center; padding-top: 1rem; margin-top: auto; border-top: 1px solid var(--gray-200);">
                <?php if ($isLoggedIn): ?>
                    <?php if ($userHasReservation): ?>
                        <!-- User already has a reservation -->
                        <?php
                        Badge::render([
                            'text' => 'Vous avez réservé',
                            'variant' => 'success',
                            'size' => 'small'
                        ]);
                        ?>
                    <?php elseif ($eq['etat'] === 'libre'): ?>
                        <!-- Equipment available -->
                        <?php
                        Button::render([
                            'text' => 'Réserver',
                            'icon' => 'fas fa-calendar-plus',
                            'variant' => 'primary',
                            'size' => 'small',
                            'href' => '?page=equipements&action=reserver&id=' . $eq['id']
                        ]);
                        ?>
                   <?php elseif ($eq['etat'] === 'reserve'): ?>
    <!-- Equipment reserved by someone else  -->
    <?php
    Button::render([
        'text' => 'Demander',
        'variant' => 'secondary',
        'size' => 'small',
        'href' => '?page=equipements&action=reserver&id=' . $eq['id']
    ]);
    ?>
                    <?php else: ?>
                        <!-- In maintenance or other status -->
                        <?php
                        Button::render([
                            'text' => 'Non disponible',
                            'icon' => 'fas fa-ban',
                            'variant' => 'secondary',
                            'size' => 'small',
                            'disabled' => true
                        ]);
                        ?>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Not logged in -->
                    <?php
                    Button::render([
                        'text' => 'Connectez-vous',
                        'icon' => 'fas fa-sign-in-alt',
                        'variant' => 'secondary',
                        'size' => 'small',
                        'href' => '?page=login'
                    ]);
                    ?>
                <?php endif; ?>

                <!-- View details link -->
                <a href="?page=equipements&action=details&id=<?= $eq['id'] ?>" 
                   class="card-link" 
                   style="margin-left: auto; display: flex; align-items: center; gap: 0.5rem; color: var(--primary-color); text-decoration: none; font-weight: 500; font-size: 0.9rem; white-space: nowrap;">
                    Voir détails
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    <?php
}
    /**
     * Render reservation form page
     */
    public function renderReservation($equipement)
    {
        $this->pageTitle = 'Réserver - ' . $equipement['nom'];
        $this->renderHeader();
        $this->renderFlashMessage();

        // Vérifier si il y a un conflit
        $conflit = isset($_GET['conflit']) && isset($_SESSION['conflit_reservation']);
        $conflitData = $conflit ? $_SESSION['conflit_reservation'] : null;
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Breadcrumb -->
                <?php
                Breadcrumb::render([
                    ['text' => 'Accueil', 'url' => '?page=accueil'],
                    ['text' => 'Équipements', 'url' => '?page=equipements'],
                    ['text' => 'Réserver']
                ]);
                ?>

                <div style="max-width: 800px; margin: 0 auto;">
                    <h1 style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2rem;">
                        <i class="fas fa-calendar-plus"></i>
                        Réserver un équipement
                    </h1>

                    <!-- Conflit Alert -->
                    <?php if ($conflit): ?>
                        <?php
                        Alert::render([
                            'title' => 'Créneau non disponible',
                            'icon' => 'fas fa-exclamation-triangle',
                            'variant' => 'warning'
                        ], function () use ($conflitData) {
                            ?>
                            <p style="margin-bottom: 1rem;">
                                <?= htmlspecialchars($conflitData['message']) ?>
                            </p>
                            <p style="margin: 0;">
                                <strong>Option :</strong> Vous pouvez soumettre une demande prioritaire qui sera examinée par
                                un administrateur.
                            </p>
                            <?php
                        });
                        unset($_SESSION['conflit_reservation']);
                        ?>
                    <?php endif; ?>

                    <!-- Equipment Info -->
                    <?php
                    Alert::render([
                        'variant' => 'info'
                    ], function () use ($equipement) {
                        ?>
                        <h3 style="margin: 0 0 0.5rem 0;"><?= htmlspecialchars($equipement['nom']) ?></h3>
                        <p style="margin: 0; color: var(--gray-600);">
                            <?= htmlspecialchars($equipement['description'] ?? '') ?>
                        </p>
                        <div style="margin-top: 0.75rem;">
                            <?php
                            Badge::render([
                                'text' => ETATS_EQUIPEMENTS[$equipement['etat']] ?? $equipement['etat'],
                                'variant' => $equipement['etat'] === 'libre' ? 'success' : 'warning'
                            ]);
                            ?>
                        </div>
                        <?php
                    });
                    ?>

                    <!-- Reservation Form -->
                    <form method="POST" action="?page=equipements&action=confirmer_reservation"
                        style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">

                        <input type="hidden" name="id_equipement" value="<?= $equipement['id'] ?>">

                        <div class="form-grid">
                            <?php
                            $dateDebut = $conflitData['date_debut'] ?? '';
                            $dateFin = $conflitData['date_fin'] ?? '';

                            FormGroup::render([
                                'label' => 'Date et heure de début',
                                'required' => true
                            ], function () use ($dateDebut) {
                                FormInput::render([
                                    'type' => 'datetime-local',
                                    'name' => 'date_debut',
                                    'id' => 'date_debut',
                                    'value' => $dateDebut,
                                    'min' => date('Y-m-d\TH:i'),
                                    'required' => true
                                ]);
                            });

                            FormGroup::render([
                                'label' => 'Date et heure de fin',
                                'required' => true
                            ], function () use ($dateFin) {
                                FormInput::render([
                                    'type' => 'datetime-local',
                                    'name' => 'date_fin',
                                    'id' => 'date_fin',
                                    'value' => $dateFin,
                                    'min' => date('Y-m-d\TH:i'),
                                    'required' => true
                                ]);
                            });
                            ?>
                        </div>

                        <!-- Demande prioritaire section -->
                        <?php if ($equipement['etat'] !== 'libre' || $conflit): ?>
                            <div
                                style="padding: 1.5rem; background: var(--warning-light, #fff3cd); border-radius: 8px; margin-top: 1.5rem; border-left: 4px solid var(--warning-color, #ffc107);">
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                                    <input type="checkbox" name="demande_prioritaire" id="demande_prioritaire"
                                        style="width: 20px; height: 20px;">
                                    <label for="demande_prioritaire" style="font-weight: 600; cursor: pointer;">
                                        Soumettre une demande prioritaire
                                    </label>
                                </div>

                                <div id="justification-container" style="display: none;">
                                    <?php
                                    FormGroup::render([
                                        'label' => 'Justification (requise)',
                                        'required' => false
                                    ], function () {
                                        ?>
                                        <textarea name="justification" id="justification" class="form-control" rows="4"
                                            placeholder="Expliquez pourquoi vous avez besoin de cet équipement de manière urgente..."></textarea>
                                        <?php
                                    });
                                    ?>
                                </div>

                                <p style="margin: 0.75rem 0 0 0; font-size: 0.9rem; color: var(--gray-700);">
                                    <i class="fas fa-info-circle"></i>
                                    Votre demande sera examinée par un administrateur qui pourra l'approuver ou la rejeter.
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php
                        FormActions::render(['align' => 'left'], function () {
                            Button::render([
                                'text' => 'Confirmer la réservation',
                                'icon' => 'fas fa-check',
                                'variant' => 'primary',
                                'type' => 'submit'
                            ]);

                            Button::render([
                                'text' => 'Annuler',
                                'icon' => 'fas fa-times',
                                'variant' => 'secondary',
                                'href' => '?page=equipements'
                            ]);
                        });
                        ?>
                    </form>
                </div>
            </div>
        </main>

        <script>
            // Toggle justification field
            document.getElementById('demande_prioritaire')?.addEventListener('change', function () {
                const container = document.getElementById('justification-container');
                const textarea = document.getElementById('justification');
                if (this.checked) {
                    container.style.display = 'block';
                    textarea.required = true;
                } else {
                    container.style.display = 'none';
                    textarea.required = false;
                }
            });

            // Validate end date is after start date
            document.getElementById('date_fin').addEventListener('change', function () {
                const debut = document.getElementById('date_debut').value;
                const fin = this.value;

                if (debut && fin && fin <= debut) {
                    alert('La date de fin doit être après la date de début');
                    this.value = '';
                }
            });
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Render equipment details page
     */
    public function renderDetails($equipement, $reservations, $statsEquipement, $userHasReservation = false)
    {
        $this->pageTitle = $equipement['nom'];
        $this->renderHeader();
        $this->renderFlashMessage();

        $etatVariant = $this->getEtatVariant($equipement['etat']);
        $etatLabel = ETATS_EQUIPEMENTS[$equipement['etat']] ?? $equipement['etat'];
        $isLoggedIn = isset($_SESSION['user']);
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Breadcrumb -->
                <?php
                Breadcrumb::render([
                    ['text' => 'Accueil', 'url' => '?page=accueil'],
                    ['text' => 'Équipements', 'url' => '?page=equipements'],
                    ['text' => $equipement['nom']]
                ]);
                ?>

                <div class="equipement-details">
                    <!-- Header -->
                    <div style="margin-bottom: 2rem;">
                        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                            <?php
                            Badge::render([
                                'text' => TYPES_EQUIPEMENTS[$equipement['type']] ?? $equipement['type'],
                                'variant' => 'primary',
                                'size' => 'large'
                            ]);

                            Badge::render([
                                'text' => $etatLabel,
                                'variant' => $etatVariant,
                                'size' => 'large'
                            ]);
                            ?>
                        </div>

                        <h1 style="font-size: 2.5rem; margin: 0; color: var(--dark-color);">
                            <?= htmlspecialchars($equipement['nom']) ?>
                        </h1>
                    </div>

                    <!-- Statistics Cards -->
<?php if (!empty($statsEquipement)): ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        
        <!-- Total Reservations -->
        <div class="stat-card stat-primary">
            <div class="stat-content">
                <h3><?= $statsEquipement['total_reservations'] ?></h3>
                <p>Total Réservations</p>
            </div>
        </div>

        <!-- Active Reservations -->
        <div class="stat-card stat-success">
            <div class="stat-content">
                <h3><?= $statsEquipement['reservations_actives'] ?></h3>
                <p>Réservations Actives</p>
            </div>
        </div>

        <!-- Unique Users -->
        <div class="stat-card stat-info">
            <div class="stat-content">
                <h3><?= $statsEquipement['utilisateurs_uniques'] ?></h3>
                <p>Utilisateurs</p>
                <small>utilisateurs uniques</small>
            </div>
        </div>
    </div>

    <style>
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .stat-content h3 {
            font-size: 2rem;
            font-weight: bold;
            color: #1f2937;
            margin: 0 0 0.25rem 0;
            line-height: 1;
        }

        .stat-content p {
            color: #6b7280;
            margin: 0;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .stat-content small {
            display: block;
            color: #9ca3af;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
    </style>
<?php endif; ?>
                    <!-- Main Content Grid -->
                    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem; align-items: start;">
                        <!-- Left Column -->
                        <div>
                            <!-- Description -->
                            <?php
                            Section::render([
                                'title' => 'Description',
                                'icon' => 'fas fa-align-left'
                            ], function () use ($equipement) {
                                echo '<div style="padding: 1.5rem; background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
                                echo '<p style="line-height: 1.8; color: var(--gray-700); margin: 0;">';
                                echo nl2br(htmlspecialchars($equipement['description'] ?? ''));
                                echo '</p></div>';
                            });
                            ?>

                            <!-- Specifications -->
                            <?php if (!empty($equipement['specifications'])): ?>
                                <?php
                                Section::render([
                                    'title' => 'Spécifications',
                                    'icon' => 'fas fa-info-circle'
                                ], function () use ($equipement) {
                                    echo '<div style="padding: 1.5rem; background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
                                    echo '<p style="line-height: 1.8; color: var(--gray-700); margin: 0;">';
                                    echo nl2br(htmlspecialchars($equipement['specifications']));
                                    echo '</p></div>';
                                });
                                ?>
                            <?php endif; ?>

                            <!-- Current Reservations -->
                            <?php if (!empty($reservations)): ?>
                                <?php
                                Section::render([
                                    'title' => 'Réservations en cours',
                                    'icon' => 'fas fa-calendar'
                                ], function () use ($reservations) {
                                    echo '<div style="display: flex; flex-direction: column; gap: 1rem;">';
                                    foreach ($reservations as $res) {
                                        ?>
                                        <div
                                            style="background: white; padding: 1.25rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center;">
                                            <div>
                                                <p
                                                    style="margin: 0 0 0.5rem 0; color: var(--dark-color); font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                                                    <i class="fas fa-user"></i>
                                                    <?= htmlspecialchars($res['membre_nom'] . ' ' . $res['membre_prenom']) ?>
                                                </p>
                                                <p
                                                    style="margin: 0; color: var(--gray-600); font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
                                                    <i class="fas fa-calendar"></i>
                                                    <?= date('d/m/Y H:i', strtotime($res['date_debut'])) ?> -
                                                    <?= date('d/m/Y H:i', strtotime($res['date_fin'])) ?>
                                                </p>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    echo '</div>';
                                });
                                ?>
                            <?php endif; ?>
                        </div>


                        <!-- Right Column: Sidebar -->
                        <div>
                            <div class="info-card">
                                <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem;">
                                    <i class="fas fa-info-circle"></i>
                                    Actions
                                </h3>

                                <?php if ($isLoggedIn): ?>
                                    <?php if ($userHasReservation): ?>
                                        <!-- ✅ L'utilisateur a déjà réservé -->
                                        <?php
                                        Alert::render([
                                            'variant' => 'success'
                                        ], function () {
                                            echo '<p style="margin: 0;"><strong>Vous avez déjà réservé cet équipement</strong></p>';
                                        });
                                        ?>
                                    <?php elseif ($equipement['etat'] === 'libre'): ?>
                                        <?php
                                        Button::render([
                                            'text' => 'Réserver',
                                            'icon' => 'fas fa-calendar-plus',
                                            'variant' => 'primary',
                                            'block' => true,
                                            'href' => '?page=equipements&action=reserver&id=' . $equipement['id']
                                        ]);
                                        ?>
                                    <?php elseif ($equipement['etat'] === 'reserve'): ?>
                                        <?php
                                        Button::render([
                                            'text' => 'Faire une demande prioritaire',
                                            'icon' => 'fas fa-star',
                                            'variant' => 'warning',
                                            'block' => true,
                                            'href' => '?page=equipements&action=reserver&id=' . $equipement['id']
                                        ]);
                                        ?>
                                    <?php else: ?>
                                        <?php
                                        Button::render([
                                            'text' => 'Non disponible (en maintenance)',
                                            'icon' => 'fas fa-tools',
                                            'variant' => 'danger',
                                            'block' => true,
                                            'disabled' => true
                                        ]);
                                        ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php
                                    Button::render([
                                        'text' => 'Connectez-vous pour réserver',
                                        'icon' => 'fas fa-sign-in-alt',
                                        'variant' => 'secondary',
                                        'block' => true,
                                        'href' => '?page=login'
                                    ]);
                                    ?>
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
     * Get badge variant based on equipment status
     */
    private function getEtatVariant($etat)
    {
        switch ($etat) {
            case 'libre':
                return 'success';
            case 'reserve':
                return 'warning';
            case 'en_maintenance':
                return 'danger';
            default:
                return 'default';
        }
    }
}
?>