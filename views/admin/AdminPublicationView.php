<?php
require_once __DIR__ . '/../BaseView.php';
require_once __DIR__ . '/../components/Button.php';
require_once __DIR__ . '/../components/Badge.php';
require_once __DIR__ . '/../components/Breadcrumb.php';
require_once __DIR__ . '/../components/FormGroup.php';
require_once __DIR__ . '/../components/FormInput.php';
require_once __DIR__ . '/../components/FormActions.php';
require_once __DIR__ . '/../components/SearchInput.php';
require_once __DIR__ . '/../components/Filter.php';
require_once __DIR__ . '/../components/StatCard.php';
require_once __DIR__ . '/../components/Table.php';
require_once __DIR__ . '/../components/ActionButtons.php';
require_once __DIR__ . '/../components/Avatar.php';
require_once __DIR__ . '/../components/Chart.php';

class AdminPublicationView extends BaseView
{
    public function __construct()
    {
        $this->pageTitle = 'Administration - Publications';
        $this->currentPage = 'admin';
    }

    public function renderListe($publications, $stats)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-file-alt"></i> Gestion des Publications</h1>
                    <div style="display: flex; gap: 1rem;">
                        <?php Button::render(['text' => 'Statistiques', 'icon' => 'fas fa-chart-bar', 'variant' => 'secondary', 'href' => '?page=admin&section=publications&action=stats']); ?>
                        <?php Button::render(['text' => 'En attente (' . $stats['en_attente'] . ')', 'icon' => 'fas fa-clock', 'variant' => 'warning', 'href' => '?page=admin&section=publications&action=pending', 'cssClass' => 'btn-warning-gradient']); ?>
                        <?php Button::render(['text' => 'Nouvelle Publication', 'icon' => 'fas fa-plus', 'variant' => 'primary', 'href' => '?page=admin&section=publications&action=create']); ?>
                    </div>
                </div>

                <div class="stats-grid">
                    <?php
                    StatCard::render(['value' => $stats['total'], 'label' => 'Total Publications', 'icon' => 'fas fa-file-alt', 'color' => 'var(--primary-color)']);
                    StatCard::render(['value' => $stats['validees'], 'label' => 'Validées', 'icon' => 'fas fa-check-circle', 'color' => '#10b981']);
                    StatCard::render(['value' => $stats['en_attente'], 'label' => 'En attente', 'icon' => 'fas fa-clock', 'color' => '#f59e0b']);
                    foreach ($stats['par_type'] as $stat) {
                        StatCard::render(['value' => $stat['total'], 'label' => ucfirst($stat['type']), 'icon' => 'fas fa-tag', 'color' => 'var(--accent-color)']);
                    }
                    ?>
                </div>

                <?php
                Table::render([
                    'headers' => ['ID', 'Titre', 'Auteurs', 'Type', 'Année', 'Domaine', 'Statut', 'Actions'],
                    'rows' => $publications
                ], function ($pub) {
                    ?>
                    <tr>
                        <td>
                            <?= $pub['id'] ?>
                        </td>
                        <td><strong>
                                <?= htmlspecialchars($pub['titre']) ?>
                            </strong></td>
                        <td>
                            <?= htmlspecialchars(mb_substr($pub['auteurs'], 0, 50)) ?>...
                        </td>
                        <td>
                            <?= htmlspecialchars(strtoupper($pub['type'])) ?>
                        </td>
                        <td>
                            <?= $pub['annee'] ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($pub['domaine_nom'] ?? 'N/A') ?>
                        </td>
                        <td>
                            <?php Badge::render(['text' => $pub['validee'] ? 'Validée' : 'En attente', 'variant' => $pub['validee'] ? 'success' : 'warning', 'size' => 'small']); ?>
                        </td>
                        <td>
                            <?php
                            $actions = [];
                            if (!$pub['validee']) {
                                $actions[] = ['type' => 'custom', 'icon' => 'fas fa-check', 'onClick' => 'confirmValidate(' . $pub['id'] . ', \'' . htmlspecialchars(addslashes($pub['titre'])) . '\')', 'title' => 'Valider'];
                            }
                            $actions[] = ['type' => 'edit', 'href' => '?page=admin&section=publications&action=edit&id=' . $pub['id']];
                            $actions[] = ['type' => 'delete', 'onClick' => 'confirmDelete(' . $pub['id'] . ', \'' . htmlspecialchars(addslashes($pub['titre'])) . '\')'];
                            ActionButtons::render($actions);
                            ?>
                        </td>
                    </tr>
                    <?php
                });
                ?>
            </div>
        </main>

        <style>
            .btn-warning-gradient {
                background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%) !important;
                box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
            }

            .btn-warning-gradient:hover {
                background: linear-gradient(135deg, #f57c00 0%, #e64a19 100%) !important;
            }
        </style>

        <script>
            function confirmDelete(id, titre) {
                if (confirm(`Êtes-vous sûr de vouloir supprimer la publication "${titre}" ?\nCette action est irréversible.`)) {
                    window.location.href = '?page=admin&section=publications&action=delete&id=' + id;
                }
            }
            function confirmValidate(id, titre) {
                if (confirm(`Valider la publication "${titre}" ?`)) {
                    window.location.href = '?page=admin&section=publications&action=validatePublication&id=' + id;
                }
            }
        </script>

        <?php $this->renderFooter();
    }

    /**
     * Render statistics page - NEW!
     */
    public function renderStatistics($stats, $types, $auteurs, $annees, $projets)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <?php Breadcrumb::render([
                    ['text' => 'Administration', 'url' => '?page=admin'],
                    ['text' => 'Publications', 'url' => '?page=admin&section=publications'],
                    ['text' => 'Statistiques']
                ]); ?>

                <div class="admin-header">
                    <h1><i class="fas fa-chart-bar"></i> Statistiques des Publications</h1>
                    <?php Button::render(['text' => 'Retour', 'icon' => 'fas fa-arrow-left', 'variant' => 'secondary', 'href' => '?page=admin&section=publications']); ?>
                </div>

                <!-- PDF Export Section -->
                <div
                    style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                    <h3 style="margin: 0 0 1rem 0; color: var(--dark-color); display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-file-pdf" style="color: #dc3545;"></i>
                        Génération de Rapports PDF
                    </h3>

                    <form method="GET" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                        <input type="hidden" name="page" value="admin">
                        <input type="hidden" name="section" value="publications">
                        <input type="hidden" name="action" value="generate_pdf">

                        <div style="flex: 1; min-width: 200px;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">Type
                                de rapport</label>
                            <select name="type" class="form-control" required onchange="toggleFilters(this.value)">
                                <option value="all">Toutes les publications</option>
                                <option value="type">Par type</option>
                                <option value="auteur">Par auteur</option>
                                <option value="annee">Par année</option>
                                <option value="projet">Par projet</option>
                            </select>
                        </div>

                        <div id="filter-type" style="flex: 1; min-width: 200px; display: none;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">Type
                                de publication</label>
                            <select name="type_value" class="form-control">
                                <option value="">Tous</option>
                                <?php foreach ($types as $key => $label): ?>
                                    <option value="<?= $key ?>">
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="filter-auteur" style="flex: 1; min-width: 200px; display: none;">
                            <label
                                style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">Auteur</label>
                            <select name="auteur_value" class="form-control">
                                <option value="">Tous</option>
                                <?php foreach ($auteurs as $auteur): ?>
                                    <option value="<?= htmlspecialchars($auteur) ?>">
                                        <?= htmlspecialchars($auteur) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="filter-annee" style="flex: 1; min-width: 200px; display: none;">
                            <label
                                style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">Année</label>
                            <select name="annee_value" class="form-control">
                                <option value="">Toutes</option>
                                <?php foreach ($annees as $a): ?>
                                    <option value="<?= $a['annee'] ?>">
                                        <?= $a['annee'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="filter-projet" style="flex: 1; min-width: 200px; display: none;">
                            <label
                                style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">Projet</label>
                            <select name="projet_value" class="form-control">
                                <option value="">Tous</option>
                                <?php foreach ($projets as $p): ?>
                                    <option value="<?= $p['id_projet'] ?>">
                                        <?= htmlspecialchars($p['titre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php Button::render(['text' => 'Générer PDF', 'icon' => 'fas fa-file-pdf', 'variant' => 'primary', 'type' => 'submit']); ?>
                    </form>
                </div>

                <!-- Charts Grid -->
                <div style="display: grid; gap: 2rem;">

                    <!-- Publications by Type -->
                    <?php
                    $typeData = array_map(function ($item) {
                        return ['label' => ucfirst($item['type']), 'value' => (int) $item['total']];
                    }, $stats['par_type']);

                    Chart::renderBar([
                        'data' => $typeData,
                        'title' => 'Répartition des publications par type',
                        'height' => 300,
                        'orientation' => 'vertical'
                    ]);
                    ?>

                    <!-- Two columns -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem;">

                        <!-- Publications by Year -->
                        <?php
                        $anneeData = array_map(function ($item) {
                            return ['label' => $item['annee'] ?? 'N/A', 'value' => (int) $item['total']];
                        }, $stats['par_annee']);

                        Chart::renderBar([
                            'data' => $anneeData,
                            'title' => 'Publications par année',
                            'height' => 250,
                            'orientation' => 'horizontal'
                        ]);
                        ?>

                        <!-- Validation Status -->
                        <?php
                        $statusData = [
                            ['label' => 'Validées', 'value' => $stats['validees']],
                            ['label' => 'En attente', 'value' => $stats['en_attente']]
                        ];

                        Chart::renderPie([
                            'data' => $statusData,
                            'title' => 'Statut de validation',
                            'donut' => true,
                            'size' => 200
                        ]);
                        ?>
                    </div>

                    <!-- Top Authors -->
                    <?php if (!empty($stats['par_auteur'])): ?>
                        <?php
                        $auteurData = array_slice(array_map(function ($item) {
                            return ['label' => $item['auteur_principal'], 'value' => (int) $item['total']];
                        }, $stats['par_auteur']), 0, 10);

                        Chart::renderBar([
                            'data' => $auteurData,
                            'title' => 'Top 10 des auteurs les plus prolifiques',
                            'height' => 300,
                            'orientation' => 'horizontal'
                        ]);
                        ?>
                    <?php endif; ?>

                </div>
            </div>
        </main>

        <script>
            function toggleFilters(type) {
                document.getElementById('filter-type').style.display = type === 'type' ? 'block' : 'none';
                document.getElementById('filter-auteur').style.display = type === 'auteur' ? 'block' : 'none';
                document.getElementById('filter-annee').style.display = type === 'annee' ? 'block' : 'none';
                document.getElementById('filter-projet').style.display = type === 'projet' ? 'block' : 'none';
            }
        </script>

        <?php
        $this->renderFooter();
    }

    public function renderPending($publications)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-clock"></i> Publications en attente de validation</h1>
                    <?php Button::render(['text' => 'Retour à la liste', 'icon' => 'fas fa-arrow-left', 'variant' => 'secondary', 'href' => '?page=admin&section=publications']); ?>
                </div>

                <?php if (empty($publications)): ?>
                    <?php
                    require_once __DIR__ . '/../components/EmptyState.php';
                    EmptyState::render([
                        'icon' => 'fas fa-check-circle',
                        'title' => 'Aucune publication en attente',
                        'description' => 'Toutes les publications ont été validées'
                    ]);
                    ?>
                <?php else: ?>
                    <?php
                    Table::render([
                        'headers' => ['ID', 'Titre', 'Auteurs', 'Type', 'Année', 'Domaine', 'Date soumission', 'Actions'],
                        'rows' => $publications
                    ], function ($pub) {
                        ?>
                        <tr>
                            <td>
                                <?= $pub['id'] ?>
                            </td>
                            <td><strong>
                                    <?= htmlspecialchars($pub['titre']) ?>
                                </strong></td>
                            <td>
                                <?= htmlspecialchars(mb_substr($pub['auteurs'], 0, 50)) ?>...
                            </td>
                            <td>
                                <?= htmlspecialchars(strtoupper($pub['type'])) ?>
                            </td>
                            <td>
                                <?= $pub['annee'] ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($pub['domaine_nom'] ?? 'N/A') ?>
                            </td>
                            <td>
                                <?= date('d/m/Y H:i', strtotime($pub['created_at'])) ?>
                            </td>
                            <td>
                                <?php ActionButtons::render([
                                    ['type' => 'custom', 'icon' => 'fas fa-check', 'onClick' => 'confirmValidate(' . $pub['id'] . ', \'' . htmlspecialchars(addslashes($pub['titre'])) . '\')', 'title' => 'Valider'],
                                    ['type' => 'edit', 'href' => '?page=admin&section=publications&action=edit&id=' . $pub['id']],
                                    ['type' => 'delete', 'onClick' => 'confirmDelete(' . $pub['id'] . ', \'' . htmlspecialchars(addslashes($pub['titre'])) . '\')']
                                ]); ?>
                            </td>
                        </tr>
                        <?php
                    });
                    ?>
                <?php endif; ?>
            </div>
        </main>

        <script>
            function confirmDelete(id, titre) {
                if (confirm(`Êtes-vous sûr de vouloir supprimer la publication "${titre}" ?\nCette action est irréversible.`)) {
                    window.location.href = '?page=admin&section=publications&action=delete&id=' + id;
                }
            }
            function confirmValidate(id, titre) {
                if (confirm(`Valider la publication "${titre}" ?\n\nElle sera visible sur le site public.`)) {
                    window.location.href = '?page=admin&section=publications&action=validatePublication&id=' + id;
                }
            }
        </script>

        <?php $this->renderFooter();
    }
}
?>