<?php
// Chemins
define('BASE_URL', 'http://localhost/lab_project/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOADS_URL', BASE_URL . 'uploads/');
define('UPLOADS_PATH', __DIR__ . '/../uploads/');

// Couleurs du thème (à gérer depuis l'admin plus tard)
define('PRIMARY_COLOR', '#2563eb');
define('SECONDARY_COLOR', '#1e40af');
define('ACCENT_COLOR', '#3b82f6');
define('SUCCESS_COLOR', '#10b981');
define('WARNING_COLOR', '#f59e0b');
define('DANGER_COLOR', '#ef4444');
define('DARK_COLOR', '#1f2937');
define('LIGHT_COLOR', '#f3f4f6');

// Pagination
define('ITEMS_PER_PAGE', 9);

// Types de publications
define('TYPES_PUBLICATIONS', [
    'article' => 'Article',
    'rapport' => 'Rapport',
    'these' => 'Thèse',
    'communication' => 'Communication',
    'poster' => 'Poster'
]);

// Types d'équipements
define('TYPES_EQUIPEMENTS', [
    'salle' => 'Salle',
    'serveur' => 'Serveur',
    'pc' => 'PC',
    'robot' => 'Robot',
    'imprimante' => 'Imprimante',
    'capteur' => 'Capteur'
]);

// États d'équipements
define('ETATS_EQUIPEMENTS', [
    'libre' => 'Libre',
    'reserve' => 'Réservé',
    'en_maintenance' => 'En maintenance'
]);

// Rôles utilisateurs
define('ROLES', [
    'admin' => 'Administrateur',
    'membre' => 'Membre'
]);

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>