<?php
/**
 * Permission Helper
 * Fonction globale pour vérifier les permissions
 */

require_once __DIR__ . '/../models/PermissionModel.php';

class PermissionHelper
{
    private static $permissionModel = null;
    private static $cache = [];

    /**
     * Vérifier si l'utilisateur connecté a une permission
     */
    public static function hasPermission($permissionName, $resourceOwnerId = null)
    {
        // Vérifier si utilisateur connecté
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id_membre'])) {
            return false;
        }

        $membreId = $_SESSION['user']['id_membre'];

        // Cache key
        $cacheKey = $membreId . '_' . $permissionName . '_' . ($resourceOwnerId ?? 'null');

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        // Lazy load model
        if (self::$permissionModel === null) {
            self::$permissionModel = new PermissionModel();
        }

        $result = self::$permissionModel->hasPermission($membreId, $permissionName, $resourceOwnerId);

        // Cache le résultat
        self::$cache[$cacheKey] = $result;

        return $result;
    }

    /**
     * Vérifier si admin
     */
    public static function isAdmin()
    {
        return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
    }

    /**
     * Clear cache (appeler après changement de permissions)
     */
    public static function clearCache()
    {
        self::$cache = [];
    }
}

/**
 * Fonction globale raccourcie
 */
function hasPermission($permissionName, $resourceOwnerId = null)
{
    return PermissionHelper::hasPermission($permissionName, $resourceOwnerId);
}

function isAdmin()
{
    return PermissionHelper::isAdmin();
}
?>