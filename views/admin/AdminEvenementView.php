<?php
require_once __DIR__ . '/../BaseView.php';

class AdminEvenementView extends BaseView
{
    /**
     * Render events list
     */
    public function renderIndex($evenements)
    {
        $this->pageTitle = 'Gestion des Événements';
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-calendar-alt"></i> Gestion des Événements</h1>
                    <a href="?page=admin&section=evenements&action=create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Créer un événement
                    </a>
                </div>

                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Type</th>
                                <th>Date début</th>
                                <th>Lieu</th>
                                <th>Statut</th>
                                <th>Publié</th>
                                <th>Inscrits</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($evenements)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Aucun événement</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($evenements as $event): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($event['titre']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: <?= htmlspecialchars($event['type_couleur'] ?? '#007bff') ?>;">
                                                <?= htmlspecialchars($event['type_nom'] ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($event['date_debut'])) ?></td>
                                        <td><?= htmlspecialchars($event['lieu'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge badge-<?= $this->getStatusClass($event['statut']) ?>">
                                                <?= htmlspecialchars(ucfirst($event['statut'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <label class="toggle-switch" data-id="<?= $event['id_evenement'] ?>">
                                                <input type="checkbox" <?= $event['est_publie'] ? 'checked' : '' ?> 
                                                       onchange="togglePublish(<?= $event['id_evenement'] ?>)">
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <a href="?page=admin&section=evenements&action=inscriptions&id=<?= $event['id_evenement'] ?>" 
                                               class="badge badge-info">
                                                <?= $event['nb_inscrits'] ?> <i class="fas fa-users"></i>
                                            </a>
                                        </td>
                                        <td class="actions">
                                            <a href="?page=admin&section=evenements&action=edit&id=<?= $event['id_evenement'] ?>" 
                                               class="btn-icon" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="?page=admin&section=evenements&action=delete" 
                                                  style="display: inline;" 
                                                  onsubmit="return confirm('Supprimer cet événement ?');">
                                                <input type="hidden" name="id" value="<?= $event['id_evenement'] ?>">
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
            fetch('?page=admin&section=evenements&action=togglePublish', {
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
    public function renderCreate($types, $membres)
    {
        $this->pageTitle = 'Créer un Événement';
        $this->renderHeader();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-calendar-plus"></i> Créer un Événement</h1>
                    <a href="?page=admin&section=evenements" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>

                <form method="POST" action="?page=admin&section=evenements&action=store" enctype="multipart/form-data" class="admin-form">
                    <?php $this->renderEventForm($types, $membres); ?>
                </form>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    /**
     * Render edit form
     */
    public function renderEdit($evenement, $types, $membres)
    {
        $this->pageTitle = 'Modifier un Événement';
        $this->renderHeader();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-edit"></i> Modifier un Événement</h1>
                    <a href="?page=admin&section=evenements" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>

                <form method="POST" action="?page=admin&section=evenements&action=update" enctype="multipart/form-data" class="admin-form">
                    <input type="hidden" name="id_evenement" value="<?= $evenement['id_evenement'] ?>">
                    <?php $this->renderEventForm($types, $membres, $evenement); ?>
                </form>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    /**
     * Render event form fields
     */
    private function renderEventForm($types, $membres, $evenement = null)
    {
        $isEdit = $evenement !== null;
        ?>
        <div class="form-grid">
            <div class="form-group">
                <label for="titre">Titre *</label>
                <input type="text" id="titre" name="titre" required 
                       value="<?= $isEdit ? htmlspecialchars($evenement['titre']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="id_type_evenement">Type *</label>
                <select id="id_type_evenement" name="id_type_evenement" required>
                    <option value="">Sélectionner un type</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?= $type['id_type_evenement'] ?>" 
                                <?= $isEdit && $evenement['id_type_evenement'] == $type['id_type_evenement'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type['nom_type']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group full-width">
                <label for="description">Description *</label>
                <textarea id="description" name="description" rows="5" required><?= $isEdit ? htmlspecialchars($evenement['description']) : '' ?></textarea>
            </div>

            <div class="form-group">
                <label for="date_debut">Date début *</label>
                <input type="datetime-local" id="date_debut" name="date_debut" required 
                       value="<?= $isEdit ? date('Y-m-d\TH:i', strtotime($evenement['date_debut'])) : '' ?>">
            </div>

            <div class="form-group">
                <label for="date_fin">Date fin</label>
                <input type="datetime-local" id="date_fin" name="date_fin" 
                       value="<?= $isEdit && $evenement['date_fin'] ? date('Y-m-d\TH:i', strtotime($evenement['date_fin'])) : '' ?>">
            </div>

            <div class="form-group">
                <label for="lieu">Lieu</label>
                <input type="text" id="lieu" name="lieu" 
                       value="<?= $isEdit ? htmlspecialchars($evenement['lieu'] ?? '') : '' ?>">
            </div>

            <div class="form-group">
                <label for="capacite_max">Capacité maximale</label>
                <input type="number" id="capacite_max" name="capacite_max" min="0" 
                       value="<?= $isEdit ? $evenement['capacite_max'] : '' ?>">
            </div>

            <div class="form-group full-width">
                <label for="adresse">Adresse complète</label>
                <textarea id="adresse" name="adresse" rows="3"><?= $isEdit ? htmlspecialchars($evenement['adresse'] ?? '') : '' ?></textarea>
            </div>

            <div class="form-group">
                <label for="organisateur_id">Organisateur</label>
                <select id="organisateur_id" name="organisateur_id">
                    <option value="">Sélectionner un membre</option>
                    <?php foreach ($membres as $membre): ?>
                        <option value="<?= $membre['id_membre'] ?>" 
                                <?= $isEdit && $evenement['organisateur_id'] == $membre['id_membre'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="lien_inscription">Lien inscription externe</label>
                <input type="url" id="lien_inscription" name="lien_inscription" 
                       value="<?= $isEdit ? htmlspecialchars($evenement['lien_inscription'] ?? '') : '' ?>">
            </div>

            <div class="form-group">
                <label for="statut">Statut</label>
                <select id="statut" name="statut">
                    <option value="à venir" <?= $isEdit && $evenement['statut'] == 'à venir' ? 'selected' : '' ?>>À venir</option>
                    <option value="en cours" <?= $isEdit && $evenement['statut'] == 'en cours' ? 'selected' : '' ?>>En cours</option>
                    <option value="terminé" <?= $isEdit && $evenement['statut'] == 'terminé' ? 'selected' : '' ?>>Terminé</option>
                    <option value="annulé" <?= $isEdit && $evenement['statut'] == 'annulé' ? 'selected' : '' ?>>Annulé</option>
                </select>
            </div>

            <div class="form-group">
                <label for="image">Image</label>
                <input type="file" id="image" name="image" accept="image/*">
                <?php if ($isEdit && $evenement['image']): ?>
                    <img src="<?= UPLOADS_URL ?>evenements/<?= htmlspecialchars($evenement['image']) ?>" 
                         alt="Image actuelle" style="max-width: 200px; margin-top: 0.5rem;">
                <?php endif; ?>
            </div>

            <div class="form-group full-width">
                <label class="checkbox-label">
                    <input type="checkbox" name="publier" value="1" 
                           <?= $isEdit && $evenement['est_publie'] ? 'checked' : '' ?>>
                    <span>Publier cet événement (visible sur le site public)</span>
                </label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?= $isEdit ? 'Modifier' : 'Créer' ?>
            </button>
            <a href="?page=admin&section=evenements" class="btn btn-outline">Annuler</a>
        </div>
        <?php
    }

    /**
     * Render registrations page
     */
    public function renderInscriptions($evenement, $participants)
    {
        $this->pageTitle = 'Inscriptions - ' . $evenement['titre'];
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <div class="admin-header">
                    <div>
                        <h1><i class="fas fa-users"></i> Inscriptions</h1>
                        <p style="color: var(--gray-600);"><?= htmlspecialchars($evenement['titre']) ?></p>
                    </div>
                    <a href="?page=admin&section=evenements" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>

                <div class="stats-grid" style="margin-bottom: 2rem;">
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <div>
                            <div class="stat-value"><?= count($participants) ?></div>
                            <div class="stat-label">Total inscrits</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <div class="stat-value"><?= count(array_filter($participants, fn($p) => $p['statut_participation'] == 'confirmé')) ?></div>
                            <div class="stat-label">Confirmés</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-clock"></i>
                        <div>
                            <div class="stat-value"><?= count(array_filter($participants, fn($p) => $p['statut_participation'] == 'inscrit')) ?></div>
                            <div class="stat-label">En attente</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-user-check"></i>
                        <div>
                            <div class="stat-value"><?= count(array_filter($participants, fn($p) => $p['statut_participation'] == 'présent')) ?></div>
                            <div class="stat-label">Présents</div>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Participant</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Date inscription</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($participants)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Aucune inscription</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($participants as $p): ?>
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                <?php if ($p['photo']): ?>
                                                    <img src="<?= UPLOADS_URL ?>photos/<?= htmlspecialchars($p['photo']) ?>" 
                                                         style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                                <?php else: ?>
                                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                                        <?= strtoupper(substr($p['nom'] ?? 'P', 0, 1)) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?= htmlspecialchars(($p['nom'] ?? '') . ' ' . ($p['prenom'] ?? '')) ?></strong>
                                                    <?php if ($p['grade']): ?>
                                                        <div style="font-size: 0.85rem; color: var(--gray-600);">
                                                            <?= htmlspecialchars($p['grade']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($p['email']) ?></td>
                                        <td><?= htmlspecialchars($p['telephone'] ?? '-') ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($p['date_inscription'])) ?></td>
                                        <td>
                                            <select class="status-select" onchange="updateStatus(<?= $p['id_participation'] ?>, this.value)">
                                                <option value="inscrit" <?= $p['statut_participation'] == 'inscrit' ? 'selected' : '' ?>>Inscrit</option>
                                                <option value="confirmé" <?= $p['statut_participation'] == 'confirmé' ? 'selected' : '' ?>>Confirmé</option>
                                                <option value="présent" <?= $p['statut_participation'] == 'présent' ? 'selected' : '' ?>>Présent</option>
                                                <option value="absent" <?= $p['statut_participation'] == 'absent' ? 'selected' : '' ?>>Absent</option>
                                            </select>
                                        </td>
                                        <td class="actions">
                                            <button onclick="deleteParticipant(<?= $p['id_participation'] ?>)" 
                                                    class="btn-icon btn-danger" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
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
        function updateStatus(id, statut) {
            fetch('?page=admin&section=evenements&action=updateParticipantStatus', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_participation=' + id + '&statut=' + statut
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur lors de la mise à jour');
                }
            });
        }

        function deleteParticipant(id) {
            if (!confirm('Supprimer cette inscription ?')) return;
            
            fetch('?page=admin&section=evenements&action=deleteParticipant', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur lors de la suppression');
                }
            });
        }
        </script>

        <?php
        $this->renderFooter();
    }

    private function getStatusClass($statut)
    {
        switch ($statut) {
            case 'à venir': return 'info';
            case 'en cours': return 'success';
            case 'terminé': return 'default';
            case 'annulé': return 'danger';
            default: return 'default';
        }
    }
}
?>