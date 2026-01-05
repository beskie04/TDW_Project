<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/utils/PermissionHelper.php';
require_once __DIR__ . '/models/PermissionModel.php';

if (!isset($_SESSION['user'])) {
    die('Not logged in');
}

$userId = $_SESSION['user']['id_membre'];
echo "<h1>Permission Debug for User ID: $userId</h1>";

$model = new PermissionModel();

// 1. Check role
echo "<h2>1. Role Information</h2>";
echo "Role: " . $_SESSION['user']['role_systeme'] . "<br>";

// 2. Check role permissions
echo "<h2>2. Role Permissions</h2>";
$sql = "SELECT r.id_role FROM roles WHERE nom = :role";
$roleResult = $model->query($sql, ['role' => $_SESSION['user']['role_systeme']]);

if (!empty($roleResult)) {
    $rolePerms = $model->getRolePermissions($roleResult[0]['id_role'], true);
    echo "Found " . count($rolePerms) . " role permissions<br>";
    foreach ($rolePerms as $p) {
        echo "- " . $p['nom'] . " (" . $p['description'] . ")<br>";
    }
}

// 3. Check user custom permissions
echo "<h2>3. Custom User Permissions</h2>";
$userPerms = $model->getUserPermissions($userId);
echo "Found " . count($userPerms) . " custom permissions<br>";
foreach ($userPerms as $p) {
    echo "- " . $p['nom'] . " (granted: " . ($p['granted'] ? 'YES' : 'NO') . ")<br>";
}

// 4. Test specific permissions
echo "<h2>4. Permission Tests</h2>";
$testsPerms = ['view_projets', 'create_projet', 'edit_own_projet'];
foreach ($testsPerms as $perm) {
    $has = hasPermission($perm);
    echo "hasPermission('$perm'): " . ($has ? '✅ YES' : '❌ NO') . "<br>";
}