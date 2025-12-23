<?php
require_once __DIR__ . '/../BaseView.php';

class AdminProjetView extends BaseView {
    
    public function __construct() {
        $this->pageTitle = 'Administration - Projets';
        $this->currentPage = 'admin';
    }
    
    /**
     * Tableau de gestion des projets
     */
    public function renderListe($projets, $stats) {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>
        
        <main class="admin-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-project-diagram"></i> Gestion des Projets</h1>
                    <a href="?page=admin&section=projets&action=create" class="btn-primary">
                        <i class="fas fa-plus"></i> Nouveau Projet
                    </a>
                </div>
                
                <!-- Statistiques -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: var(--primary-color)">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= count($projets) ?></h3>
                            <p>Total Projets</p>
                        </div>
                    </div>
                    
                    <?php foreach ($stats['par_statut'] as $stat): ?>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: var(--accent-color)">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $stat['total'] ?></h3>
                            <p><?= htmlspecialchars($stat['nom_statut']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Tableau -->
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Responsable</th>
                                <th>Thématique</th>
                                <th>Statut</th>
                                <th>Date début</th>
                                <th>Budget</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projets as $projet): ?>
                            <tr>
                                <td><?= $projet['id_projet'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($projet['titre']) ?></strong>
                                </td>
                                <td>
                                    <?= htmlspecialchars(($projet['responsable_nom'] ?? '') . ' ' . ($projet['responsable_prenom'] ?? '')) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($projet['thematique_nom'] ?? 'N/A') ?>
                                </td>
                                <td>
                                    <span class="status-text status-<?= $this->getStatusClass($projet['statut_nom'] ?? '') ?>">
                                        <?= htmlspecialchars($projet['statut_nom'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($projet['date_debut'])) ?></td>
                                <td><?= $projet['budget'] ? number_format($projet['budget'], 0, ',', ' ') . ' DZD' : 'N/A' ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?page=projets&action=details&id=<?= $projet['id_projet'] ?>" 
                                           class="btn-action" 
                                           title="Voir" 
                                           target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?page=admin&section=projets&action=edit&id=<?= $projet['id_projet'] ?>" 
                                           class="btn-action" 
                                           title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?= $projet['id_projet'] ?>, '<?= htmlspecialchars(addslashes($projet['titre'])) ?>')" 
                                                class="btn-action" 
                                                title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
        
        <style>
        /* Status text styling - colored text without background */
        .status-text {
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .status-text.status-success {
            color: #28a745;
        }
        
        .status-text.status-warning {
            color: #ffc107;
        }
        
        .status-text.status-secondary {
            color: #6c757d;
        }
        
        .status-text.status-info {
            color: #17a2b8;
        }
        
        /* Action buttons - uniform neutral style */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        
        .btn-action {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-action:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .btn-action i {
            font-size: 1rem;
        }
        </style>
        
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
     * Formulaire de création/modification
     */
    public function renderForm($projet = null, $thematiques, $statuts, $typesFinancement, $membres, $projetMembres = []) {
        $isEdit = $projet !== null;
        $this->pageTitle = $isEdit ? 'Modifier le projet' : 'Nouveau projet';
        
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>
        
        <main class="admin-wrapper">
            <div class="container">
                <div class="breadcrumb">
                    <a href="?page=admin">Administration</a>
                    <i class="fas fa-chevron-right"></i>
                    <a href="?page=admin&section=projets">Projets</a>
                    <i class="fas fa-chevron-right"></i>
                    <span><?= $isEdit ? 'Modifier' : 'Nouveau' ?></span>
                </div>
                
                <div class="form-container">
                    <h1><?= $isEdit ? 'Modifier le projet' : 'Nouveau projet' ?></h1>
                    
                    <form method="POST" action="?page=admin&section=projets&action=<?= $isEdit ? 'update' : 'store' ?>" class="admin-form">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?= $projet['id_projet'] ?>">
                        <?php endif; ?>
                        
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="titre">Titre du projet *</label>
                                <input type="text" 
                                       id="titre" 
                                       name="titre" 
                                       value="<?= $isEdit ? htmlspecialchars($projet['titre']) : '' ?>" 
                                       required 
                                       class="form-control">
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="description">Description *</label>
                                <textarea id="description" 
                                          name="description" 
                                          rows="4" 
                                          required 
                                          class="form-control"><?= $isEdit ? htmlspecialchars($projet['description']) : '' ?></textarea>
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="objectifs">Objectifs</label>
                                <textarea id="objectifs" 
                                          name="objectifs" 
                                          rows="4" 
                                          class="form-control"><?= $isEdit ? htmlspecialchars($projet['objectifs'] ?? '') : '' ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="id_thematique">Thématique *</label>
                                <select id="id_thematique" name="id_thematique" required class="form-control">
                                    <option value="">Sélectionnez une thématique</option>
                                    <?php foreach ($thematiques as $them): ?>
                                        <option value="<?= $them['id'] ?>" 
                                                <?= $isEdit && isset($projet['id_thematique']) && $projet['id_thematique'] == $them['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($them['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="id_statut">Statut *</label>
                                <select id="id_statut" name="id_statut" required class="form-control">
                                    <option value="">Sélectionnez un statut</option>
                                    <?php foreach ($statuts as $stat): ?>
                                        <option value="<?= $stat['id_statut'] ?>" 
                                                <?= $isEdit && isset($projet['id_statut']) && $projet['id_statut'] == $stat['id_statut'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($stat['nom_statut']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="id_type_financement">Type de financement *</label>
                                <select id="id_type_financement" name="id_type_financement" required class="form-control">
                                    <option value="">Sélectionnez un type</option>
                                    <?php foreach ($typesFinancement as $type): ?>
                                        <option value="<?= $type['id'] ?>" 
                                                <?= $isEdit && isset($projet['id_type_financement']) && $projet['id_type_financement'] == $type['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($type['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="responsable_id">Responsable *</label>
                                <select id="responsable_id" name="responsable_id" required class="form-control">
                                    <option value="">Sélectionnez un responsable</option>
                                    <?php foreach ($membres as $membre): ?>
                                        <option value="<?= $membre['id_membre'] ?>" 
                                                <?= $isEdit && $projet['responsable_id'] == $membre['id_membre'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        
                            <div class="form-group">
                                <label for="date_debut">Date de début *</label>
                                <input type="date" 
                                       id="date_debut" 
                                       name="date_debut" 
                                       value="<?= $isEdit ? $projet['date_debut'] : '' ?>" 
                                       required 
                                       class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="date_fin">Date de fin</label>
                                <input type="date" 
                                       id="date_fin" 
                                       name="date_fin" 
                                       value="<?= $isEdit ? ($projet['date_fin'] ?? '') : '' ?>" 
                                       class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="budget">Budget (DZD)</label>
                                <input type="number" 
                                       id="budget" 
                                       name="budget" 
                                       value="<?= $isEdit ? ($projet['budget'] ?? '') : '' ?>" 
                                       step="0.01" 
                                       class="form-control">
                            </div>
                        </div>
                        
                        <!-- Section des membres du projet -->
                        <div class="form-section">
                            <h3>
                                <i class="fas fa-users"></i> Membres du projet
                                <span style="font-size: 0.9rem; font-weight: normal; color: #666;">(optionnel)</span>
                            </h3>
                            
                            <div id="membres-container">
                                <?php if (!empty($projetMembres)): ?>
                                    <?php foreach ($projetMembres as $index => $pm): ?>
                                        <div class="membre-row" data-index="<?= $index ?>">
                                            <div class="membre-fields">
                                                <div class="form-group">
                                                    <label>Membre</label>
                                                    <select name="membres[<?= $index ?>][id_membre]" class="form-control membre-select">
                                                        <option value="">Sélectionnez un membre</option>
                                                        <?php foreach ($membres as $membre): ?>
                                                            <option value="<?= $membre['id_membre'] ?>" 
                                                                    <?= $pm['id_membre'] == $membre['id_membre'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label>Rôle</label>
                                                    <input type="text" 
                                                           name="membres[<?= $index ?>][role_projet]" 
                                                           value="<?= htmlspecialchars($pm['role_projet'] ?? 'Membre') ?>"
                                                           class="form-control" 
                                                           placeholder="Ex: Développeur, Chercheur...">
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label>Date début</label>
                                                    <input type="date" 
                                                           name="membres[<?= $index ?>][date_debut]" 
                                                           value="<?= $pm['date_debut'] ?? '' ?>"
                                                           class="form-control">
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label>Date fin</label>
                                                    <input type="date" 
                                                           name="membres[<?= $index ?>][date_fin]" 
                                                           value="<?= $pm['date_fin'] ?? '' ?>"
                                                           class="form-control">
                                                </div>
                                                
                                                <button type="button" class="btn-remove-membre" onclick="removeMembre(this)" title="Retirer ce membre">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <button type="button" class="btn-secondary" onclick="addMembre()">
                                <i class="fas fa-plus"></i> Ajouter un membre
                            </button>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> <?= $isEdit ? 'Mettre à jour' : 'Créer' ?>
                            </button>
                            <a href="?page=admin&section=projets" class="btn-secondary">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
        
        <style>
        .form-section {
            margin-top: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .form-section h3 {
            margin-bottom: 1rem;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        #membres-container {
            margin-bottom: 1rem;
        }
        
        .membre-row {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
        }
        
        .membre-fields {
            display: grid;
            grid-template-columns: 2fr 1.5fr 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }
        
        .membre-fields .form-group {
            margin-bottom: 0;
        }
        
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
            .membre-fields {
                grid-template-columns: 1fr;
            }
            
            .btn-remove-membre {
                width: 100%;
                border-radius: 8px;
            }
        }
        </style>
        
        <script>
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
            newRow.dataset.index = membreIndex;
            
            newRow.innerHTML = `
                <div class="membre-fields">
                    <div class="form-group">
                        <label>Membre</label>
                        <select name="membres[${membreIndex}][id_membre]" class="form-control membre-select">
                            ${membresOptions}
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Rôle</label>
                        <input type="text" 
                               name="membres[${membreIndex}][role_projet]" 
                               value="Membre"
                               class="form-control" 
                               placeholder="Ex: Développeur, Chercheur...">
                    </div>
                    
                    <div class="form-group">
                        <label>Date début</label>
                        <input type="date" 
                               name="membres[${membreIndex}][date_debut]" 
                               class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>Date fin</label>
                        <input type="date" 
                               name="membres[${membreIndex}][date_fin]" 
                               class="form-control">
                    </div>
                    
                    <button type="button" class="btn-remove-membre" onclick="removeMembre(this)" title="Retirer ce membre">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            container.appendChild(newRow);
            membreIndex++;
        }
        
        function removeMembre(button) {
            const row = button.closest('.membre-row');
            row.remove();
        }
        </script>
        
        <?php
        $this->renderFooter();
    }
    
    /**
     * Classe CSS pour le statut (retourne seulement le nom de la classe)
     */
    private function getStatusClass($statut) {
        $statut = strtolower($statut);
        if (strpos($statut, 'cours') !== false) return 'success';
        if (strpos($statut, 'termin') !== false) return 'secondary';
        if (strpos($statut, 'soumis') !== false) return 'warning';
        return 'info';
    }
}
?>