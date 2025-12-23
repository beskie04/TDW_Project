<?php
require_once __DIR__ . '/../BaseView.php';

class AdminEquipementView extends BaseView
{

    public function __construct()
    {
        $this->pageTitle = 'Administration - Équipements';
        $this->currentPage = 'admin';
    }

    /**
     * Tableau de gestion des équipements
     */
    public function renderListe($equipements, $stats, $reservations)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-tools"></i> Gestion des Équipements et Ressources</h1>
                    <a href="?page=admin&section=equipements&action=create" class="btn-primary">
                        <i class="fas fa-plus"></i> Nouvel Équipement
                    </a>
                </div>

                <!-- Statistiques -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: var(--primary-color)">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $stats['total'] ?></h3>
                            <p>Total Équipements</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: var(--success-color)">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $stats['en_utilisation'] ?></h3>
                            <p>En utilisation</p>
                        </div>
                    </div>

                    <?php foreach ($stats['par_etat'] as $stat): ?>
                        <div class="stat-card">
                            <div class="stat-icon" style="background: var(--accent-color)">
                                <i class="fas fa-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= $stat['total'] ?></h3>
                                <p><?= htmlspecialchars(ETATS_EQUIPEMENTS[$stat['etat']] ?? $stat['etat']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Réservations récentes -->
                <?php if (!empty($reservations)): ?>
                    <div class="recent-reservations">
                        <h2><i class="fas fa-calendar"></i> Réservations récentes</h2>
                        <div class="table-container">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Équipement</th>
                                        <th>Membre</th>
                                        <th>Début</th>
                                        <th>Fin</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($reservations, 0, 10) as $res): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($res['equipement_nom']) ?></td>
                                            <td><?= htmlspecialchars($res['membre_nom'] . ' ' . $res['membre_prenom']) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($res['date_debut'])) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($res['date_fin'])) ?></td>
                                            <td>
                                                <span class="badge badge-<?= $res['statut'] === 'active' ? 'success' : 'secondary' ?>">
                                                    <?= ucfirst($res['statut']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($res['statut'] === 'active'): ?>
                                                    <button onclick="annulerReservation(<?= $res['id'] ?>)" class="btn-action btn-delete"
                                                        title="Annuler">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Filtres -->
                <div class="admin-filters">
                    <input type="text" id="search-table" placeholder="Rechercher un équipement..." class="search-input">
                    <select id="filter-type-admin" class="filter-select">
                        <option value="">Tous les types</option>
                        <?php foreach (TYPES_EQUIPEMENTS as $key => $label): ?>
                            <option value="<?= $key ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="filter-etat-admin" class="filter-select">
                        <option value="">Tous les états</option>
                        <?php foreach (ETATS_EQUIPEMENTS as $key => $label): ?>
                            <option value="<?= $key ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tableau -->
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Type</th>
                                <th>État</th>
                                <th>Description</th>
                                <th>Réservations</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($equipements as $eq): ?>
                                <tr>
                                    <td><?= $eq['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($eq['nom']) ?></strong></td>
                                    <td>
                                        <span class="badge badge-primary">
                                            <?= htmlspecialchars(TYPES_EQUIPEMENTS[$eq['type']] ?? $eq['type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $this->getEtatBadgeClass($eq['etat']) ?>">
                                            <?= htmlspecialchars(ETATS_EQUIPEMENTS[$eq['etat']] ?? $eq['etat']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars(mb_substr($eq['description'] ?? '', 0, 50)) ?>...</td>
                                    <td><?= $eq['nb_reservations_actives'] ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="?page=equipements&action=details&id=<?= $eq['id'] ?>"
                                                class="btn-action btn-view" title="Voir" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?page=admin&section=equipements&action=edit&id=<?= $eq['id'] ?>"
                                                class="btn-action btn-edit" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button
                                                onclick="confirmDelete(<?= $eq['id'] ?>, '<?= htmlspecialchars(addslashes($eq['nom'])) ?>')"
                                                class="btn-action btn-delete" title="Supprimer">
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

        <script>
            function confirmDelete(id, nom) {
                if (confirm(`Êtes-vous sûr de vouloir supprimer l'équipement "${nom}" ?\nCette action est irréversible.`)) {
                    window.location.href = '?page=admin&section=equipements&action=delete&id=' + id;
                }
            }

            function annulerReservation(id) {
                if (confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')) {
                    window.location.href = '?page=admin&section=equipements&action=annuler_reservation&id=' + id;
                }
            }

            // Recherche dans le tableau
            document.getElementById('search-table').addEventListener('input', function () {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('.admin-table tbody tr');

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });

            // Filtres
            document.getElementById('filter-type-admin').addEventListener('change', filterTable);
            document.getElementById('filter-etat-admin').addEventListener('change', filterTable);

            function filterTable() {
                const type = document.getElementById('filter-type-admin').value.toLowerCase();
                const etat = document.getElementById('filter-etat-admin').value.toLowerCase();
                const rows = document.querySelectorAll('.admin-table tbody tr');

                rows.forEach(row => {
                    const typeCell = row.cells[2].textContent.toLowerCase();
                    const etatCell = row.cells[3].textContent.toLowerCase();

                    const typeMatch = type === '' || typeCell.includes(type);
                    const etatMatch = etat === '' || etatCell.includes(etat);

                    row.style.display = (typeMatch && etatMatch) ? '' : 'none';
                });
            }
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Formulaire de création/modification
     */
    public function renderForm($equipement = null)
    {
        $isEdit = $equipement !== null;
        $this->pageTitle = $isEdit ? 'Modifier l\'équipement' : 'Nouvel équipement';

        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <div class="breadcrumb">
                    <a href="?page=admin">Administration</a>
                    <i class="fas fa-chevron-right"></i>
                    <a href="?page=admin&section=equipements">Équipements</a>
                    <i class="fas fa-chevron-right"></i>
                    <span><?= $isEdit ? 'Modifier' : 'Nouveau' ?></span>
                </div>

                <div class="form-container">
                    <h1><?= $isEdit ? 'Modifier l\'équipement' : 'Nouvel équipement' ?></h1>

                    <form method="POST" action="?page=admin&section=equipements&action=<?= $isEdit ? 'update' : 'store' ?>"
                        class="admin-form">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?= $equipement['id'] ?>">
                        <?php endif; ?>

                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="nom">Nom de l'équipement *</label>
                                <input type="text" id="nom" name="nom"
                                    value="<?= $isEdit ? htmlspecialchars($equipement['nom']) : '' ?>" required
                                    class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="type">Type *</label>
                                <select id="type" name="type" required class="form-control">
                                    <option value="">Sélectionnez un type</option>
                                    <?php foreach (TYPES_EQUIPEMENTS as $key => $label): ?>
                                        <option value="<?= $key ?>" <?= $isEdit && $equipement['type'] === $key ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="etat">État *</label>
                                <select id="etat" name="etat" required class="form-control">
                                    <?php foreach (ETATS_EQUIPEMENTS as $key => $label): ?>
                                        <option value="<?= $key ?>" <?= $isEdit && $equipement['etat'] === $key ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group full-width">
                                <label for="description">Description *</label>
                                <textarea id="description" name="description" rows="3" required
                                    class="form-control"><?= $isEdit ? htmlspecialchars($equipement['description'] ?? '') : '' ?></textarea>
                            </div>

                            <div class="form-group full-width">
                                <label for="specifications">Spécifications techniques</label>
                                <textarea id="specifications" name="specifications" rows="3"
                                    class="form-control"><?= $isEdit ? htmlspecialchars($equipement['specifications'] ?? '') : '' ?></textarea>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> <?= $isEdit ? 'Mettre à jour' : 'Créer' ?>
                            </button>
                            <a href="?page=admin&section=equipements" class="btn-secondary">
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

    /**
     * Classe CSS pour le badge d'état
     */
    private function getEtatBadgeClass($etat)
    {
        switch ($etat) {
            case 'libre':
                return 'success';
            case 'reserve':
                return 'warning';
            case 'en_maintenance':
                return 'danger';
            default:
                return 'secondary';
        }
    }
}
?>