<?php
require_once __DIR__ . '/BaseModel.php';

class PermissionModel extends BaseModel
{
    /**
     * Récupérer toutes les permissions groupées par module
     */
    public function getAllPermissionsGrouped()
    {
        $sql = "SELECT * FROM permissions ORDER BY module, action, nom";
        $permissions = $this->query($sql);

        // Grouper par module
        $grouped = [];
        foreach ($permissions as $perm) {
            $module = $perm['module'];
            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            $grouped[$module][] = $perm;
        }

        return $grouped;
    }

    /**
     * Récupérer tous les rôles
     */
    public function getAllRoles()
    {
        $sql = "SELECT r.*, 
                       COUNT(DISTINCT rp.id_permission) as nb_permissions,
                       COUNT(DISTINCT m.id_membre) as nb_users
                FROM roles r
                LEFT JOIN role_permissions rp ON r.id_role = rp.id_role
                LEFT JOIN membres m ON m.role_systeme = r.nom
                GROUP BY r.id_role
                ORDER BY r.id_role";

        return $this->query($sql);
    }

    /**
     * Récupérer un rôle par ID avec ses permissions
     */
    public function getRoleWithPermissions($roleId)
    {
        $sql = "SELECT * FROM roles WHERE id_role = :id";
        $role = $this->query($sql, ['id' => $roleId]);

        if (empty($role)) {
            return null;
        }

        $role = $role[0];

        // Récupérer ses permissions
        $sql = "SELECT p.* FROM permissions p
                INNER JOIN role_permissions rp ON p.id_permission = rp.id_permission
                WHERE rp.id_role = :role_id";

        $role['permissions'] = $this->query($sql, ['role_id' => $roleId]);

        return $role;
    }

    /**
     * Récupérer les permissions d'un rôle (avec héritage)
     */
    public function getRolePermissions($roleId, $includeInherited = true)
    {
        $permissions = [];

        if ($includeInherited) {
            // Récupérer la hiérarchie du rôle
            $roleHierarchy = $this->getRoleHierarchy($roleId);

            // Récupérer permissions de tous les rôles parents
            foreach ($roleHierarchy as $rid) {
                $sql = "SELECT p.* FROM permissions p
                        INNER JOIN role_permissions rp ON p.id_permission = rp.id_permission
                        WHERE rp.id_role = :role_id";

                $perms = $this->query($sql, ['role_id' => $rid]);
                foreach ($perms as $perm) {
                    $permissions[$perm['id_permission']] = $perm;
                }
            }
        } else {
            // Seulement les permissions directes
            $sql = "SELECT p.* FROM permissions p
                    INNER JOIN role_permissions rp ON p.id_permission = rp.id_permission
                    WHERE rp.id_role = :role_id";

            $perms = $this->query($sql, ['role_id' => $roleId]);
            foreach ($perms as $perm) {
                $permissions[$perm['id_permission']] = $perm;
            }
        }

        return array_values($permissions);
    }

    /**
     * Récupérer la hiérarchie d'un rôle (lui + tous les parents)
     */
    private function getRoleHierarchy($roleId)
    {
        $hierarchy = [$roleId];

        $sql = "SELECT parent_role FROM roles WHERE id_role = :id";
        $result = $this->query($sql, ['id' => $roleId]);

        if (!empty($result) && $result[0]['parent_role']) {
            $parentHierarchy = $this->getRoleHierarchy($result[0]['parent_role']);
            $hierarchy = array_merge($hierarchy, $parentHierarchy);
        }

        return $hierarchy;
    }

    /**
     * Récupérer les permissions custom d'un utilisateur (granted only)
     */
    public function getUserPermissions($membreId)
    {
        $sql = "SELECT p.* 
                FROM permissions p
                INNER JOIN user_permissions up ON p.id_permission = up.id_permission
                WHERE up.id_membre = :membre_id AND up.granted = 1";

        return $this->query($sql, ['membre_id' => $membreId]);
    }

    /**
     * ⭐ NOUVEAU: Vérifier si un utilisateur a une permission (logique simplifiée)
     */
    public function hasPermission($membreId, $permissionName, $resourceOwnerId = null)
    {
        // ⭐ NOUVEAU: Admin a TOUT
        $sql = "SELECT role_systeme FROM membres WHERE id_membre = :id";
        $result = $this->query($sql, ['id' => $membreId]);

        if (empty($result)) {
            return false;
        }

        $userRole = $result[0]['role_systeme'];

        // ⭐ ADMIN BYPASS: L'admin a toutes les permissions
        if ($userRole === 'admin') {
            return true;
        }

        // Récupérer l'ID du rôle
        $sql = "SELECT id_role FROM roles WHERE nom = :nom";
        $roleResult = $this->query($sql, ['nom' => $userRole]);

        if (empty($roleResult)) {
            return false;
        }

        $roleId = $roleResult[0]['id_role'];

        // 1. Vérifier si permission ajoutée via user_permissions (granted = 1)
        $sql = "SELECT p.is_own FROM user_permissions up
                INNER JOIN permissions p ON up.id_permission = p.id_permission
                WHERE up.id_membre = :membre_id AND p.nom = :perm_name AND up.granted = 1";

        $userPerm = $this->query($sql, [
            'membre_id' => $membreId,
            'perm_name' => $permissionName
        ]);

        if (!empty($userPerm)) {
            // Permission custom accordée - vérifier si "own"
            if ($userPerm[0]['is_own'] && $resourceOwnerId !== null) {
                return $membreId == $resourceOwnerId;
            }
            return true;
        }

        // 2. Vérifier permissions du rôle (avec héritage)
        $rolePermissions = $this->getRolePermissions($roleId, true);
        $hasRolePermission = false;

        foreach ($rolePermissions as $perm) {
            if ($perm['nom'] === $permissionName) {
                $hasRolePermission = true;

                // Si c'est une permission "own", vérifier ownership
                if ($perm['is_own'] && $resourceOwnerId !== null) {
                    return $membreId == $resourceOwnerId;
                }

                break;
            }
        }

        return $hasRolePermission;
    }

    /**
     * Ajouter une permission à un utilisateur
     */
    public function grantPermissionToUser($membreId, $permissionId, $grantedBy, $note = '')
    {
        // Vérifier si déjà existe
        $sql = "SELECT id FROM user_permissions WHERE id_membre = :membre AND id_permission = :perm";
        $existing = $this->query($sql, ['membre' => $membreId, 'perm' => $permissionId]);

        if (!empty($existing)) {
            // Update
            $sql = "UPDATE user_permissions SET granted = 1, granted_by = :by, note = :note, granted_at = NOW()
                    WHERE id_membre = :membre AND id_permission = :perm";

            $this->execute($sql, [
                'membre' => $membreId,
                'perm' => $permissionId,
                'by' => $grantedBy,
                'note' => $note
            ]);
        } else {
            // Insert
            $sql = "INSERT INTO user_permissions (id_membre, id_permission, granted, granted_by, note)
                    VALUES (:membre, :perm, 1, :by, :note)";

            $this->execute($sql, [
                'membre' => $membreId,
                'perm' => $permissionId,
                'by' => $grantedBy,
                'note' => $note
            ]);
        }

        return true;
    }

    /**
     * Réinitialiser les permissions d'un utilisateur (supprimer toutes les permissions custom)
     */
    public function resetUserPermissions($membreId, $resetBy)
    {
        $sql = "DELETE FROM user_permissions WHERE id_membre = :membre";
        $this->execute($sql, ['membre' => $membreId]);

        return true;
    }

    /**
     * Mettre à jour les permissions d'un rôle
     */
    public function updateRolePermissions($roleId, $permissionIds)
    {
        // Supprimer les anciennes
        $sql = "DELETE FROM role_permissions WHERE id_role = :role";
        $this->execute($sql, ['role' => $roleId]);

        // Ajouter les nouvelles
        foreach ($permissionIds as $permId) {
            $sql = "INSERT INTO role_permissions (id_role, id_permission) VALUES (:role, :perm)";
            $this->execute($sql, ['role' => $roleId, 'perm' => $permId]);
        }

        return true;
    }

    /**
     * Créer un nouveau rôle
     */
    public function createRole($nom, $description, $parentRole = null)
    {
        $sql = "INSERT INTO roles (nom, description, parent_role, is_system) 
                VALUES (:nom, :desc, :parent, FALSE)";

        return $this->execute($sql, [
            'nom' => $nom,
            'desc' => $description,
            'parent' => $parentRole
        ]);
    }

    /**
     * Supprimer un rôle
     */
    public function deleteRole($roleId)
    {
        // Vérifier si c'est un rôle système
        $sql = "SELECT is_system FROM roles WHERE id_role = :id";
        $result = $this->query($sql, ['id' => $roleId]);

        if (!empty($result) && $result[0]['is_system']) {
            return false; // Ne peut pas supprimer un rôle système
        }

        // Vérifier s'il y a des utilisateurs
        $sql = "SELECT COUNT(*) as total FROM membres m
                INNER JOIN roles r ON m.role_systeme = r.nom
                WHERE r.id_role = :id";

        $result = $this->query($sql, ['id' => $roleId]);

        if (!empty($result) && $result[0]['total'] > 0) {
            return false; // Ne peut pas supprimer un rôle avec des utilisateurs
        }

        // Supprimer
        $sql = "DELETE FROM roles WHERE id_role = :id";
        return $this->execute($sql, ['id' => $roleId]);
    }
}
?>