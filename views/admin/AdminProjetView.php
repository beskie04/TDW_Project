<?php
// ⭐ AJOUT: Import PermissionHelper
require_once __DIR__ . '/../../utils/PermissionHelper.php';

require_once __DIR__ . '/../BaseView.php';

// Import Generic Framework Components
require_once __DIR__ . '/../components/Button.php';
require_once __DIR__ . '/../components/Badge.php';
require_once __DIR__ . '/../components/Breadcrumb.php';
require_once __DIR__ . '/../components/FormGroup.php';
require_once __DIR__ . '/../components/FormInput.php';
require_once __DIR__ . '/../components/FormActions.php';
require_once __DIR__ . '/../components/StatCard.php';
require_once __DIR__ . '/../components/Table.php';
require_once __DIR__ . '/../components/ActionButtons.php';
require_once __DIR__ . '/../components/SelectOrCreate.php';
require_once __DIR__ . '/../components/Chart.php';

class AdminProjetView extends BaseView
{
    public function __construct()
    {
        $this->pageTitle = 'Administration - Projets';
        $this->currentPage = 'admin';
    }

    /**
     * Render projects list
     */
    public function renderListe($projets, $stats)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-project-diagram"></i> Gestion des Projets</h1>
                    <div style="display: flex; gap: 1rem;">
                        <?php 
                        // ⭐ AJOUT: Check permission pour voir les stats
                        if (hasPermission('view_projet_stats')): 
                            Button::render([
                                'text' => 'Statistiques',
                                'icon' => 'fas fa-chart-bar',
                                'variant' => 'secondary',
                                'href' => '?page=admin&section=projets&action=stats'
                            ]); 
                        endif; 
                        ?>
                        
                        <?php 
                        // ⭐ AJOUT: Check permission pour créer un projet
                        if (hasPermission('create_projet')): 
                            Button::render([
                                'text' => 'Nouveau Projet',
                                'icon' => 'fas fa-plus',
                                'variant' => 'primary',
                                'href' => '?page=admin&section=projets&action=create'
                            ]); 
                        endif; 
                        ?>
                    </div>
                </div>

                <!-- Stats -->
                <div class="stats-grid">
                    <?php
                    StatCard::render([
                        'value' => count($projets),
                        'label' => 'Total Projets',
                        'icon' => 'fas fa-project-diagram',
                        'color' => 'var(--primary-color)'
                    ]);

                    foreach ($stats['par_statut'] as $stat) {
                        StatCard::render([
                            'value' => $stat['total'],
                            'label' => $stat['nom_statut'],
                            'icon' => 'fas fa-tasks',
                            'color' => 'var(--accent-color)'
                        ]);
                    }
                    ?>
                </div>

                <!-- Table -->
                <?php
                Table::render([
                    'headers' => ['ID', 'Titre', 'Responsable', 'Thématique', 'Statut', 'Date début', 'Budget', 'Actions'],
                    'rows' => $projets
                ], function ($p) {
                    // ⭐ AJOUT: Vérifier si l'utilisateur est le responsable
                    $isResponsable = isset($_SESSION['user']) && 
                                     $_SESSION['user']['id_membre'] == $p['responsable_id'];
                    
                    // ⭐ AJOUT: Vérifier les permissions
                    $canEdit = hasPermission('edit_projet') || 
                               ($isResponsable && hasPermission('edit_own_projet'));
                    
                    $canDelete = hasPermission('delete_projet') || 
                                 ($isResponsable && hasPermission('delete_own_projet'));
                    ?>
                    <tr>
                        <td><?= $p['id_projet'] ?></td>
                        <td><strong><?= htmlspecialchars($p['titre']) ?></strong></td>
                        <td><?= htmlspecialchars(($p['responsable_nom'] ?? '') . ' ' . ($p['responsable_prenom'] ?? '')) ?></td>
                        <td><?= htmlspecialchars($p['thematique_nom'] ?? 'N/A') ?></td>
                        <td>
                            <?php Badge::render([
                                'text' => $p['statut_nom'] ?? 'N/A',
                                'variant' => $this->getStatusVariant($p['statut_nom'] ?? ''),
                                'size' => 'small'
                            ]); ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($p['date_debut'])) ?></td>
                        <td><?= $p['budget'] ? number_format($p['budget'], 0, ',', ' ') . ' DZD' : 'N/A' ?></td>
                        <td>
                            <?php 
                            // ⭐ CHANGÉ: Actions conditionnelles basées sur permissions
                            $actions = [
                                ['type' => 'view', 'href' => '?page=projets&action=details&id=' . $p['id_projet'], 'target' => '_blank']
                            ];
                            
                            if ($canEdit) {
                                $actions[] = ['type' => 'edit', 'href' => '?page=admin&section=projets&action=edit&id=' . $p['id_projet']];
                            }
                            
                            if ($canDelete) {
                                $actions[] = ['type' => 'delete', 'onClick' => 'confirmDelete(' . $p['id_projet'] . ', \'' . htmlspecialchars(addslashes($p['titre'])) . '\')'];
                            }
                            
                            ActionButtons::render($actions);
                            ?>
                        </td>
                    </tr>
                    <?php
                });
                ?>
            </div>
        </main>

        <script>
            function confirmDelete(id, titre) {
                if (confirm(`Êtes-vous sûr de vouloir supprimer le projet "${titre}" ?\nCette action est irréversible.`)) {
                    window.location.href = '?page=admin&section=projets&action=delete&id=' + id;
                }
            }
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Render statistics page
     */
    public function renderStatistics($stats, $thematiques, $responsables, $annees)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <!-- Breadcrumb -->
                <?php Breadcrumb::render([
                    ['text' => 'Administration', 'url' => '?page=admin'],
                    ['text' => 'Projets', 'url' => '?page=admin&section=projets'],
                    ['text' => 'Statistiques']
                ]); ?>

                <div class="admin-header">
                    <h1><i class="fas fa-chart-bar"></i> Statistiques des Projets</h1>
                    <div style="display: flex; gap: 1rem;">
                        <?php Button::render([
                            'text' => 'Retour',
                            'icon' => 'fas fa-arrow-left',
                            'variant' => 'secondary',
                            'href' => '?page=admin&section=projets'
                        ]); ?>
                    </div>
                </div>

                <!-- PDF Export Section -->
                <?php 
                // ⭐ AJOUT: Check permission pour générer PDF
                if (hasPermission('generate_projet_pdf')): 
                ?>
                <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                    <h3 style="margin: 0 0 1rem 0; color: var(--dark-color); display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-file-pdf" style="color: #dc3545;"></i>
                        Génération de Rapports PDF
                    </h3>

                    <form method="GET" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                        <input type="hidden" name="page" value="admin">
                        <input type="hidden" name="section" value="projets">
                        <input type="hidden" name="action" value="generate_pdf">

                        <div style="flex: 1; min-width: 200px;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                                Type de rapport
                            </label>
                            <select name="type" class="form-control" required>
                                <option value="all">Tous les projets</option>
                                <option value="thematique">Par thématique</option>
                                <option value="responsable">Par responsable</option>
                                <option value="annee">Par année</option>
                            </select>
                        </div>

                        <div id="filter-thematique" style="flex: 1; min-width: 200px; display: none;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                                Thématique
                            </label>
                            <select name="thematique_id" class="form-control">
                                <option value="">Toutes</option>
                                <?php foreach ($thematiques as $t): ?>
                                    <option value="<?= $t['id_thematique'] ?>"><?= htmlspecialchars($t['nom_thematique']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="filter-responsable" style="flex: 1; min-width: 200px; display: none;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                                Responsable
                            </label>
                            <select name="responsable_id" class="form-control">
                                <option value="">Tous</option>
                                <?php foreach ($responsables as $r): ?>
                                    <option value="<?= $r['id_membre'] ?>"><?= htmlspecialchars($r['nom'] . ' ' . $r['prenom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="filter-annee" style="flex: 1; min-width: 200px; display: none;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                                Année
                            </label>
                            <select name="annee" class="form-control">
                                <option value="">Toutes</option>
                                <?php foreach ($annees as $a): ?>
                                    <option value="<?= $a['annee'] ?>"><?= $a['annee'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php Button::render([
                            'text' => 'Générer PDF',
                            'icon' => 'fas fa-file-pdf',
                            'variant' => 'primary',
                            'type' => 'submit'
                        ]); ?>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Charts Grid -->
                <div style="display: grid; gap: 2rem;">

                    <!-- Projects by Thematic -->
                    <?php
                    $thematiqueData = array_map(function ($item) {
                        return [
                            'label' => $item['nom'],
                            'value' => (int) $item['total']
                        ];
                    }, $stats['par_thematique']);

                    Chart::renderBar([
                        'data' => $thematiqueData,
                        'title' => 'Répartition des projets par thématique',
                        'height' => 300,
                        'orientation' => 'vertical'
                    ]);
                    ?>

                    <!-- Two columns for smaller charts -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem;">

                        <!-- Projects by Status -->
                        <?php
                        $statutData = array_map(function ($item) {
                            return [
                                'label' => $item['nom_statut'],
                                'value' => (int) $item['total']
                            ];
                        }, $stats['par_statut']);

                        Chart::renderPie([
                            'data' => $statutData,
                            'title' => 'Répartition des projets par statut',
                            'donut' => true,
                            'size' => 200
                        ]);
                        ?>

                        <!-- Projects by Year -->
                        <?php
                        $anneeData = array_map(function ($item) {
                            return [
                                'label' => $item['annee'] ?? 'N/A',
                                'value' => (int) $item['total']
                            ];
                        }, $stats['par_annee']);

                        Chart::renderBar([
                            'data' => $anneeData,
                            'title' => 'Projets par année de début',
                            'height' => 250,
                            'orientation' => 'horizontal'
                        ]);
                        ?>
                    </div>

                    <!-- Projects by Responsable -->
                    <?php if (!empty($stats['par_responsable'])): ?>
                        <?php
                        $responsableData = array_slice(array_map(function ($item) {
                            return [
                                'label' => $item['responsable_nom'],
                                'value' => (int) $item['total']
                            ];
                        }, $stats['par_responsable']), 0, 10); // Top 10
            
                        Chart::renderBar([
                            'data' => $responsableData,
                            'title' => 'Top 10 des responsables par nombre de projets',
                            'height' => 300,
                            'orientation' => 'horizontal'
                        ]);
                        ?>
                    <?php endif; ?>

                </div>
            </div>
        </main>

        <script>
            // Show/hide filter based on report type
            document.querySelector('select[name="type"]').addEventListener('change', function () {
                const type = this.value;
                document.getElementById('filter-thematique').style.display = type === 'thematique' ? 'block' : 'none';
                document.getElementById('filter-responsable').style.display = type === 'responsable' ? 'block' : 'none';
                document.getElementById('filter-annee').style.display = type === 'annee' ? 'block' : 'none';
            });
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Render form for creating/editing projects
     */
    public function renderForm($projet = null, $thematiques, $statuts, $typesFinancement, $membres, $projetMembres = [], $partenaires = [], $projetPartenaires = [])
    {
        $isEdit = $projet !== null;
        $this->pageTitle = $isEdit ? 'Modifier le projet' : 'Nouveau projet';

        $this->renderHeader();
        $this->renderFlashMessage();
        
        // ⭐ AJOUT: Check permission pour gérer les membres/partenaires
        $canManageMembers = hasPermission('manage_projet_members');
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <!-- Breadcrumb -->
                <?php Breadcrumb::render([
                    ['text' => 'Administration', 'url' => '?page=admin'],
                    ['text' => 'Projets', 'url' => '?page=admin&section=projets'],
                    ['text' => $isEdit ? 'Modifier' : 'Nouveau']
                ]); ?>

                <div class="form-container">
                    <h1><?= $isEdit ? 'Modifier le projet' : 'Nouveau projet' ?></h1>

                    <form method="POST" action="?page=admin&section=projets&action=<?= $isEdit ? 'update' : 'store' ?>" class="admin-form">

                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?= $projet['id_projet'] ?>">
                        <?php endif; ?>

                        <!-- Main Project Information -->
                        <div class="form-grid">
                            <?php
                            // Titre
                            FormGroup::render([
                                'label' => 'Titre du projet',
                                'required' => true,
                                'cssClass' => 'full-width'
                            ], function () use ($isEdit, $projet) {
                                FormInput::render([
                                    'name' => 'titre',
                                    'value' => $isEdit ? $projet['titre'] : '',
                                    'required' => true,
                                    'placeholder' => 'Ex: Développement d\'un système intelligent...'
                                ]);
                            });

                            // Description
                            FormGroup::render([
                                'label' => 'Description',
                                'required' => true,
                                'cssClass' => 'full-width'
                            ], function () use ($isEdit, $projet) {
                                ?>
                                <textarea name="description" rows="4" class="form-control" required placeholder="Décrivez le projet en détail..."><?= $isEdit ? htmlspecialchars($projet['description']) : '' ?></textarea>
                                <?php
                            });

                            // Objectifs
                            FormGroup::render([
                                'label' => 'Objectifs',
                                'cssClass' => 'full-width'
                            ], function () use ($isEdit, $projet) {
                                ?>
                                <textarea name="objectifs" rows="4" class="form-control" placeholder="Définissez les objectifs du projet..."><?= $isEdit ? htmlspecialchars($projet['objectifs'] ?? '') : '' ?></textarea>
                                <?php
                            });
                            ?>
                        </div>

                        <!-- Thématique with Create New Option -->
                        <div style="margin-bottom: 1.5rem;">
                            <?php
                            SelectOrCreate::render([
                                'name' => 'id_thematique',
                                'label' => 'Thématique',
                                'items' => array_map(function ($t) {
                                    return ['value' => $t['id'], 'text' => $t['nom']];
                                }, $thematiques),
                                'value' => $isEdit ? $projet['id_thematique'] : '',
                                'required' => true,
                                'createLabel' => '+ Créer nouvelle thématique',
                                'newItemPlaceholder' => 'Nom de la nouvelle thématique'
                            ]);
                            ?>
                        </div>

                        <div class="form-grid">
                            <?php
                            // Statut
                            FormGroup::render([
                                'label' => 'Statut',
                                'required' => true
                            ], function () use ($isEdit, $projet, $statuts) {
                                ?>
                                <select name="id_statut" class="form-control" required>
                                    <option value="">Sélectionnez un statut</option>
                                    <?php foreach ($statuts as $stat): ?>
                                        <option value="<?= $stat['id_statut'] ?>" <?= $isEdit && $projet['id_statut'] == $stat['id_statut'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($stat['nom_statut']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php
                            });
                            ?>
                        </div>

                        <!-- Type Financement with Create New Option -->
                        <div style="margin-bottom: 1.5rem;">
                            <?php
                            SelectOrCreate::render([
                                'name' => 'id_type_financement',
                                'label' => 'Type de financement',
                                'items' => array_map(function ($t) {
                                    return ['value' => $t['id'], 'text' => $t['nom']];
                                }, $typesFinancement),
                                'value' => $isEdit ? $projet['id_type_financement'] : '',
                                'required' => true,
                                'createLabel' => '+ Créer nouveau type',
                                'newItemPlaceholder' => 'Nom du nouveau type de financement'
                            ]);
                            ?>
                        </div>

                        <div class="form-grid">
                            <?php
                            // Responsable
                            FormGroup::render([
                                'label' => 'Responsable',
                                'required' => true
                            ], function () use ($isEdit, $projet, $membres) {
                                ?>
                                <select name="responsable_id" class="form-control" required>
                                    <option value="">Sélectionnez un responsable</option>
                                    <?php foreach ($membres as $membre): ?>
                                        <option value="<?= $membre['id_membre'] ?>" <?= $isEdit && $projet['responsable_id'] == $membre['id_membre'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php
                            });

                            // Date début
                            FormGroup::render([
                                'label' => 'Date de début',
                                'required' => true
                            ], function () use ($isEdit, $projet) {
                                FormInput::render([
                                    'type' => 'date',
                                    'name' => 'date_debut',
                                    'value' => $isEdit ? $projet['date_debut'] : '',
                                    'required' => true
                                ]);
                            });

                            // Date fin
                            FormGroup::render([
                                'label' => 'Date de fin'
                            ], function () use ($isEdit, $projet) {
                                FormInput::render([
                                    'type' => 'date',
                                    'name' => 'date_fin',
                                    'value' => $isEdit ? ($projet['date_fin'] ?? '') : ''
                                ]);
                            });

                            // Budget
                            FormGroup::render([
                                'label' => 'Budget (DZD)'
                            ], function () use ($isEdit, $projet) {
                                FormInput::render([
                                    'type' => 'number',
                                    'name' => 'budget',
                                    'value' => $isEdit ? ($projet['budget'] ?? '') : '',
                                    'attributes' => ['step' => '0.01', 'min' => '0']
                                ]);
                            });
                            ?>
                        </div>

                        <!-- ⭐ CHANGÉ: Team Members Section (only if has permission) -->
                        <?php if ($canManageMembers): ?>
                        <div class="form-section" style="margin-top: 2rem; padding: 1.5rem; background: #f8f9fa; border-radius: 8px;">
                            <h3 style="margin-bottom: 1rem; color: var(--primary-color); display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-users"></i>
                                Membres du projet
                                <span style="font-size: 0.9rem; font-weight: normal; color: #666;">(optionnel)</span>
                            </h3>

                            <div id="membres-container">
                                <?php if (!empty($projetMembres)): ?>
                                    <?php foreach ($projetMembres as $index => $pm): ?>
                                        <?php $this->renderMembreRow($index, $pm, $membres); ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <?php Button::render([
                                'text' => 'Ajouter un membre',
                                'icon' => 'fas fa-plus',
                                'variant' => 'secondary',
                                'type' => 'button',
                                'attributes' => ['onclick' => 'addMembre()']
                            ]); ?>
                        </div>
                        <?php else: ?>
                        <div style="margin-top: 2rem; padding: 1rem; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                            <p style="margin: 0; color: #856404;">
                                <i class="fas fa-info-circle"></i>
                                <strong>Info:</strong> Vous n'avez pas la permission de gérer les membres du projet.
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- ⭐ CHANGÉ: Partners Section (only if has permission) -->
                        <?php if ($canManageMembers): ?>
                        <div class="form-section" style="margin-top: 2rem; padding: 1.5rem; background: #f0f9ff; border-radius: 8px;">
                            <h3 style="margin-bottom: 1rem; color: var(--primary-color); display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-handshake"></i>
                                Partenaires du projet
                                <span style="font-size: 0.9rem; font-weight: normal; color: #666;">(optionnel)</span>
                            </h3>

                            <div id="partenaires-container">
                                <?php if (!empty($projetPartenaires)): ?>
                                    <?php foreach ($projetPartenaires as $index => $pp): ?>
                                        <?php $this->renderPartenaireRow($index, $pp, $partenaires); ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <?php Button::render([
                                'text' => 'Ajouter un partenaire',
                                'icon' => 'fas fa-plus',
                                'variant' => 'secondary',
                                'type' => 'button',
                                'attributes' => ['onclick' => 'addPartenaire()']
                            ]); ?>
                        </div>
                        <?php endif; ?>

                        <!-- Form Actions -->
                        <?php FormActions::render(['align' => 'left'], function () use ($isEdit) {
                            Button::render([
                                'text' => $isEdit ? 'Mettre à jour' : 'Créer',
                                'icon' => 'fas fa-save',
                                'variant' => 'primary',
                                'type' => 'submit'
                            ]);

                            Button::render([
                                'text' => 'Annuler',
                                'icon' => 'fas fa-times',
                                'variant' => 'secondary',
                                'href' => '?page=admin&section=projets'
                            ]);
                        }); ?>
                    </form>
                </div>
            </div>
        </main>

        <style>
            .form-section { margin-top: 2rem; }
            #membres-container, #partenaires-container { margin-bottom: 1rem; }
            .membre-row, .partenaire-row {
                background: white;
                padding: 1rem;
                border-radius: 8px;
                margin-bottom: 1rem;
                border: 1px solid #ddd;
                position: relative;
            }
            .membre-fields, .partenaire-fields {
                display: grid;
                grid-template-columns: 2fr 1.5fr 1fr 1fr auto;
                gap: 1rem;
                align-items: end;
            }
            .membre-fields .form-group, .partenaire-fields .form-group { margin-bottom: 0; }
            .btn-remove-membre {
                background: #dc3545;
                color: white;
                border: none;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
            }
            .btn-remove-membre:hover {
                background: #c82333;
                transform: scale(1.1);
            }
            @media (max-width: 768px) {
                .membre-fields, .partenaire-fields { grid-template-columns: 1fr; }
                .btn-remove-membre { width: 100%; border-radius: 8px; }
            }
        </style>

        <script>
            // Members Management
            let membreIndex = <?= !empty($projetMembres) ? count($projetMembres) : 0 ?>;

            const membresOptions = `
            <option value="">Sélectionnez un membre</option>
            <?php foreach ($membres as $membre): ?>
                <option value="<?= $membre['id_membre'] ?>">
                    <?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?>
                </option>
            <?php endforeach; ?>
        `;

            function addMembre() {
                const container = document.getElementById('membres-container');
                const newRow = document.createElement('div');
                newRow.className = 'membre-row';

                newRow.innerHTML = `
                <div class="membre-fields">
                    <div class="form-group">
                        <label>Membre</label>
                        <select name="membres[${membreIndex}][id_membre]" class="form-control">
                            ${membresOptions}
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Rôle</label>
                        <input type="text" name="membres[${membreIndex}][role_projet]" value="Membre" class="form-control" placeholder="Ex: Développeur...">
                    </div>
                    <div class="form-group">
                        <label>Date début</label>
                        <input type="date" name="membres[${membreIndex}][date_debut]" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Date fin</label>
                        <input type="date" name="membres[${membreIndex}][date_fin]" class="form-control">
                    </div>
                    <button type="button" class="btn-remove-membre" onclick="removeMembre(this)" title="Retirer">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

                container.appendChild(newRow);
                membreIndex++;
            }

            function removeMembre(button) {
                button.closest('.membre-row').remove();
            }

            // Partners Management
            let partenaireIndex = <?= !empty($projetPartenaires) ? count($projetPartenaires) : 0 ?>;

            const partenairesOptions = `
            <option value="">Sélectionnez un partenaire</option>
            <?php foreach ($partenaires as $p): ?>
                <option value="<?= $p['id_partenaire'] ?>">
                    <?= htmlspecialchars($p['nom']) ?> (<?= htmlspecialchars($p['type_partenaire']) ?>)
                </option>
            <?php endforeach; ?>
        `;

            function addPartenaire() {
                const container = document.getElementById('partenaires-container');
                const newRow = document.createElement('div');
                newRow.className = 'partenaire-row';

                newRow.innerHTML = `
                <div class="partenaire-fields">
                    <div class="form-group">
                        <label>Partenaire</label>
                        <select name="partenaires[${partenaireIndex}][id_partenaire]" class="form-control">
                            ${partenairesOptions}
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Rôle</label>
                        <select name="partenaires[${partenaireIndex}][role_partenaire]" class="form-control">
                            <option value="Collaborateur">Collaborateur</option>
                            <option value="Financement">Financement</option>
                            <option value="Sponsor">Sponsor</option>
                            <option value="Partenaire technique">Partenaire technique</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date début</label>
                        <input type="date" name="partenaires[${partenaireIndex}][date_debut]" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Date fin</label>
                        <input type="date" name="partenaires[${partenaireIndex}][date_fin]" class="form-control">
                    </div>
                    <button type="button" class="btn-remove-membre" onclick="removePartenaire(this)" title="Retirer">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

                container.appendChild(newRow);
                partenaireIndex++;
            }

            function removePartenaire(button) {
                button.closest('.partenaire-row').remove();
            }
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Render single member row
     */
    private function renderMembreRow($index, $pm, $membres)
    {
        ?>
        <div class="membre-row">
            <div class="membre-fields">
                <div class="form-group">
                    <label>Membre</label>
                    <select name="membres[<?= $index ?>][id_membre]" class="form-control">
                        <option value="">Sélectionnez un membre</option>
                        <?php foreach ($membres as $membre): ?>
                            <option value="<?= $membre['id_membre'] ?>" <?= $pm['id_membre'] == $membre['id_membre'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Rôle</label>
                    <input type="text" name="membres[<?= $index ?>][role_projet]" value="<?= htmlspecialchars($pm['role_projet'] ?? 'Membre') ?>" class="form-control">
                </div>
                <div class="form-group">
                    <label>Date début</label>
                    <input type="date" name="membres[<?= $index ?>][date_debut]" value="<?= $pm['date_debut'] ?? '' ?>" class="form-control">
                </div>
                <div class="form-group">
                    <label>Date fin</label>
                    <input type="date" name="membres[<?= $index ?>][date_fin]" value="<?= $pm['date_fin'] ?? '' ?>" class="form-control">
                </div>
                <button type="button" class="btn-remove-membre" onclick="removeMembre(this)" title="Retirer">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Render single partner row
     */
    private function renderPartenaireRow($index, $pp, $partenaires)
    {
        ?>
        <div class="partenaire-row">
            <div class="partenaire-fields">
                <div class="form-group">
                    <label>Partenaire</label>
                    <select name="partenaires[<?= $index ?>][id_partenaire]" class="form-control">
                        <option value="">Sélectionnez un partenaire</option>
                        <?php foreach ($partenaires as $p): ?>
                            <option value="<?= $p['id_partenaire'] ?>" <?= $pp['id_partenaire'] == $p['id_partenaire'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nom']) ?> (<?= htmlspecialchars($p['type_partenaire']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Rôle</label>
                    <select name="partenaires[<?= $index ?>][role_partenaire]" class="form-control">
                        <option value="Collaborateur" <?= ($pp['role_partenaire'] ?? '') === 'Collaborateur' ? 'selected' : '' ?>>Collaborateur</option>
                        <option value="Financement" <?= ($pp['role_partenaire'] ?? '') === 'Financement' ? 'selected' : '' ?>>Financement</option>
                        <option value="Sponsor" <?= ($pp['role_partenaire'] ?? '') === 'Sponsor' ? 'selected' : '' ?>>Sponsor</option>
                        <option value="Partenaire technique" <?= ($pp['role_partenaire'] ?? '') === 'Partenaire technique' ? 'selected' : '' ?>>Partenaire technique</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date début</label>
                    <input type="date" name="partenaires[<?= $index ?>][date_debut]" value="<?= $pp['date_debut'] ?? '' ?>" class="form-control">
                </div>
                <div class="form-group">
                    <label>Date fin</label>
                    <input type="date" name="partenaires[<?= $index ?>][date_fin]" value="<?= $pp['date_fin'] ?? '' ?>" class="form-control">
                </div>
                <button type="button" class="btn-remove-membre" onclick="removePartenaire(this)" title="Retirer">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Get badge variant based on status
     */
    private function getStatusVariant($statut)
    {
        $s = strtolower($statut);
        if (strpos($s, 'cours') !== false) return 'success';
        if (strpos($s, 'termin') !== false) return 'default';
        if (strpos($s, 'soumis') !== false) return 'warning';
        return 'info';
    }
}
?>