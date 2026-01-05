<?php
require_once __DIR__ . '/../BaseView.php';
require_once __DIR__ . '/../components/Table.php';
require_once __DIR__ . '/../components/Filter.php';
require_once __DIR__ . '/../components/FilterBar.php';
require_once __DIR__ . '/../components/SearchInput.php';
require_once __DIR__ . '/../components/Button.php';
require_once __DIR__ . '/../components/Badge.php';
require_once __DIR__ . '/../components/StatCard.php';
require_once __DIR__ . '/../components/Modal.php';
require_once __DIR__ . '/../components/FormInput.php';
require_once __DIR__ . '/../components/Avatar.php';

class AdminMembreView extends BaseView
{
    protected $pageTitle = 'Gestion des Membres - Administration';
    protected $currentPage = 'admin';

    public function render($membres, $stats, $roles, $grades, $specialites, $filters)
    {
        $this->renderHeader();
        ?>

        <div class="admin-container">
            <?php $this->renderFlashMessage(); ?>

            <!-- Page Header -->
            <div class="admin-header">
                <div>
                    <h1><i class="fas fa-users"></i> Gestion des Membres</h1>
                    <p>Gérer les utilisateurs du laboratoire</p>
                </div>
                <div>
                    <?php
                    Button::render([
                        'text' => 'Ajouter un membre',
                        'icon' => 'fa-plus',
                        'variant' => 'primary',
                        'onClick' => "openModal('modal-add-membre')"
                    ]);
                    ?>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <?php
                StatCard::render([
                    'value' => $stats['total'],
                    'label' => 'Total Membres',
                    'icon' => 'fas fa-users',
                    'color' => '#3b82f6'
                ]);

                StatCard::render([
                    'value' => $stats['actifs'],
                    'label' => 'Actifs',
                    'icon' => 'fas fa-check-circle',
                    'color' => '#10b981'
                ]);

                StatCard::render([
                    'value' => $stats['enseignants'],
                    'label' => 'Enseignants',
                    'icon' => 'fas fa-chalkboard-teacher',
                    'color' => '#8b5cf6'
                ]);

                StatCard::render([
                    'value' => $stats['doctorants'],
                    'label' => 'Doctorants',
                    'icon' => 'fas fa-graduation-cap',
                    'color' => '#f59e0b'
                ]);

                StatCard::render([
                    'value' => $stats['admins'],
                    'label' => 'Administrateurs',
                    'icon' => 'fas fa-user-shield',
                    'color' => '#ef4444'
                ]);
                ?>
            </div>

            <!-- Filters -->
            <?php $this->renderFilters($roles, $grades, $specialites, $filters); ?>

            <!-- Members Table -->
            <div class="admin-card">
                <?php $this->renderMembresTable($membres); ?>
            </div>

            <!-- Add Member Modal -->
            <?php $this->renderAddModal($roles); ?>

            <!-- Edit Member Modal -->
            <?php $this->renderEditModal($roles); ?>
        </div>

        <script src="<?= ASSETS_URL ?>js/admin-membres.js"></script>

        <?php
        $this->renderFooter();
    }

    /**
     * Render filters section
     */
    private function renderFilters($roles, $grades, $specialites, $filters)
    {
        ?>
        <div class="filters-card">
            <?php
            FilterBar::render(
                [
                    'resetId' => 'reset-filters',
                    'resetText' => 'Réinitialiser'
                ],
                function () use ($roles, $grades, $specialites, $filters) {
                    SearchInput::render([
                        'id' => 'search-membres',
                        'placeholder' => 'Rechercher par nom, email...',
                        'value' => $filters['search'] ?? '',
                        'onInput' => 'applyFilters()'
                    ]);

                    Filter::render([
                        'id' => 'filter-role',
                        'label' => 'Rôle',
                        'icon' => 'fas fa-user-tag',
                        'options' => $roles,
                        'placeholder' => 'Tous les rôles',
                        'onChange' => 'applyFilters()'
                    ]);

                    if (!empty($grades)) {
                        Filter::render([
                            'id' => 'filter-grade',
                            'label' => 'Grade',
                            'icon' => 'fas fa-award',
                            'options' => $grades,
                            'placeholder' => 'Tous les grades',
                            'onChange' => 'applyFilters()'
                        ]);
                    }

                    if (!empty($specialites)) {
                        Filter::render([
                            'id' => 'filter-specialite',
                            'label' => 'Spécialité',
                            'icon' => 'fas fa-flask',
                            'options' => $specialites,
                            'placeholder' => 'Toutes les spécialités',
                            'onChange' => 'applyFilters()'
                        ]);
                    }

                    Filter::render([
                        'id' => 'filter-actif',
                        'label' => 'Statut',
                        'icon' => 'fas fa-toggle-on',
                        'options' => [
                            ['value' => '1', 'text' => 'Actif'],
                            ['value' => '0', 'text' => 'Inactif']
                        ],
                        'placeholder' => 'Tous les statuts',
                        'onChange' => 'applyFilters()'
                    ]);

                    Filter::render([
                        'id' => 'filter-min-publications',
                        'label' => 'Min. Publications',
                        'icon' => 'fas fa-file-alt',
                        'options' => [
                            ['value' => '1', 'text' => '1+'],
                            ['value' => '5', 'text' => '5+'],
                            ['value' => '10', 'text' => '10+'],
                            ['value' => '20', 'text' => '20+']
                        ],
                        'placeholder' => 'Toutes',
                        'onChange' => 'applyFilters()'
                    ]);

                    Filter::render([
                        'id' => 'filter-min-projets',
                        'label' => 'Min. Projets',
                        'icon' => 'fas fa-project-diagram',
                        'options' => [
                            ['value' => '1', 'text' => '1+'],
                            ['value' => '3', 'text' => '3+'],
                            ['value' => '5', 'text' => '5+'],
                            ['value' => '10', 'text' => '10+']
                        ],
                        'placeholder' => 'Tous',
                        'onChange' => 'applyFilters()'
                    ]);
                }
            );
            ?>
        </div>

        <script>
            function applyFilters() {
                const search = document.getElementById('search-membres').value;
                const role = document.getElementById('filter-role').value;
                const grade = document.getElementById('filter-grade')?.value || '';
                const specialite = document.getElementById('filter-specialite')?.value || '';
                const actif = document.getElementById('filter-actif').value;
                const minPublications = document.getElementById('filter-min-publications').value;
                const minProjets = document.getElementById('filter-min-projets').value;

                const params = new URLSearchParams({
                    page: 'admin',
                    section: 'membres',
                    search, role, grade, specialite, actif,
                    min_publications: minPublications,
                    min_projets: minProjets
                });

                window.location.href = '?' + params.toString();
            }

            document.getElementById('reset-filters')?.addEventListener('click', () => {
                window.location.href = '?page=admin&section=membres';
            });
        </script>
        <?php
    }

    /**
     * Render members table
     */
    private function renderMembresTable($membres)
    {
        $headers = ['Photo', 'Nom', 'Email', 'Rôle', 'Type', 'Grade', 'Publications', 'Projets', 'Statut', 'Actions'];

        Table::render(
            [
                'headers' => $headers,
                'rows' => $membres,
                'striped' => true,
                'hoverable' => true,
                'responsive' => true
            ],
            function ($membre, $index) {
                $statusClass = $membre['actif'] ? 'success' : 'danger';
                $statusText = $membre['actif'] ? 'Actif' : 'Inactif';
                ?>
            <tr>
                <td>
                    <?php
                        Avatar::render([
                            'src' => $membre['photo'] ? ASSETS_URL . 'uploads/photos/' . $membre['photo'] : null,
                            'name' => $membre['prenom'] . ' ' . $membre['nom'],
                            'size' => 'sm'
                        ]);
                        ?>
                </td>
                <td>
                    <strong>
                        <?= htmlspecialchars($membre['prenom'] . ' ' . $membre['nom']) ?>
                    </strong>
                </td>
                <td>
                    <?= htmlspecialchars($membre['email']) ?>
                </td>
                <td>
                    <?php
                        Badge::render([
                            'text' => ucfirst($membre['role']),
                            'variant' => 'secondary'
                        ]);
                        ?>
                </td>
                <td>
                    <?php
                        if ($membre['role_systeme'] === 'admin') {
                            Badge::render([
                                'text' => 'Admin',
                                'variant' => 'danger'
                            ]);
                        } else {
                            Badge::render([
                                'text' => 'User',
                                'variant' => 'info'
                            ]);
                        }
                        ?>
                </td>
                <td>
                    <?= htmlspecialchars($membre['grade']) ?>
                </td>
                <td><span class="badge-count">
                        <?= $membre['nb_publications'] ?>
                    </span></td>
                <td><span class="badge-count">
                        <?= $membre['nb_projets'] ?>
                    </span></td>
                <td>
                    <?php
                        Badge::render([
                            'text' => $statusText,
                            'variant' => $statusClass
                        ]);
                        ?>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-icon btn-primary" onclick='editMembre(<?= json_encode($membre) ?>)' title="Modifier">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="?page=admin&section=membres&action=toggleStatus&id=<?= $membre['id_membre'] ?>"
                            class="btn-icon btn-warning" onclick="return confirm('Changer le statut de ce membre ?')"
                            title="<?= $membre['actif'] ? 'Désactiver' : 'Activer' ?>">
                            <i class="fas fa-toggle-<?= $membre['actif'] ? 'on' : 'off' ?>"></i>
                        </a>
                        <a href="?page=admin&section=membres&action=delete&id=<?= $membre['id_membre'] ?>"
                            class="btn-icon btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce membre ?')"
                            title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php
            }
        );

        if (empty($membres)) {
            echo '<div class="empty-state"><p>Aucun membre trouvé</p></div>';
        }
    }

    /**
     * Render Add Member Modal
     */
    private function renderAddModal($roles)
    {
        $errors = $_SESSION['errors'] ?? [];
        $oldData = $_SESSION['old_data'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old_data']);

        $rolesSysteme = [
            'user' => 'Utilisateur',
            'admin' => 'Administrateur'
        ];

        Modal::render(
            [
                'id' => 'modal-add-membre',
                'title' => 'Ajouter un Membre',
                'size' => 'large'
            ],
            function () use ($roles, $rolesSysteme, $errors, $oldData) {
                ?>
            <form method="POST" action="?page=admin&section=membres&action=store" id="form-add-membre">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nom <span class="required">*</span></label>
                        <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($oldData['nom'] ?? '') ?>"
                            required>
                        <?php if (isset($errors['nom'])): ?>
                            <span class="error-text">
                                <?= $errors['nom'] ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Prénom <span class="required">*</span></label>
                        <input type="text" name="prenom" class="form-control"
                            value="<?= htmlspecialchars($oldData['prenom'] ?? '') ?>" required>
                        <?php if (isset($errors['prenom'])): ?>
                            <span class="error-text">
                                <?= $errors['prenom'] ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control"
                            value="<?= htmlspecialchars($oldData['email'] ?? '') ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <span class="error-text">
                                <?= $errors['email'] ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Mot de passe <span class="required">*</span></label>
                        <input type="password" name="mot_de_passe" class="form-control" required>
                        <?php if (isset($errors['mot_de_passe'])): ?>
                            <span class="error-text">
                                <?= $errors['mot_de_passe'] ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Poste <span class="required">*</span></label>
                        <input type="text" name="poste" class="form-control"
                            value="<?= htmlspecialchars($oldData['poste'] ?? '') ?>" required>
                        <?php if (isset($errors['poste'])): ?>
                            <span class="error-text">
                                <?= $errors['poste'] ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Grade <span class="required">*</span></label>
                        <input type="text" name="grade" class="form-control"
                            value="<?= htmlspecialchars($oldData['grade'] ?? '') ?>" required>
                        <?php if (isset($errors['grade'])): ?>
                            <span class="error-text">
                                <?= $errors['grade'] ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Rôle <span class="required">*</span></label>
                        <select name="role" class="form-control" required>
                            <option value="">Sélectionner un rôle</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['value'] ?>" <?= ($oldData['role'] ?? '') == $role['value'] ? 'selected' : '' ?>>
                                    <?= $role['text'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['role'])): ?>
                            <span class="error-text">
                                <?= $errors['role'] ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Accès système <span class="required">*</span></label>
                        <select name="role_systeme" class="form-control" required>
                            <?php foreach ($rolesSysteme as $val => $text): ?>
                                <option value="<?= $val ?>" <?= ($oldData['role_systeme'] ?? 'user') == $val ? 'selected' : '' ?>>
                                    <?= $text ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Spécialité</label>
                    <input type="text" name="specialite" class="form-control"
                        value="<?= htmlspecialchars($oldData['specialite'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Domaine de recherche</label>
                    <textarea name="domaine_recherche" class="form-control"
                        rows="2"><?= htmlspecialchars($oldData['domaine_recherche'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Biographie</label>
                    <textarea name="biographie" class="form-control"
                        rows="3"><?= htmlspecialchars($oldData['biographie'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="actif" checked>
                        <span>Compte actif</span>
                    </label>
                </div>
            </form>
            <?php
            },
            function () {
                ?>
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal-add-membre')">Annuler</button>
            <button type="submit" form="form-add-membre" class="btn btn-primary">
                <i class="fas fa-save"></i> Enregistrer
            </button>
            <?php
            }
        );
    }

    /**
     * Render Edit Member Modal
     */
    private function renderEditModal($roles)
    {
        $rolesSysteme = [
            'user' => 'Utilisateur',
            'admin' => 'Administrateur'
        ];

        Modal::render(
            [
                'id' => 'modal-edit-membre',
                'title' => 'Modifier le Membre',
                'size' => 'large'
            ],
            function () use ($roles, $rolesSysteme) {
                ?>
            <form method="POST" action="?page=admin&section=membres&action=update" id="form-edit-membre">
                <input type="hidden" name="id_membre" id="edit-id">

                <div class="form-grid">
                    <div class="form-group">
                        <label>Nom <span class="required">*</span></label>
                        <input type="text" name="nom" id="edit-nom" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Prénom <span class="required">*</span></label>
                        <input type="text" name="prenom" id="edit-prenom" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <input type="email" name="email" id="edit-email" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Mot de passe (laisser vide pour ne pas changer)</label>
                        <input type="password" name="mot_de_passe" id="edit-password" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Poste <span class="required">*</span></label>
                        <input type="text" name="poste" id="edit-poste" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Grade <span class="required">*</span></label>
                        <input type="text" name="grade" id="edit-grade" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Rôle <span class="required">*</span></label>
                        <select name="role" id="edit-role" class="form-control" required>
                            <option value="">Sélectionner un rôle</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['value'] ?>">
                                    <?= $role['text'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Accès système <span class="required">*</span></label>
                        <select name="role_systeme" id="edit-role-systeme" class="form-control" required>
                            <?php foreach ($rolesSysteme as $val => $text): ?>
                                <option value="<?= $val ?>">
                                    <?= $text ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Spécialité</label>
                    <input type="text" name="specialite" id="edit-specialite" class="form-control">
                </div>

                <div class="form-group">
                    <label>Domaine de recherche</label>
                    <textarea name="domaine_recherche" id="edit-domaine" class="form-control" rows="2"></textarea>
                </div>

                <div class="form-group">
                    <label>Biographie</label>
                    <textarea name="biographie" id="edit-biographie" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="actif" id="edit-actif">
                        <span>Compte actif</span>
                    </label>
                </div>
            </form>
            <?php
            },
            function () {
                ?>
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-membre')">Annuler</button>
            <button type="submit" form="form-edit-membre" class="btn btn-primary">
                <i class="fas fa-save"></i> Mettre à jour
            </button>
            <?php
            }
        );
    }
}
?>