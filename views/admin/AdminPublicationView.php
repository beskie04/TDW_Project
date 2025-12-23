<?php
require_once __DIR__ . '/../BaseView.php';

class AdminPublicationView extends BaseView {
    
    public function __construct() {
        $this->pageTitle = 'Administration - Publications';
        $this->currentPage = 'admin';
    }
    
    /**
     * Vue des publications en attente de validation
     */
    public function renderPending($publications) {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>
        
        <main class="admin-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-clock"></i> Publications en attente de validation</h1>
                    <a href="?page=admin&section=publications" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour à la liste
                    </a>
                </div>
                
                <?php if (empty($publications)): ?>
                    <div class="no-results">
                        <i class="fas fa-check-circle"></i>
                        <p>Aucune publication en attente de validation</p>
                    </div>
                <?php else: ?>
                
                <!-- Tableau -->
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Auteurs</th>
                                <th>Type</th>
                                <th>Année</th>
                                <th>Domaine</th>
                                <th>Date soumission</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($publications as $pub): ?>
                            <tr>
                                <td><?= $pub['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($pub['titre']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars(mb_substr($pub['auteurs'], 0, 50)) ?>...</td>
                                <td><?= htmlspecialchars(strtoupper($pub['type'])) ?></td>
                                <td><?= $pub['annee'] ?></td>
                                <td>
                                    <?= htmlspecialchars($pub['domaine_nom'] ?? 'N/A') ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($pub['created_at'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick="confirmValidate(<?= $pub['id'] ?>, '<?= htmlspecialchars(addslashes($pub['titre'])) ?>')" 
                                                class="btn-action btn-success" 
                                                title="Valider">
                                            <i class="fas fa-check"></i> Valider
                                        </button>
                                        
                                        <a href="?page=admin&section=publications&action=edit&id=<?= $pub['id'] ?>" 
                                           class="btn-action" 
                                           title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <button onclick="confirmDelete(<?= $pub['id'] ?>, '<?= htmlspecialchars(addslashes($pub['titre'])) ?>')" 
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
                
                <?php endif; ?>
            </div>
        </main>
        
        <style>
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .no-results i {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .no-results p {
            font-size: 1.2rem;
        }
        
        .btn-success {
            background: #28a745 !important;
        }
        
        .btn-success:hover {
            background: #218838 !important;
        }
        </style>
        
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
        
        <?php
        $this->renderFooter();
    }
    
    /**
     * Tableau de gestion des publications
     */
    public function renderListe($publications, $stats) {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>
        
        <main class="admin-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-file-alt"></i> Gestion des Publications</h1>
                    <div class="header-actions">
                        <a href="?page=admin&section=publications&action=pending" class="btn-warning">
                            <i class="fas fa-clock"></i> En attente (<?= $stats['en_attente'] ?>)
                        </a>
                        <a href="?page=admin&section=publications&action=create" class="btn-primary">
                            <i class="fas fa-plus"></i> Nouvelle Publication
                        </a>
                    </div>
                </div>
                
                <!-- Statistiques -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: var(--primary-color)">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $stats['total'] ?></h3>
                            <p>Total Publications</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: var(--success-color)">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $stats['validees'] ?></h3>
                            <p>Validées</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: var(--warning-color)">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $stats['en_attente'] ?></h3>
                            <p>En attente</p>
                        </div>
                    </div>
                    
                    <?php foreach ($stats['par_type'] as $stat): ?>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: var(--accent-color)">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $stat['total'] ?></h3>
                            <p><?= htmlspecialchars(ucfirst($stat['type'])) ?></p>
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
                                <th>Auteurs</th>
                                <th>Type</th>
                                <th>Année</th>
                                <th>Domaine</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($publications as $pub): ?>
                            <tr>
                                <td><?= $pub['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($pub['titre']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars(mb_substr($pub['auteurs'], 0, 50)) ?>...</td>
                                <td><?= htmlspecialchars(strtoupper($pub['type'])) ?></td>
                                <td><?= $pub['annee'] ?></td>
                                <td>
                                    <?= htmlspecialchars($pub['domaine_nom'] ?? 'N/A') ?>
                                </td>
                                <td>
                                    <?php if ($pub['validee']): ?>
                                        <span class="status-text status-success">Validée</span>
                                    <?php else: ?>
                                        <span class="status-text status-warning">En attente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if (!$pub['validee']): ?>
                                            <button onclick="confirmValidate(<?= $pub['id'] ?>, '<?= htmlspecialchars(addslashes($pub['titre'])) ?>')" 
                                                    class="btn-action" 
                                                    title="Valider">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <a href="?page=admin&section=publications&action=edit&id=<?= $pub['id'] ?>" 
                                           class="btn-action" 
                                           title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?= $pub['id'] ?>, '<?= htmlspecialchars(addslashes($pub['titre'])) ?>')" 
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
        /* Status text styling */
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
        
        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
            border: none;
        }
        
        .btn-warning:hover {
            background: linear-gradient(135deg, #f57c00 0%, #e64a19 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(255, 152, 0, 0.4);
        }
        
        .btn-warning i {
            font-size: 1.1rem;
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
        
        <?php
        $this->renderFooter();
    }
    
    /**
     * Formulaire de création/modification
     */
    public function renderForm($publication = null, $types, $domaines) {
        $isEdit = $publication !== null;
        $this->pageTitle = $isEdit ? 'Modifier la publication' : 'Nouvelle publication';
        
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>
        
        <main class="admin-wrapper">
            <div class="container">
                <div class="breadcrumb">
                    <a href="?page=admin">Administration</a>
                    <i class="fas fa-chevron-right"></i>
                    <a href="?page=admin&section=publications">Publications</a>
                    <i class="fas fa-chevron-right"></i>
                    <span><?= $isEdit ? 'Modifier' : 'Nouveau' ?></span>
                </div>
                
                <div class="form-container">
                    <h1><?= $isEdit ? 'Modifier la publication' : 'Nouvelle publication' ?></h1>
                    
                    <form method="POST" action="?page=admin&section=publications&action=<?= $isEdit ? 'update' : 'store' ?>" class="admin-form" enctype="multipart/form-data">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?= $publication['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="titre">Titre *</label>
                                <input type="text" 
                                       id="titre" 
                                       name="titre" 
                                       value="<?= $isEdit ? htmlspecialchars($publication['titre']) : '' ?>" 
                                       required 
                                       class="form-control">
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="auteurs">Auteurs *</label>
                                <textarea id="auteurs" 
                                          name="auteurs" 
                                          rows="2" 
                                          required 
                                          class="form-control"><?= $isEdit ? htmlspecialchars($publication['auteurs']) : '' ?></textarea>
                                <small class="form-text">Séparez les auteurs par des virgules</small>
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="resume">Résumé</label>
                                <textarea id="resume" 
                                          name="resume" 
                                          rows="4" 
                                          class="form-control"><?= $isEdit ? htmlspecialchars($publication['resume'] ?? '') : '' ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="type">Type *</label>
                                <select id="type" name="type" required class="form-control">
                                    <option value="">Sélectionnez un type</option>
                                    <?php foreach ($types as $key => $label): ?>
                                        <option value="<?= $key ?>" 
                                                <?= $isEdit && $publication['type'] == $key ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="annee">Année *</label>
                                <input type="number" 
                                       id="annee" 
                                       name="annee" 
                                       min="1900" 
                                       max="<?= date('Y') + 1 ?>" 
                                       value="<?= $isEdit ? $publication['annee'] : date('Y') ?>" 
                                       required 
                                       class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="id_thematique">Domaine *</label>
                                <select id="id_thematique" name="id_thematique" required class="form-control">
                                    <option value="">Sélectionnez un domaine</option>
                                    <?php foreach ($domaines as $dom): ?>
                                        <option value="<?= $dom['id_thematique'] ?>" 
                                                <?= $isEdit && $publication['id_thematique'] == $dom['id_thematique'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($dom['nom_thematique']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="doi">DOI</label>
                                <input type="text" 
                                       id="doi" 
                                       name="doi" 
                                       value="<?= $isEdit ? htmlspecialchars($publication['doi'] ?? '') : '' ?>" 
                                       class="form-control"
                                       placeholder="10.1234/example.2024.001">
                            </div>
                            
                            <div class="form-group">
                                <label for="date_publication">Date de publication</label>
                                <input type="date" 
                                       id="date_publication" 
                                       name="date_publication" 
                                       value="<?= $isEdit ? ($publication['date_publication'] ?? '') : '' ?>" 
                                       class="form-control">
                            </div>
                            
                            <?php if ($isEdit): ?>
                            <div class="form-group">
                                <label>Statut de validation</label>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" 
                                           id="validee" 
                                           name="validee" 
                                           value="1"
                                           <?= $publication['validee'] ? 'checked' : '' ?>
                                           style="width: auto;">
                                    <label for="validee" style="margin: 0;">Publication validée</label>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="form-group full-width">
                                <label for="fichier">Fichier PDF</label>
                                <input type="file" 
                                       id="fichier" 
                                       name="fichier" 
                                       accept=".pdf"
                                       class="form-control">
                                <?php if ($isEdit && !empty($publication['fichier'])): ?>
                                    <small class="form-text">
                                        Fichier actuel: <?= htmlspecialchars($publication['fichier']) ?>
                                        <a href="<?= UPLOADS_URL . 'publications/' . $publication['fichier'] ?>" target="_blank">
                                            <i class="fas fa-download"></i> Télécharger
                                        </a>
                                    </small>
                                <?php endif; ?>
                                <small class="form-text">Format PDF uniquement, max 10 MB</small>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> <?= $isEdit ? 'Mettre à jour' : 'Créer' ?>
                            </button>
                            <a href="?page=admin&section=publications" class="btn-secondary">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
        
        <?php
        $this->renderFooter();
    }
}
?>