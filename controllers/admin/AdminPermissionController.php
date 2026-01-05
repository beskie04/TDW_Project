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
     * Gestion des permissions par utilisateur
     */
    public function userPermissions()
    {
        // Récupérer tous les membres
        $sql = "SELECT * FROM membres WHERE actif = 1 ORDER BY nom, prenom";
        $membres = $this->model->query($sql);

        $selectedMembre = null;
        $userPermissions = [];
        $rolePermissions = [];
        $allPermissions = [];

        if (isset($_GET['membre_id'])) {
            $membreId = $_GET['membre_id'];

            // Récupérer le membre
            $sql = "SELECT * FROM membres WHERE id_membre = :id";
            $result = $this->model->query($sql, ['id' => $membreId]);
            $selectedMembre = !empty($result) ? $result[0] : null;

            if ($selectedMembre) {
                // Récupérer ses permissions custom
                $userPermissions = $this->model->getUserPermissions($membreId);

                // Récupérer les permissions de son rôle
                $sql = "SELECT id_role FROM roles WHERE nom = :nom";
                $roleResult = $this->model->query($sql, ['nom' => $selectedMembre['role_systeme']]);

                if (!empty($roleResult)) {
                    $rolePermissions = $this->model->getRolePermissions($roleResult[0]['id_role'], true);
                }

                // Toutes les permissions groupées par module
                $allPermissions = $this->model->getAllPermissionsGrouped();
            }
        }

        $this->view->renderUserPermissions($membres, $selectedMembre, $userPermissions, $rolePermissions, $allPermissions);
    }

    /**
     * Sauvegarder les permissions d'un utilisateur
     */
    public function saveUserPermissions()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=admin&section=permissions&action=userPermissions');
            exit;
        }

        $membreId = $_POST['membre_id'] ?? null;
        $grantedPermissions = $_POST['granted_permissions'] ?? [];
        $revokedPermissions = $_POST['revoked_permissions'] ?? [];
        $note = $_POST['note'] ?? '';

        if (!$membreId) {
            BaseView::setFlash('Données invalides', 'error');
            header('Location: ?page=admin&section=permissions&action=userPermissions');
            exit;
        }

        $adminId = $_SESSION['user']['id_membre'];

        // Ajouter les permissions accordées
        foreach ($grantedPermissions as $permId) {
            $this->model->grantPermissionToUser($membreId, $permId, $adminId, $note);
        }

        // Retirer les permissions révoquées
        foreach ($revokedPermissions as $permId) {
            $this->model->revokePermissionFromUser($membreId, $permId, $adminId, $note);
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

    /**
     * Voir les logs de permissions
     */
    public function logs()
    {
        $logs = $this->model->getPermissionLogs(100);
        $this->view->renderLogs($logs);
    }
}
?>