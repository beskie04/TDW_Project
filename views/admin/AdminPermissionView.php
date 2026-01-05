<?php
require_once __DIR__ . '/../BaseView.php';

// Import Components
require_once __DIR__ . '/../components/Button.php';
require_once __DIR__ . '/../components/Badge.php';
require_once __DIR__ . '/../components/Card.php';
require_once __DIR__ . '/../components/Section.php';
require_once __DIR__ . '/../components/Table.php';
require_once __DIR__ . '/../components/FormGroup.php';
require_once __DIR__ . '/../components/FormInput.php';
require_once __DIR__ . '/../components/FormActions.php';
require_once __DIR__ . '/../components/StatCard.php';

class AdminPermissionView extends BaseView
{
    public function __construct()
    {
        $this->pageTitle = 'Gestion des Permissions';
        $this->currentPage = 'admin';
    }

    /**
     * Dashboard des permissions
     */
    public function renderDashboard($roles)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-shield-alt"></i> Gestion des Permissions et Accès</h1>
                </div>

                <div class="stats-grid">
                    <?php
                    StatCard::render([
                        'value' => count($roles),
                        'label' => 'Rôles configurés',
                        'icon' => 'fas fa-user-tag',
                        'color' => '#3b82f6'
                    ]);

                    $totalUsers = array_sum(array_column($roles, 'nb_users'));
                    StatCard::render([
                        'value' => $totalUsers,
                        'label' => 'Utilisateurs',
                        'icon' => 'fas fa-users',
                        'color' => '#10b981'
                    ]);

                    StatCard::render([
                        'value' => '70+',
                        'label' => 'Permissions disponibles',
                        'icon' => 'fas fa-key',
                        'color' => '#f59e0b'
                    ]);
                    ?>
                </div>

                <!-- Menu Cards -->
                <h2 style="margin: 2rem 0 1rem 0;">Actions Rapides</h2>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                    <a href="?page=admin&section=permissions&action=roles" class="card" style="text-decoration: none; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div class="card-content" style="text-align: center; padding: 2rem;">
                            <div style="width: 80px; height: 80px; margin: 0 auto 1rem; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-user-tag" style="font-size: 2.5rem; color: white;"></i>
                            </div>
                            <h3 style="margin: 0 0 0.5rem 0; color: var(--dark-color);">Gérer les Rôles</h3>
                            <p style="margin: 0; color: var(--gray-600); font-size: 0.9rem;">Configurer les rôles et leurs permissions par défaut</p>
                        </div>
                    </a>

                    <a href="?page=admin&section=permissions&action=userPermissions" class="card" style="text-decoration: none; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div class="card-content" style="text-align: center; padding: 2rem;">
                            <div style="width: 80px; height: 80px; margin: 0 auto 1rem; background: linear-gradient(135deg, #10b981, #059669); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-user-cog" style="font-size: 2.5rem; color: white;"></i>
                            </div>
                            <h3 style="margin: 0 0 0.5rem 0; color: var(--dark-color);">Permissions Utilisateurs</h3>
                            <p style="margin: 0; color: var(--gray-600); font-size: 0.9rem;">Attribuer des permissions spécifiques à des utilisateurs</p>
                        </div>
                    </a>

                    <a href="?page=admin&section=permissions&action=logs" class="card" style="text-decoration: none; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div class="card-content" style="text-align: center; padding: 2rem;">
                            <div style="width: 80px; height: 80px; margin: 0 auto 1rem; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-history" style="font-size: 2.5rem; color: white;"></i>
                            </div>
                            <h3 style="margin: 0 0 0.5rem 0; color: var(--dark-color);">Historique</h3>
                            <p style="margin: 0; color: var(--gray-600); font-size: 0.9rem;">Consulter les logs des changements de permissions</p>
                        </div>
                    </a>
                </div>

                <!-- Liste des rôles -->
                <h2 style="margin: 2rem 0 1rem 0;">Aperçu des Rôles</h2>
                <?php
                Table::render([
                    'headers' => ['Rôle', 'Description', 'Permissions', 'Utilisateurs', 'Actions'],
                    'rows' => $roles
                ], function ($role) {
                    ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($role['nom']) ?></strong>
                            <?php if ($role['is_system']): ?>
                                <Badge::render([
                                    'text' => 'Système',
                                    'variant' => 'info',
                                    'size' => 'small'
                                ]);
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($role['description'] ?? '') ?></td>
                        <td><?= $role['nb_permissions'] ?> permissions</td>
                        <td><?= $role['nb_users'] ?> utilisateurs</td>
                        <td style="white-space: nowrap;">
                            <a href="?page=admin&section=permissions&action=configRole&id=<?= $role['id_role'] ?>" 
                               class="btn btn-sm btn-primary" title="Configurer">
                                <i class="fas fa-cog"></i>
                            </a>
                            <?php if (!$role['is_system']): ?>
                                <button onclick="deleteRole(<?= $role['id_role'] ?>, '<?= htmlspecialchars(addslashes($role['nom'])) ?>')" 
                                        class="btn btn-sm btn-danger" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                });
                ?>
            </div>
        </main>

        <script>
            function deleteRole(id, nom) {
                if (confirm(`Supprimer le rôle "${nom}" ?\n\nAttention: Cette action est irréversible.`)) {
                    window.location.href = '?page=admin&section=permissions&action=deleteRole&id=' + id;
                }
            }
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Gestion des rôles
     */
    public function renderRoles($roles)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-user-tag"></i> Gestion des Rôles</h1>
                    <div style="display: flex; gap: 1rem;">
                        <Button::render([
                            'text' => 'Nouveau Rôle',
                            'icon' => 'fas fa-plus',
                            'variant' => 'primary',
                            'href' => '?page=admin&section=permissions&action=createRole'
                        ]);
                        <Button::render([
                            'text' => 'Retour',
                            'icon' => 'fas fa-arrow-left',
                            'variant' => 'secondary',
                            'href' => '?page=admin&section=permissions'
                        ]);
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
                    <?php foreach ($roles as $role): ?>
                        <div class="card">
                            <div class="card-content">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                    <h3 style="margin: 0; color: var(--dark-color);">
                                        <?= htmlspecialchars($role['nom']) ?>
                                    </h3>
                                    <?php if ($role['is_system']): ?>
                                        <?php
                                        Badge::render([
                                            'text' => 'Système',
                                            'variant' => 'info',
                                            'size' => 'small'
                                        ]);
                                        ?>
                                    <?php endif; ?>
                                </div>

                                <p style="color: var(--gray-600); font-size: 0.9rem; margin: 0 0 1rem 0;">
                                    <?= htmlspecialchars($role['description'] ?? 'Aucune description') ?>
                                </p>

                                <div style="display: flex; gap: 1rem; padding: 1rem 0; border-top: 1px solid var(--gray-200); border-bottom: 1px solid var(--gray-200); margin-bottom: 1rem;">
                                    <div style="flex: 1; text-align: center;">
                                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);">
                                            <?= $role['nb_permissions'] ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--gray-600);">Permissions</div>
                                    </div>
                                    <div style="flex: 1; text-align: center;">
                                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--success-color, #10b981);">
                                            <?= $role['nb_users'] ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--gray-600);">Utilisateurs</div>
                                    </div>
                                </div>

                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="?page=admin&section=permissions&action=configRole&id=<?= $role['id_role'] ?>" 
                                       class="btn btn-primary btn-sm" style="flex: 1;">
                                        <i class="fas fa-cog"></i> Configurer
                                    </a>
                                    <?php if (!$role['is_system'] && $role['nb_users'] == 0): ?>
                                        <button onclick="deleteRole(<?= $role['id_role'] ?>, '<?= htmlspecialchars(addslashes($role['nom'])) ?>')" 
                                                class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>

        <script>
            function deleteRole(id, nom) {
                if (confirm(`Supprimer le rôle "${nom}" ?`)) {
                    window.location.href = '?page=admin&section=permissions&action=deleteRole&id=' + id;
                }
            }
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Configuration d'un rôle
     */
    public function renderConfigRole($role, $allPermissions, $rolePermissionIds)
    {
        $this->pageTitle = 'Configuration: ' . $role['nom'];
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-cog"></i> Configuration: <?= htmlspecialchars($role['nom']) ?></h1>
                    <Button::render([
                        'text' => 'Retour',
                        'icon' => 'fas fa-arrow-left',
                        'variant' => 'secondary',
                        'href' => '?page=admin&section=permissions&action=roles'
                    ]);
                </div>

                <form method="POST" action="?page=admin&section=permissions&action=saveRoleConfig">
                    <input type="hidden" name="id_role" value="<?= $role['id_role'] ?>">

                    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <p style="color: var(--gray-600); margin-bottom: 2rem;">
                            Sélectionnez les permissions que les utilisateurs avec le rôle 
                            <strong><?= htmlspecialchars($role['nom']) ?></strong> doivent avoir par défaut.
                        </p>

                        <?php foreach ($allPermissions as $module => $permissions): ?>
                            <div style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--gray-200);">
                                <h3 style="margin: 0 0 1rem 0; color: var(--primary-color); text-transform: uppercase; font-size: 1rem;">
                                    <i class="<?= $this->getModuleIcon($module) ?>"></i>
                                    <?= ucfirst($module) ?>
                                </h3>

                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 0.75rem;">
                                    <?php foreach ($permissions as $perm): ?>
                                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; background: #f7fafc; border-radius: 6px; cursor: pointer; transition: background 0.2s;" 
                                               onmouseover="this.style.background='#e2e8f0'" 
                                               onmouseout="this.style.background='#f7fafc'">
                                            <input type="checkbox" 
                                                   name="permissions[]" 
                                                   value="<?= $perm['id_permission'] ?>"
                                                   <?= in_array($perm['id_permission'], $rolePermissionIds) ? 'checked' : '' ?>
                                                   style="width: 18px; height: 18px;">
                                            <div style="flex: 1;">
                                                <div style="font-weight: 600; color: var(--dark-color); font-size: 0.9rem;">
                                                    <?= htmlspecialchars($perm['description']) ?>
                                                    <?php if ($perm['is_own']): ?>
                                                        <Badge::render([
                                                            'text' => 'Own',
                                                            'variant' => 'warning',
                                                            'size' => 'small'
                                                        ]);
                                                    <?php endif; ?>
                                                </div>
                                                <div style="font-size: 0.75rem; color: var(--gray-500);">
                                                    <?= htmlspecialchars($perm['nom']) ?>
                                                </div>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--gray-200);">
                            <div>
                                <strong id="selected-count">0</strong> permission(s) sélectionnée(s)
                            </div>
                            <div style="display: flex; gap: 1rem;">
                                <button type="button" onclick="selectAll()" class="btn btn-secondary">
                                    <i class="fas fa-check-double"></i> Tout sélectionner
                                </button>
                                <button type="button" onclick="unselectAll()" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Tout désélectionner
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Sauvegarder
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>

        <script>
            function updateCount() {
                const checked = document.querySelectorAll('input[name="permissions[]"]:checked').length;
                document.getElementById('selected-count').textContent = checked;
            }

            function selectAll() {
                document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = true);
                updateCount();
            }

            function unselectAll() {
                document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = false);
                updateCount();
            }

            document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
                cb.addEventListener('change', updateCount);
            });

            updateCount();
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Créer un nouveau rôle
     */
    public function renderCreateRole($allRoles)
    {
        $this->pageTitle = 'Nouveau Rôle';
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-plus"></i> Créer un Nouveau Rôle</h1>
                    <Button::render([
                        'text' => 'Retour',
                        'icon' => 'fas fa-arrow-left',
                        'variant' => 'secondary',
                        'href' => '?page=admin&section=permissions&action=roles'
                    ]);
                </div>

                <form method="POST" action="?page=admin&section=permissions&action=storeRole" style="max-width: 600px; margin: 0 auto;">
                    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <?php
                        FormGroup::render([
                            'label' => 'Nom du rôle',
                            'required' => true
                        ], function () {
                            FormInput::render([
                                'type' => 'text',
                                'name' => 'nom',
                                'placeholder' => 'Ex: Gestionnaire Équipements',
                                'required' => true
                            ]);
                        });

                        FormGroup::render([
                            'label' => 'Description'
                        ], function () {
                            ?>
                            <textarea name="description" class="form-control" rows="3" 
                                      placeholder="Description du rôle et de ses responsabilités"></textarea>
                            <?php
                        });

                        FormGroup::render([
                            'label' => 'Rôle parent (héritage)',
                        ], function () use ($allRoles) {
                            ?>
                            <select name="parent_role" class="form-control">
                                <option value="">Aucun (rôle indépendant)</option>
                                <?php foreach ($allRoles as $role): ?>
                                    <option value="<?= $role['id_role'] ?>">
                                        <?= htmlspecialchars($role['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small style="color: var(--gray-600); display: block; margin-top: 0.5rem;">
                                Si vous sélectionnez un rôle parent, ce nouveau rôle héritera automatiquement de toutes ses permissions.
                            </small>
                            <?php
                        });

                        FormActions::render(['align' => 'left'], function () {
                            ?>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Créer le Rôle
                            </button>
                            <a href="?page=admin&section=permissions&action=roles" class="btn btn-secondary">
                                Annuler
                            </a>
                            <?php
                        });
                        ?>
                    </div>
                </form>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    /**
     * Permissions par utilisateur
     */
    public function renderUserPermissions($membres, $selectedMembre, $userPermissions, $rolePermissions, $allPermissions)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-user-cog"></i> Permissions par Utilisateur</h1>
                    <Button::render([
                        'text' => 'Retour',
                        'icon' => 'fas fa-arrow-left',
                        'variant' => 'secondary',
                        'href' => '?page=admin&section=permissions'
                    ]);
                </div>

                <!-- Sélection utilisateur -->
                <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                        <i class="fas fa-user"></i> Sélectionner un utilisateur:
                    </label>
                    <select id="membre-select" class="form-control" onchange="window.location.href='?page=admin&section=permissions&action=userPermissions&membre_id=' + this.value">
                        <option value="">-- Choisir un membre --</option>
                        <?php foreach ($membres as $membre): ?>
                            <option value="<?= $membre['id_membre'] ?>" 
                                    <?= $selectedMembre && $selectedMembre['id_membre'] == $membre['id_membre'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?> 
                                (<?= htmlspecialchars($membre['role_systeme']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($selectedMembre): ?>
                    <!-- Info utilisateur -->
                    <div style="background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h2 style="margin: 0 0 0.5rem 0;">
                                    <?= htmlspecialchars($selectedMembre['nom'] . ' ' . $selectedMembre['prenom']) ?>
                                </h2>
                                <p style="margin: 0; opacity: 0.9;">
                                    <i class="fas fa-envelope"></i> <?= htmlspecialchars($selectedMembre['email']) ?>
                                </p>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Rôle actuel</div>
                                <div style="background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600; display: inline-block;">
                                    <?= htmlspecialchars($selectedMembre['role_systeme']) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="?page=admin&section=permissions&action=saveUserPermissions">
                        <input type="hidden" name="membre_id" value="<?= $selectedMembre['id_membre'] ?>">

                        <!-- Permissions du rôle -->
                        <div style="background: #f7fafc; padding: 2rem; border-radius: 12px; margin-bottom: 2rem; border: 2px dashed var(--gray-300);">
                            <h3 style="margin: 0 0 1rem 0; color: var(--dark-color);">
                                <i class="fas fa-lock"></i> Permissions du Rôle (héritées)
                            </h3>
                            <p style="color: var(--gray-600); margin-bottom: 1.5rem; font-size: 0.9rem;">
                                Ces permissions sont automatiquement accordées par le rôle 
                                <strong><?= htmlspecialchars($selectedMembre['role_systeme']) ?></strong> et ne peuvent pas être retirées directement ici.
                            </p>

                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 0.5rem;">
                                <?php foreach ($rolePermissions as $perm): ?>
                                    <div style="padding: 0.5rem; background: white; border-radius: 6px; font-size: 0.875rem; color: var(--gray-700);">
                                        <i class="fas fa-check" style="color: var(--success-color, #10b981);"></i>
                                        <?= htmlspecialchars($perm['description']) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Permissions personnalisées -->
                        <?php
                        $userPermGranted = [];
                        $userPermRevoked = [];
                        foreach ($userPermissions as $up) {
                            if ($up['granted']) {
                                $userPermGranted[] = $up['id_permission'];
                            } else {
                                $userPermRevoked[] = $up['id_permission'];
                            }
                        }

                        $rolePermIds = array_column($rolePermissions, 'id_permission');
                        ?>

                        <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                            <h3 style="margin: 0 0 1rem 0; color: var(--dark-color);">
                                <i class="fas fa-user-plus"></i> Permissions Additionnelles
                            </h3>
                            <p style="color: var(--gray-600); margin-bottom: 1.5rem;">
                                Accordez des permissions supplémentaires à cet utilisateur, même si son rôle ne les possède pas.
                            </p>

                            <?php foreach ($allPermissions as $module => $permissions): ?>
                                <div style="margin-bottom: 2rem;">
                                    <h4 style="margin: 0 0 1rem 0; color: var(--primary-color); text-transform: uppercase; font-size: 0.9rem;">
                                        <i class="<?= $this->getModuleIcon($module) ?>"></i>
                                        <?= ucfirst($module) ?>
                                    </h4>

                                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 0.5rem;">
                                        <?php foreach ($permissions as $perm): ?>
                                            <?php 
                                            $hasFromRole = in_array($perm['id_permission'], $rolePermIds);
                                            $isGranted = in_array($perm['id_permission'], $userPermGranted);
                                            $isRevoked = in_array($perm['id_permission'], $userPermRevoked);
                                            ?>

                                            <?php if (!$hasFromRole): ?>
                                                <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; background: #f7fafc; border-radius: 6px; cursor: pointer; border: 2px solid transparent; transition: all 0.2s;"
                                                       class="perm-grant-label"
                                                       onmouseover="this.style.borderColor='#3b82f6'"
                                                       onmouseout="this.style.borderColor='transparent'">
                                                    <input type="checkbox" 
                                                           name="granted_permissions[]" 
                                                           value="<?= $perm['id_permission'] ?>"
                                                           <?= $isGranted ? 'checked' : '' ?>
                                                           style="width: 18px; height: 18px;">
                                                    <div style="flex: 1; font-size: 0.875rem;">
                                                        <?= htmlspecialchars($perm['description']) ?>
                                                    </div>
                                                </label>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Permissions retirées -->
                        <div style="background: #fef2f2; padding: 2rem; border-radius: 12px; border: 2px solid #fca5a5; margin-bottom: 2rem;">
                            <h3 style="margin: 0 0 1rem 0; color: #dc2626;">
                                <i class="fas fa-user-minus"></i> Permissions Retirées
                            </h3>
                            <p style="color: #7f1d1d; margin-bottom: 1.5rem;">
                                Retirez des permissions que le rôle possède normalement. Ces permissions ne seront plus accessibles pour cet utilisateur.
                            </p>

                            <?php foreach ($allPermissions as $module => $permissions): ?>
                                <div style="margin-bottom: 2rem;">
                                    <h4 style="margin: 0 0 1rem 0; color: #dc2626; text-transform: uppercase; font-size: 0.9rem;">
                                        <?= ucfirst($module) ?>
                                    </h4>

                                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 0.5rem;">
                                        <?php foreach ($permissions as $perm): ?>
                                            <?php 
                                            $hasFromRole = in_array($perm['id_permission'], $rolePermIds);
                                            $isRevoked = in_array($perm['id_permission'], $userPermRevoked);
                                            ?>

                                            <?php if ($hasFromRole): ?>
                                                <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; background: white; border-radius: 6px; cursor: pointer; border: 2px solid transparent; transition: all 0.2s;"
                                                       onmouseover="this.style.borderColor='#dc2626'"
                                                       onmouseout="this.style.borderColor='transparent'">
                                                    <input type="checkbox" 
                                                           name="revoked_permissions[]" 
                                                           value="<?= $perm['id_permission'] ?>"
                                                           <?= $isRevoked ? 'checked' : '' ?>
                                                           style="width: 18px; height: 18px;">
                                                    <div style="flex: 1; font-size: 0.875rem; color: #7f1d1d;">
                                                        <?= htmlspecialchars($perm['description']) ?>
                                                    </div>
                                                </label>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Note -->
                        <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                            <?php
                            FormGroup::render([
                                'label' => 'Note / Raison du changement (optionnel)'
                            ], function () {
                                ?>
                                <textarea name="note" class="form-control" rows="3" 
                                          placeholder="Ex: Pour gérer le projet ANR urgent, besoin temporaire pour l'événement..."></textarea>
                                <?php
                            });
                            ?>
                        </div>

                        <!-- Actions -->
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <button type="button" 
                                    onclick="if(confirm('Réinitialiser toutes les permissions personnalisées ?')) window.location.href='?page=admin&section=permissions&action=resetUserPermissions&membre_id=<?= $selectedMembre['id_membre'] ?>'" 
                                    class="btn btn-warning">
                                <i class="fas fa-undo"></i> Réinitialiser
                            </button>

                            <div style="display: flex; gap: 1rem;">
                                <a href="?page=admin&section=permissions&action=userPermissions" class="btn btn-secondary">
                                    Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Sauvegarder les Permissions
                                </button>
                            </div>
                        </div>
                    </form>
                <?php else: ?>
                    <div style="text-align: center; padding: 4rem; background: white; border-radius: 12px;">
                        <i class="fas fa-user-circle" style="font-size: 5rem; color: var(--gray-400); margin-bottom: 1rem;"></i>
                        <h3 style="color: var(--gray-600); margin: 0;">Sélectionnez un utilisateur</h3>
                        <p style="color: var(--gray-500); margin: 0.5rem 0 0 0;">Choisissez un membre dans la liste ci-dessus pour gérer ses permissions</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    /**
     * Logs des permissions
     */
    public function renderLogs($logs)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-history"></i> Historique des Changements</h1>
                    <Button::render([
                        'text' => 'Retour',
                        'icon' => 'fas fa-arrow-left',
                        'variant' => 'secondary',
                        'href' => '?page=admin&section=permissions'
                    ]);
                </div>

                <?php if (empty($logs)): ?>
                    <div style="text-align: center; padding: 3rem; background: white; border-radius: 12px;">
                        <i class="fas fa-inbox" style="font-size: 4rem; color: var(--gray-400);"></i>
                        <h3 style="color: var(--gray-600); margin: 1rem 0 0 0;">Aucun log disponible</h3>
                    </div>
                <?php else: ?>
                    <?php
                    Table::render([
                        'headers' => ['Date', 'Utilisateur', 'Action', 'Permission', 'Par', 'Note'],
                        'rows' => $logs
                    ], function ($log) {
                        $actionColors = [
                            'granted' => '#10b981',
                            'revoked' => '#ef4444',
                            'role_changed' => '#f59e0b'
                        ];
                        $actionLabels = [
                            'granted' => 'Accordée',
                            'revoked' => 'Retirée',
                            'role_changed' => 'Rôle modifié'
                        ];
                        ?>
                        <tr>
                            <td style="white-space: nowrap; font-size: 0.875rem;">
                                <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($log['membre_nom'] . ' ' . $log['membre_prenom']) ?></strong>
                            </td>
                            <td>
                                <span style="padding: 0.25rem 0.75rem; background: <?= $actionColors[$log['action']] ?>15; color: <?= $actionColors[$log['action']] ?>; border-radius: 12px; font-size: 0.875rem; font-weight: 600;">
                                    <?= $actionLabels[$log['action']] ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($log['permission_nom'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($log['changed_by_nom'] . ' ' . $log['changed_by_prenom']) ?></td>
                            <td style="max-width: 300px; font-size: 0.875rem; color: var(--gray-600);">
                                <?= htmlspecialchars($log['note'] ?? '-') ?>
                            </td>
                        </tr>
                        <?php
                    });
                    ?>
                <?php endif; ?>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    /**
     * Get module icon
     */
    private function getModuleIcon($module)
    {
        $icons = [
            'projets' => 'fas fa-project-diagram',
            'publications' => 'fas fa-file-alt',
            'equipements' => 'fas fa-tools',
            'membres' => 'fas fa-users',
            'equipes' => 'fas fa-users-cog',
            'evenements' => 'fas fa-calendar-alt',
            'annonces' => 'fas fa-bullhorn',
            'offres' => 'fas fa-briefcase',
            'messages' => 'fas fa-envelope',
            'admin' => 'fas fa-cog'
        ];

        return $icons[$module] ?? 'fas fa-circle';
    }
}
?>