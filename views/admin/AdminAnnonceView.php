<?php
require_once __DIR__ . '/../BaseView.php';

class AdminAnnonceView extends BaseView
{
    /**
     * Render announcements list
     */
    public function renderIndex($annonces)
    {
         parent::__construct();
        $this->pageTitle = 'Gestion des Annonces';
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-bullhorn"></i> Gestion des Annonces Publiques</h1>
                    <a href="?page=admin&section=annonces&action=create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Créer une annonce
                    </a>
                </div>

                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Type</th>
                                <th>Période</th>
                                <th>Priorité</th>
                                <th>Publié</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($annonces)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Aucune annonce</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($annonces as $annonce): ?>
                                    <?php
                                    $today = date('Y-m-d');
                                    $isActive = $annonce['est_publie'] && 
                                               $annonce['date_debut'] <= $today && 
                                               (empty($annonce['date_fin']) || $annonce['date_fin'] >= $today);
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($annonce['titre']) ?></strong>
                                            <div style="font-size: 0.85rem; color: var(--gray-600);">
                                                Par <?= htmlspecialchars($annonce['auteur_nom'] . ' ' . $annonce['auteur_prenom']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $this->getTypeClass($annonce['type_annonce']) ?>">
                                                <?= htmlspecialchars(ucfirst($annonce['type_annonce'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($annonce['date_debut'])) ?>
                                            <?php if ($annonce['date_fin']): ?>
                                                <br><small>→ <?= date('d/m/Y', strtotime($annonce['date_fin'])) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?= $annonce['priorite'] ?></span>
                                        </td>
                                        <td>
                                            <label class="toggle-switch" data-id="<?= $annonce['id_annonce'] ?>">
                                                <input type="checkbox" <?= $annonce['est_publie'] ? 'checked' : '' ?> 
                                                       onchange="togglePublish(<?= $annonce['id_annonce'] ?>)">
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <?php if ($isActive): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php elseif ($annonce['date_debut'] > $today): ?>
                                                <span class="badge badge-info">À venir</span>
                                            <?php else: ?>
                                                <span class="badge badge-default">Expirée</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="actions">
                                            <a href="?page=admin&section=annonces&action=edit&id=<?= $annonce['id_annonce'] ?>" 
                                               class="btn-icon" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="?page=admin&section=annonces&action=delete" 
                                                  style="display: inline;" 
                                                  onsubmit="return confirm('Supprimer cette annonce ?');">
                                                <input type="hidden" name="id" value="<?= $annonce['id_annonce'] ?>">
                                                <button type="submit" class="btn-icon btn-danger" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <script>
        function togglePublish(id) {
            fetch('?page=admin&section=annonces&action=togglePublish', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Erreur lors de la mise à jour');
                    location.reload();
                }
            });
        }
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Render create form
     */
    public function renderCreate()
    {
        $this->pageTitle = 'Créer une Annonce';
        $this->renderHeader();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-plus"></i> Créer une Annonce</h1>
                    <a href="?page=admin&section=annonces" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>

                <form method="POST" action="?page=admin&section=annonces&action=store" class="admin-form">
                    <?php $this->renderAnnonceForm(); ?>
                </form>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    /**
     * Render edit form
     */
    public function renderEdit($annonce)
    {
        $this->pageTitle = 'Modifier une Annonce';
        $this->renderHeader();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-edit"></i> Modifier une Annonce</h1>
                    <a href="?page=admin&section=annonces" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>

                <form method="POST" action="?page=admin&section=annonces&action=update" class="admin-form">
                    <input type="hidden" name="id_annonce" value="<?= $annonce['id_annonce'] ?>">
                    <?php $this->renderAnnonceForm($annonce); ?>
                </form>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    /**
     * Render announcement form fields
     */
    private function renderAnnonceForm($annonce = null)
    {
        $isEdit = $annonce !== null;
        ?>
        <div class="form-grid">
            <div class="form-group full-width">
                <label for="titre">Titre *</label>
                <input type="text" id="titre" name="titre" required 
                       value="<?= $isEdit ? htmlspecialchars($annonce['titre']) : '' ?>">
            </div>

            <div class="form-group full-width">
                <label for="contenu">Contenu *</label>
                <textarea id="contenu" name="contenu" rows="6" required><?= $isEdit ? htmlspecialchars($annonce['contenu']) : '' ?></textarea>
            </div>

            <div class="form-group">
                <label for="type_annonce">Type *</label>
                <select id="type_annonce" name="type_annonce" required>
                    <option value="info" <?= $isEdit && $annonce['type_annonce'] == 'info' ? 'selected' : '' ?>>Info</option>
                    <option value="important" <?= $isEdit && $annonce['type_annonce'] == 'important' ? 'selected' : '' ?>>Important</option>
                    <option value="urgent" <?= $isEdit && $annonce['type_annonce'] == 'urgent' ? 'selected' : '' ?>>Urgent</option>
                    <option value="evenement" <?= $isEdit && $annonce['type_annonce'] == 'evenement' ? 'selected' : '' ?>>Événement</option>
                </select>
            </div>

            <div class="form-group">
                <label for="priorite">Priorité (0-10)</label>
                <input type="number" id="priorite" name="priorite" min="0" max="10" 
                       value="<?= $isEdit ? $annonce['priorite'] : '5' ?>">
                <small>Plus la priorité est élevée, plus l'annonce apparaît en haut</small>
            </div>

            <div class="form-group">
                <label for="date_debut">Date début *</label>
                <input type="date" id="date_debut" name="date_debut" required 
                       value="<?= $isEdit ? $annonce['date_debut'] : date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label for="date_fin">Date fin (optionnel)</label>
                <input type="date" id="date_fin" name="date_fin" 
                       value="<?= $isEdit && $annonce['date_fin'] ? $annonce['date_fin'] : '' ?>">
                <small>Si vide, l'annonce reste active indéfiniment</small>
            </div>

            <div class="form-group full-width">
                <label class="checkbox-label">
                    <input type="checkbox" name="publier" value="1" 
                           <?= $isEdit && $annonce['est_publie'] ? 'checked' : 'checked' ?>>
                    <span>Publier cette annonce (visible sur le site public)</span>
                </label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?= $isEdit ? 'Modifier' : 'Créer' ?>
            </button>
            <a href="?page=admin&section=annonces" class="btn btn-outline">Annuler</a>
        </div>
        <?php
    }

    private function getTypeClass($type)
    {
        switch ($type) {
            case 'urgent': return 'danger';
            case 'important': return 'warning';
            case 'evenement': return 'success';
            default: return 'info';
        }
    }
}
?>