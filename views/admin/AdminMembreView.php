<?php
require_once __DIR__ . '/../BaseView.php';

class AdminMembreView extends BaseView
{

    public function __construct()
    {
        $this->pageTitle = 'Administration - Membres';
        $this->currentPage = 'admin';
    }

    /**
     * Tableau de gestion des membres
     */
    public function renderListe($membres, $stats)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-users"></i> Gestion des Membres</h1>
                    <a href="?page=admin&section=membres&action=create" class="btn-primary">
                        <i class="fas fa-plus"></i> Nouveau Membre
                    </a>
                </div>

                <!-- Statistiques -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: var(--primary-color)">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $stats['total'] ?></h3>
                            <p>Total Membres</p>
                        </div>
                    </div>

                    <?php foreach ($stats['par_grade'] as $stat): ?>
                        <div class="stat-card">
                            <div class="stat-icon" style="background: var(--accent-color)">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= $stat['total'] ?></h3>
                                <p><?= htmlspecialchars($stat['grade']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Filtres -->
                <div class="admin-filters">
                    <input type="text" id="search-table" placeholder="Rechercher un membre..." class="search-input">
                    <select id="filter-role-admin" class="filter-select">
                        <option value="">Tous les rôles</option>
                        <option value="admin">Administrateur</option>
                        <option value="membre">Membre</option>
                    </select>
                    <select id="filter-actif-admin" class="filter-select">
                        <option value="">Tous les statuts</option>
                        <option value="1">Actifs</option>
                        <option value="0">Inactifs</option>
                    </select>
                </div>

                <!-- Tableau -->
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Photo</th>
                                <th>Nom Complet</th>
                                <th>Email</th>
                                <th>Poste</th>
                                <th>Grade</th>
                                <th>Rôle</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($membres as $membre): ?>
                                <tr>
                                    <td><?= $membre['id_membre'] ?></td>
                                    <td>
                                        <div class="table-photo">
                                            <?php if ($membre['photo']): ?>
                                                <img src="<?= UPLOADS_URL . 'photos/' . $membre['photo'] ?>"
                                                    alt="<?= htmlspecialchars($membre['nom']) ?>">
                                            <?php else: ?>
                                                <i class="fas fa-user"></i>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($membre['email']) ?></td>
                                    <td><?= htmlspecialchars($membre['poste'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?= htmlspecialchars($membre['grade'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $membre['role'] === 'admin' ? 'danger' : 'primary' ?>">
                                            <?= htmlspecialchars(ucfirst($membre['role'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($membre['actif']): ?>
                                            <span class="badge badge-success">Actif</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="?page=membres&action=biographie&id=<?= $membre['id_membre'] ?>"
                                                class="btn-action btn-view" title="Voir" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?page=admin&section=membres&action=edit&id=<?= $membre['id_membre'] ?>"
                                                class="btn-action btn-edit" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button
                                                onclick="confirmDelete(<?= $membre['id_membre'] ?>, '<?= htmlspecialchars(addslashes($membre['nom'] . ' ' . $membre['prenom'])) ?>')"
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
                if (confirm(`Êtes-vous sûr de vouloir supprimer le membre "${nom}" ?\nCette action est irréversible.`)) {
                    window.location.href = '?page=admin&section=membres&action=delete&id=' + id;
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
            document.getElementById('filter-role-admin').addEventListener('change', function () {
                const role = this.value.toLowerCase();
                const rows = document.querySelectorAll('.admin-table tbody tr');

                rows.forEach(row => {
                    if (role === '') {
                        row.style.display = '';
                    } else {
                        const roleCell = row.cells[6].textContent.toLowerCase();
                        row.style.display = roleCell.includes(role) ? '' : 'none';
                    }
                });
            });

            document.getElementById('filter-actif-admin').addEventListener('change', function () {
                const actif = this.value;
                const rows = document.querySelectorAll('.admin-table tbody tr');

                rows.forEach(row => {
                    if (actif === '') {
                        row.style.display = '';
                    } else {
                        const statut = row.cells[7].textContent.toLowerCase();
                        const isActif = statut.includes('actif') && !statut.includes('inactif');
                        row.style.display = (actif === '1' && isActif) || (actif === '0' && !isActif) ? '' : 'none';
                    }
                });
            });
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Formulaire de création/modification
     */
    public function renderForm($membre = null)
    {
        $isEdit = $membre !== null;
        $this->pageTitle = $isEdit ? 'Modifier le membre' : 'Nouveau membre';

        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <div class="breadcrumb">
                    <a href="?page=admin">Administration</a>
                    <i class="fas fa-chevron-right"></i>
                    <a href="?page=admin&section=membres">Membres</a>
                    <i class="fas fa-chevron-right"></i>
                    <span><?= $isEdit ? 'Modifier' : 'Nouveau' ?></span>
                </div>

                <div class="form-container">
                    <h1><?= $isEdit ? 'Modifier le membre' : 'Nouveau membre' ?></h1>

                    <form method="POST" action="?page=admin&section=membres&action=<?= $isEdit ? 'update' : 'store' ?>"
                        class="admin-form" enctype="multipart/form-data">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?= $membre['id_membre'] ?>">
                        <?php endif; ?>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nom">Nom *</label>
                                <input type="text" id="nom" name="nom"
                                    value="<?= $isEdit ? htmlspecialchars($membre['nom']) : '' ?>" required
                                    class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="prenom">Prénom *</label>
                                <input type="text" id="prenom" name="prenom"
                                    value="<?= $isEdit ? htmlspecialchars($membre['prenom']) : '' ?>" required
                                    class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email"
                                    value="<?= $isEdit ? htmlspecialchars($membre['email']) : '' ?>" required
                                    class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="poste">Poste</label>
                                <input type="text" id="poste" name="poste"
                                    value="<?= $isEdit ? htmlspecialchars($membre['poste'] ?? '') : '' ?>" class="form-control"
                                    placeholder="Ex: Chercheur, Doctorant, etc.">
                            </div>

                            <div class="form-group">
                                <label for="grade">Grade</label>
                                <input type="text" id="grade" name="grade"
                                    value="<?= $isEdit ? htmlspecialchars($membre['grade'] ?? '') : '' ?>" class="form-control"
                                    placeholder="Ex: Professeur, Maître de conférences, etc.">
                            </div>

                            <div class="form-group">
                                <label for="role">Rôle *</label>
                                <select id="role" name="role" required class="form-control">
                                    <option value="membre" <?= $isEdit && $membre['role'] === 'membre' ? 'selected' : '' ?>>Membre
                                    </option>
                                    <option value="admin" <?= $isEdit && $membre['role'] === 'admin' ? 'selected' : '' ?>>
                                        Administrateur</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="actif">Statut *</label>
                                <select id="actif" name="actif" required class="form-control">
                                    <option value="1" <?= $isEdit && $membre['actif'] ? 'selected' : '' ?>>Actif</option>
                                    <option value="0" <?= $isEdit && !$membre['actif'] ? 'selected' : '' ?>>Inactif</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="mot_de_passe">Mot de passe <?= $isEdit ? '' : '*' ?></label>
                                <input type="password" id="mot_de_passe" name="mot_de_passe" <?= $isEdit ? '' : 'required' ?>
                                    class="form-control">
                                <?php if ($isEdit): ?>
                                    <small class="form-text">Laisser vide pour ne pas changer</small>
                                <?php endif; ?>
                            </div>

                            <div class="form-group full-width">
                                <label for="photo">Photo de profil</label>
                                <input type="file" id="photo" name="photo" accept="image/*" class="form-control">
                                <?php if ($isEdit && !empty($membre['photo'])): ?>
                                    <small class="form-text">
                                        Photo actuelle:
                                        <img src="<?= UPLOADS_URL . 'photos/' . $membre['photo'] ?>" alt="Photo"
                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%; vertical-align: middle;">
                                    </small>
                                <?php endif; ?>
                                <small class="form-text">Format: JPG, PNG, max 2 MB</small>
                            </div>

                            <div class="form-group full-width">
                                <label for="biographie">Biographie</label>
                                <textarea id="biographie" name="biographie" rows="4"
                                    class="form-control"><?= $isEdit ? htmlspecialchars($membre['biographie'] ?? '') : '' ?></textarea>
                            </div>

                            <div class="form-group full-width">
                                <label for="domaine_recherche">Domaines de recherche</label>
                                <textarea id="domaine_recherche" name="domaine_recherche" rows="3"
                                    class="form-control"><?= $isEdit ? htmlspecialchars($membre['domaine_recherche'] ?? '') : '' ?></textarea>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> <?= $isEdit ? 'Mettre à jour' : 'Créer' ?>
                            </button>
                            <a href="?page=admin&section=membres" class="btn-secondary">
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