<?php
require_once __DIR__ . '/../BaseView.php';

// Import Components
require_once __DIR__ . '/../components/Section.php';
require_once __DIR__ . '/../components/Table.php';
require_once __DIR__ . '/../components/Badge.php';
require_once __DIR__ . '/../components/Filter.php';
require_once __DIR__ . '/../components/FilterBar.php';
require_once __DIR__ . '/../components/FormGroup.php';
require_once __DIR__ . '/../components/FormInput.php';
require_once __DIR__ . '/../components/FormActions.php';

class AdminOffreView extends BaseView
{
    public function __construct()
    {
        $this->pageTitle = 'Gestion des Offres';
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
                <div class="page-header">
                    <h1><i class="fas fa-briefcase"></i> Gestion des Offres</h1>
                    <a href="?page=admin&section=offres&action=create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouvelle offre
                    </a>
                </div>

                <!-- Filters -->
                <?php
                FilterBar::render([], function () {
                    Filter::render([
                        'id' => 'filter-type',
                        'label' => 'Type',
                        'icon' => 'fas fa-tag',
                        'placeholder' => 'Tous',
                        'options' => [
                            ['value' => 'stage', 'text' => 'Stages'],
                            ['value' => 'these', 'text' => 'Thèses'],
                            ['value' => 'bourse', 'text' => 'Bourses'],
                            ['value' => 'collaboration', 'text' => 'Collaborations']
                        ]
                    ]);

                    Filter::render([
                        'id' => 'filter-statut',
                        'label' => 'Statut',
                        'icon' => 'fas fa-toggle-on',
                        'placeholder' => 'Tous',
                        'options' => [
                            ['value' => 'active', 'text' => 'Actives'],
                            ['value' => 'inactive', 'text' => 'Inactives']
                        ]
                    ]);
                });
                ?>

                <!-- Table -->
                <?php
                if (empty($offres)) {
                    echo '<div style="text-align: center; padding: 3rem; background: white; border-radius: 12px;">';
                    echo '<p style="color: var(--gray-600);">Aucune offre disponible</p>';
                    echo '</div>';
                } else {
                    Table::render([
                        'headers' => ['Type', 'Titre', 'Date limite', 'Statut', 'Actions'],
                        'rows' => $offres
                    ], function ($offre) {
                        ?>
                        <tr>
                            <td>
                                <?php
                                $variants = [
                                    'stage' => 'primary',
                                    'these' => 'info',
                                    'bourse' => 'success',
                                    'collaboration' => 'warning'
                                ];

                                Badge::render([
                                    'text' => ucfirst($offre['type']),
                                    'variant' => $variants[$offre['type']] ?? 'default',
                                    'size' => 'small'
                                ]);
                                ?>
                            </td>
                            <td><strong>
                                    <?= htmlspecialchars($offre['titre']) ?>
                                </strong></td>
                            <td>
                                <?= $offre['date_limite'] ? date('d/m/Y', strtotime($offre['date_limite'])) : 'N/A' ?>
                            </td>
                            <td>
                                <?php
                                Badge::render([
                                    'text' => $offre['statut'] === 'active' ? 'Active' : 'Inactive',
                                    'variant' => $offre['statut'] === 'active' ? 'success' : 'default',
                                    'size' => 'small'
                                ]);
                                ?>
                            </td>
                            <td style="white-space: nowrap;">
                                <a href="?page=admin&section=offres&action=toggleStatus&id=<?= $offre['id_offre'] ?>"
                                    class="btn btn-sm <?= $offre['statut'] === 'active' ? 'btn-warning' : 'btn-success' ?>"
                                    title="<?= $offre['statut'] === 'active' ? 'Désactiver' : 'Activer' ?>">
                                    <i class="fas fa-toggle-<?= $offre['statut'] === 'active' ? 'on' : 'off' ?>"></i>
                                </a>
                                <a href="?page=admin&section=offres&action=edit&id=<?= $offre['id_offre'] ?>"
                                    class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?page=admin&section=offres&action=delete&id=<?= $offre['id_offre'] ?>"
                                    class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette offre ?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php
                    });
                }
                ?>
            </div>
        </main>

        <script>
            const typeFilter = document.getElementById('filter-type');
            const statutFilter = document.getElementById('filter-statut');

            function applyFilters() {
                const params = new URLSearchParams({
                    page: 'admin',
                    section: 'offres',
                    type: typeFilter.value,
                    statut: statutFilter.value
                });
                window.location.href = '?' + params.toString();
            }

            if (typeFilter) typeFilter.addEventListener('change', applyFilters);
            if (statutFilter) statutFilter.addEventListener('change', applyFilters);
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Formulaire création/modification
     */
    public function renderForm($offre = null)
    {
        $isEdit = $offre !== null;
        $this->pageTitle = $isEdit ? 'Modifier l\'offre' : 'Nouvelle offre';

        $this->renderHeader();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <a href="?page=admin&section=offres" class="btn btn-outline" style="margin-bottom: 1.5rem;">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>

                <div
                    style="max-width: 800px; background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h2 style="margin: 0 0 1.5rem 0;">
                        <?= $isEdit ? 'Modifier l\'offre' : 'Nouvelle offre' ?>
                    </h2>

                    <form method="POST" action="?page=admin&section=offres&action=<?= $isEdit ? 'update' : 'store' ?>">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_offre" value="<?= $offre['id_offre'] ?>">
                        <?php endif; ?>

                        <?php
                        // Titre
                        FormGroup::render([
                            'label' => 'Titre',
                            'required' => true
                        ], function () use ($offre) {
                            FormInput::render([
                                'type' => 'text',
                                'name' => 'titre',
                                'value' => $offre['titre'] ?? '',
                                'placeholder' => 'Ex: Stage de recherche en IA',
                                'required' => true
                            ]);
                        });

                        // Type
                        FormGroup::render([
                            'label' => 'Type d\'offre',
                            'required' => true
                        ], function () use ($offre) {
                            ?>
                            <select name="type" class="form-control" required>
                                <option value="">-- Choisir --</option>
                                <option value="stage" <?= ($offre['type'] ?? '') === 'stage' ? 'selected' : '' ?>>Stage</option>
                                <option value="these" <?= ($offre['type'] ?? '') === 'these' ? 'selected' : '' ?>>Thèse</option>
                                <option value="bourse" <?= ($offre['type'] ?? '') === 'bourse' ? 'selected' : '' ?>>Bourse</option>
                                <option value="collaboration" <?= ($offre['type'] ?? '') === 'collaboration' ? 'selected' : '' ?>
                                    >Collaboration</option>
                            </select>
                            <?php
                        });

                        // Date limite
                        FormGroup::render([
                            'label' => 'Date limite (optionnel)'
                        ], function () use ($offre) {
                            FormInput::render([
                                'type' => 'date',
                                'name' => 'date_limite',
                                'value' => $offre['date_limite'] ?? ''
                            ]);
                        });

                        // Description
                        FormGroup::render([
                            'label' => 'Description',
                            'required' => true
                        ], function () use ($offre) {
                            ?>
                            <textarea name="description" class="form-control" rows="10" placeholder="Décrivez l'offre..."
                                required><?= htmlspecialchars($offre['description'] ?? '') ?></textarea>
                            <?php
                        });

                        // Actions
                        FormActions::render([
                            'align' => 'space-between'
                        ], function () use ($isEdit) {
                            ?>
                            <a href="?page=admin&section=offres" class="btn btn-outline">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                <?= $isEdit ? 'Modifier' : 'Créer' ?>
                            </button>
                            <?php
                        });
                        ?>
                    </form>
                </div>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }
}
?>