<?php
require_once __DIR__ . '/../../models/PermissionModel.php';
require_once __DIR__ . '/../../models/BaseModel.php';
require_once __DIR__ . '/../../views/admin/AdminPermissionView.php';
require_once __DIR__ . '/../../views/BaseView.php';

class AdminPermissionController
{
    private $model;
    private $view;

    public function __construct()
    {
        $this->checkAdmin();
        $this->model = new PermissionModel();
        $this->view = new AdminPermissionView();
    }

    private function checkAdmin()
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: ?page=login');
            exit;
        }
    }

    /**
     * Dashboard des permissions
     */
    public function index()
    {
        $roles = $this->model->getAllRoles();
        $this->view->renderDashboard($roles);
    }

    /**
     * Gestion des rôles
     */
    public function roles()
    {
        $roles = $this->model->getAllRoles();
        $this->view->renderRoles($roles);
    }

    /**
     * Configuration d'un rôle
     */
    public function configRole()
    {
        $roleId = $_GET['id'] ?? null;

        if (!$roleId) {
            header('Location: ?page=admin&section=permissions&action=roles');
            exit;
        }

        $role = $this->model->getRoleWithPermissions($roleId);

        if (!$role) {
            BaseView::setFlash('Rôle introuvable', 'error');
            header('Location: ?page=admin&section=permissions&action=roles');
            exit;
        }

        $allPermissions = $this->model->getAllPermissionsGrouped();
        $rolePermissionIds = array_column($role['permissions'], 'id_permission');

        $this->view->renderConfigRole($role, $allPermissions, $rolePermissionIds);
    }

    /**
     * Sauvegarder la configuration d'un rôle
     */
    public function saveRoleConfig()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=permissions&action=roles');
            exit;
        }

        $roleId = $_POST['id_role'] ?? null;
        $permissionIds = $_POST['permissions'] ?? [];

        if (!$roleId) {
            BaseView::setFlash('Données invalides', 'error');
            header('Location: ?page=admin&section=permissions&action=roles');
            exit;
        }

        $success = $this->model->updateRolePermissions($roleId, $permissionIds);

        if ($success) {
            BaseView::setFlash('Permissions du rôle mises à jour avec succès', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la mise à jour', 'error');
        }

        header('Location: ?page=admin&section=permissions&action=configRole&id=' . $roleId);
        exit;
    }

    /**
     * Créer un nouveau rôle
     */
    public function createRole()
    {
        $allRoles = $this->model->getAllRoles();
        $this->view->renderCreateRole($allRoles);
    }

    /**
     * Enregistrer un nouveau rôle
     */
    public function storeRole()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=permissions&action=roles');
            exit;
        }

        $nom = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $parentRole = !empty($_POST['parent_role']) ? $_POST['parent_role'] : null;

        if (empty($nom)) {
            BaseView::setFlash('Le nom du rôle est requis', 'error');
            header('Location: ?page=admin&section=permissions&action=createRole');
            exit;
        }

        $success = $this->model->createRole($nom, $description, $parentRole);

        if ($success) {
            BaseView::setFlash('Rôle créé avec succès', 'success');
            header('Location: ?page=admin&section=permissions&action=roles');
        } else {
            BaseView::setFlash('Erreur lors de la création du rôle', 'error');
            header('Location: ?page=admin&section=permissions&action=createRole');
        }
        exit;
    }

    /**
     * Supprimer un rôle
     */
    public function deleteRole()
    {
        $roleId = $_GET['id'] ?? null;

        if (!$roleId) {
            BaseView::setFlash('ID manquant', 'error');
            header('Location: ?page=admin&section=permissions&action=roles');
            exit;
        }

        $success = $this->model->deleteRole($roleId);

        if ($success) {
            BaseView::setFlash('Rôle supprimé avec succès', 'success');
        } else {
            BaseView::setFlash('Impossible de supprimer ce rôle (rôle système ou utilisateurs assignés)', 'error');
        }

        header('Location: ?page=admin&section=permissions&action=roles');
        exit;
    }

    /**
     * Gestion des permissions par utilisateur - VERSION SIMPLIFIÉE
     */
    public function userPermissions()
    {
        // Récupérer tous les membres (sauf admin)
        $sql = "SELECT * FROM membres WHERE actif = 1 AND role_systeme != 'admin' ORDER BY nom, prenom";
        $membres = $this->model->query($sql);

        $selectedMembre = null;
        $userPermissions = [];
        $rolePermissions = [];
        $availableOwnPermissions = [];

        if (isset($_GET['membre_id'])) {
            $membreId = $_GET['membre_id'];

            // Récupérer le membre
            $sql = "SELECT * FROM membres WHERE id_membre = :id";
            $result = $this->model->query($sql, ['id' => $membreId]);
            $selectedMembre = !empty($result) ? $result[0] : null;

            if ($selectedMembre) {
                // Récupérer ses permissions custom (granted only)
                $sql = "SELECT p.* FROM permissions p
                        INNER JOIN user_permissions up ON p.id_permission = up.id_permission
                        WHERE up.id_membre = :membre AND up.granted = 1";
                $userPermissions = $this->model->query($sql, ['membre' => $membreId]);

                // Récupérer les permissions de son rôle
                $sql = "SELECT id_role FROM roles WHERE nom = :nom";
                $roleResult = $this->model->query($sql, ['nom' => $selectedMembre['role_systeme']]);

                if (!empty($roleResult)) {
                    $rolePermissions = $this->model->getRolePermissions($roleResult[0]['id_role'], true);
                }

                // ⭐ NOUVEAU: Récupérer UNIQUEMENT les permissions "own" qui NE SONT PAS dans le rôle
                $rolePermissionIds = array_column($rolePermissions, 'id_permission');
                $placeholders = !empty($rolePermissionIds) ? implode(',', array_fill(0, count($rolePermissionIds), '?')) : '0';
                
                $sql = "SELECT * FROM permissions 
                        WHERE is_own = 1 
                        AND id_permission NOT IN ($placeholders)
                        ORDER BY module, action";
                
                $availableOwnPermissions = $this->model->query($sql, $rolePermissionIds);
            }
        }

        $this->view->renderUserPermissions($membres, $selectedMembre, $userPermissions, $rolePermissions, $availableOwnPermissions);
    }

    /**
     * Sauvegarder les permissions d'un utilisateur - VERSION SIMPLIFIÉE
     */
    public function saveUserPermissions()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=permissions&action=userPermissions');
            exit;
        }

        $membreId = $_POST['membre_id'] ?? null;
        $grantedPermissions = $_POST['permissions'] ?? [];
        $note = $_POST['note'] ?? 'Permissions additionnelles accordées';

        if (!$membreId) {
            BaseView::setFlash('Données invalides', 'error');
            header('Location: ?page=admin&section=permissions&action=userPermissions');
            exit;
        }

        $adminId = $_SESSION['user']['id_membre'];

        // ⭐ NOUVEAU: Supprimer TOUTES les permissions custom actuelles
        $sql = "DELETE FROM user_permissions WHERE id_membre = :membre";
        $this->model->execute($sql, ['membre' => $membreId]);

        // ⭐ NOUVEAU: Ajouter uniquement les permissions cochées
        foreach ($grantedPermissions as $permId) {
            // Vérifier que c'est bien une permission "own"
            $sql = "SELECT is_own FROM permissions WHERE id_permission = :id";
            $result = $this->model->query($sql, ['id' => $permId]);
            
            if (!empty($result) && $result[0]['is_own'] == 1) {
                $this->model->grantPermissionToUser($membreId, $permId, $adminId, $note);
            }
        }

        // Clear cache
        require_once __DIR__ . '/../../utils/PermissionHelper.php';
        PermissionHelper::clearCache();

        BaseView::setFlash('Permissions utilisateur mises à jour avec succès', 'success');
        header('Location: ?page=admin&section=permissions&action=userPermissions&membre_id=' . $membreId);
        exit;
    }

    /**
     * Réinitialiser les permissions d'un utilisateur
     */
    public function resetUserPermissions()
    {
        $membreId = $_GET['membre_id'] ?? null;

        if (!$membreId) {
            BaseView::setFlash('ID manquant', 'error');
            header('Location: ?page=admin&section=permissions&action=userPermissions');
            exit;
        }

        $adminId = $_SESSION['user']['id_membre'];
        $success = $this->model->resetUserPermissions($membreId, $adminId);

        // Clear cache
        require_once __DIR__ . '/../../utils/PermissionHelper.php';
        PermissionHelper::clearCache();

        if ($success) {
            BaseView::setFlash('Permissions réinitialisées avec succès', 'success');
        } else {
            BaseView::setFlash('Erreur lors de la réinitialisation', 'error');
        }

        header('Location: ?page=admin&section=permissions&action=userPermissions&membre_id=' . $membreId);
        exit;
    }
}
?>