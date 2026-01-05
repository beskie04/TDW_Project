<?php
// clear_cache.php - À la racine du projet
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ Opcache vidé !<br>";
} else {
    echo "❌ Opcache n'est pas activé<br>";
}

if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
    echo "✅ APCu vidé !<br>";
} else {
    echo "❌ APCu n'est pas activé<br>";
}

echo "<br><a href='debug_permissions.php'>Tester les permissions maintenant</a>";