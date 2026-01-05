<?php
// ============================================================================
// AdminMembreView.php - Members Management
// ============================================================================
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

class AdminMembreView extends BaseView
{
    public function __construct()
    {
        $this->pageTitle = 'Administration - Membres';
        $this->currentPage = 'admin';
    }

    public function renderListe($membres, $stats)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <!-- Header -->
                <div class="admin-header">
                    <h1><i class="fas fa-users"></i> Gestion des Membres</h1>
                    <?php Button::render(['text' => 'Nouveau Membre', 'icon' => 'fas fa-plus', 'variant' => 'primary', 'href' => '?page=admin&section=membres&action=create']); ?>
                </div>

                <!-- Stats -->
                <div class="stats-grid">
                    <?php
                    StatCard::render(['value' => $stats['total'], 'label' => 'Total Membres', 'icon' => 'fas fa-users', 'color' => 'var(--primary-color)']);
                    foreach ($stats['par_grade'] as $stat) {
                        StatCard::render(['value' => $stat['total'], 'label' => $stat['grade'], 'icon' => 'fas fa-graduation-cap', 'color' => 'var(--accent-color)']);
                    }
                    ?>
                </div>

                <!-- Filters -->
                <div class="admin-filters">
                    <?php
                    SearchInput::render(['id' => 'search-table', 'placeholder' => 'Rechercher un membre...']);
                    Filter::render(['id' => 'filter-role-admin', 'placeholder' => 'Tous les rôles', 'options' => [['value' => 'admin', 'text' => 'Administrateur'], ['value' => 'membre', 'text' => 'Membre']], 'cssClass' => 'filter-inline']);
                    Filter::render(['id' => 'filter-actif-admin', 'placeholder' => 'Tous les statuts', 'options' => [['value' => '1', 'text' => 'Actifs'], ['value' => '0', 'text' => 'Inactifs']], 'cssClass' => 'filter-inline']);
                    ?>
                </div>

                <!-- Table -->
                <?php
                Table::render([
                    'headers' => ['ID', 'Photo', 'Nom Complet', 'Email', 'Poste', 'Grade', 'Rôle', 'Statut', 'Actions'],
                    'rows' => $membres
                ], function ($m) {
                    $photoUrl = $m['photo'] ? UPLOADS_URL . 'photos/' . $m['photo'] : null;
                    ?>
                    <tr>
                        <td><?= $m['id_membre'] ?></td>
                        <td>
                            <div style="display: inline-flex;">
                                <?php Avatar::render(['src' => $photoUrl, 'alt' => $m['nom'], 'size' => 'small']); ?>
                            </div>
                        </td>
                        <td><strong><?= htmlspecialchars($m['nom'] . ' ' . $m['prenom']) ?></strong></td>
                        <td><?= htmlspecialchars($m['email']) ?></td>
                        <td><?= htmlspecialchars($m['poste'] ?? 'N/A') ?></td>
                        <td><?php Badge::render(['text' => $m['grade'] ?? 'N/A', 'variant' => 'info', 'size' => 'small']); ?></td>
                        <td><?php Badge::render(['text' => ucfirst($m['role']), 'variant' => $m['role'] === 'admin' ? 'danger' : 'primary', 'size' => 'small']); ?></td>
                        <td><?php Badge::render(['text' => $m['actif'] ? 'Actif' : 'Inactif', 'variant' => $m['actif'] ? 'success' : 'default', 'size' => 'small']); ?></td>
                        <td>
                            <?php ActionButtons::render([
                                ['type' => 'view', 'href' => '?page=membres&action=biographie&id=' . $m['id_membre'], 'target' => '_blank'],
                                ['type' => 'edit', 'href' => '?page=admin&section=membres&action=edit&id=' . $m['id_membre']],
                                ['type' => 'delete', 'onClick' => 'confirmDelete(' . $m['id_membre'] . ', \'' . htmlspecialchars(addslashes($m['nom'] . ' ' . $m['prenom'])) . '\')']
                            ]); ?>
                        </td>
                    </tr>
                    <?php
                });
                ?>
            </div>
        </main>

        <script>
        function confirmDelete(id, nom) {
            if (confirm(`Êtes-vous sûr de vouloir supprimer le membre "${nom}" ?\nCette action est irréversible.`)) {
                window.location.href = '?page=admin&section=membres&action=delete&id=' + id;
            }
        }
        document.getElementById('search-table').addEventListener('input', function() {
            const term = this.value.toLowerCase();
            document.querySelectorAll('.data-table tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        });
        </script>

        <?php $this->renderFooter();
    }

    public function renderForm($membre = null)
    {
        $isEdit = $membre !== null;
        $this->pageTitle = $isEdit ? 'Modifier le membre' : 'Nouveau membre';
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <?php Breadcrumb::render([['text' => 'Administration', 'url' => '?page=admin'], ['text' => 'Membres', 'url' => '?page=admin&section=membres'], ['text' => $isEdit ? 'Modifier' : 'Nouveau']]); ?>

                <div class="form-container">
                    <h1><?= $isEdit ? 'Modifier le membre' : 'Nouveau membre' ?></h1>

                    <form method="POST" action="?page=admin&section=membres&action=<?= $isEdit ? 'update' : 'store' ?>" class="admin-form" enctype="multipart/form-data">
                        <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $membre['id_membre'] ?>"><?php endif; ?>

                        <div class="form-grid">
                            <?php
                            FormGroup::render(['label' => 'Nom', 'required' => true], fn() => FormInput::render(['name' => 'nom', 'value' => $isEdit ? $membre['nom'] : '', 'required' => true]));
                            FormGroup::render(['label' => 'Prénom', 'required' => true], fn() => FormInput::render(['name' => 'prenom', 'value' => $isEdit ? $membre['prenom'] : '', 'required' => true]));
                            FormGroup::render(['label' => 'Email', 'required' => true], fn() => FormInput::render(['type' => 'email', 'name' => 'email', 'value' => $isEdit ? $membre['email'] : '', 'required' => true]));
                            FormGroup::render(['label' => 'Poste'], fn() => FormInput::render(['name' => 'poste', 'value' => $isEdit ? ($membre['poste'] ?? '') : '', 'placeholder' => 'Ex: Chercheur, Doctorant']));
                            FormGroup::render(['label' => 'Grade'], fn() => FormInput::render(['name' => 'grade', 'value' => $isEdit ? ($membre['grade'] ?? '') : '', 'placeholder' => 'Ex: Professeur']));
                            FormGroup::render(['label' => 'Rôle', 'required' => true], function() use ($isEdit, $membre) {
                                ?>
                                <select name="role" class="form-control" required>
                                    <option value="membre" <?= $isEdit && $membre['role'] === 'membre' ? 'selected' : '' ?>>Membre</option>
                                    <option value="admin" <?= $isEdit && $membre['role'] === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                                </select>
                                <?php
                            });
                            FormGroup::render(['label' => 'Statut', 'required' => true], function() use ($isEdit, $membre) {
                                ?>
                                <select name="actif" class="form-control" required>
                                    <option value="1" <?= $isEdit && $membre['actif'] ? 'selected' : '' ?>>Actif</option>
                                    <option value="0" <?= $isEdit && !$membre['actif'] ? 'selected' : '' ?>>Inactif</option>
                                </select>
                                <?php
                            });
                            FormGroup::render(['label' => 'Mot de passe' . ($isEdit ? '' : ' *'), 'required' => !$isEdit], function() use ($isEdit) {
                                FormInput::render(['type' => 'password', 'name' => 'mot_de_passe', 'required' => !$isEdit]);
                                if ($isEdit) echo '<small class="form-text">Laisser vide pour ne pas changer</small>';
                            });
                            FormGroup::render(['label' => 'Photo de profil', 'cssClass' => 'full-width'], function() use ($isEdit, $membre) {
                                FormInput::render(['type' => 'file', 'name' => 'photo', 'attributes' => ['accept' => 'image/*']]);
                                if ($isEdit && !empty($membre['photo'])) {
                                    echo '<small class="form-text">Photo actuelle: <img src="' . UPLOADS_URL . 'photos/' . $membre['photo'] . '" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%; vertical-align: middle;"></small>';
                                }
                                echo '<small class="form-text">Format: JPG, PNG, max 2 MB</small>';
                            });
                            FormGroup::render(['label' => 'Biographie', 'cssClass' => 'full-width'], function() use ($isEdit, $membre) {
                                echo '<textarea name="biographie" rows="4" class="form-control">' . ($isEdit ? htmlspecialchars($membre['biographie'] ?? '') : '') . '</textarea>';
                            });
                            FormGroup::render(['label' => 'Domaines de recherche', 'cssClass' => 'full-width'], function() use ($isEdit, $membre) {
                                echo '<textarea name="domaine_recherche" rows="3" class="form-control">' . ($isEdit ? htmlspecialchars($membre['domaine_recherche'] ?? '') : '') . '</textarea>';
                            });
                            ?>
                        </div>

                        <?php FormActions::render(['align' => 'left'], function() use ($isEdit) {
                            Button::render(['text' => $isEdit ? 'Mettre à jour' : 'Créer', 'icon' => 'fas fa-save', 'variant' => 'primary', 'type' => 'submit']);
                            Button::render(['text' => 'Annuler', 'icon' => 'fas fa-times', 'variant' => 'secondary', 'href' => '?page=admin&section=membres']);
                        }); ?>
                    </form>
                </div>
            </div>
        </main>

        <?php $this->renderFooter();
    }
}