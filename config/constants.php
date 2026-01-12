<?php
// Chemins
define('BASE_URL', 'http://localhost/lab_project/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOADS_URL', BASE_URL . 'uploads/');
define('UPLOADS_PATH', __DIR__ . '/../uploads/');

// Couleurs principales du thème
define('PRIMARY_COLOR', '#2563eb');
define('SECONDARY_COLOR', '#1e40af');
define('ACCENT_COLOR', '#3b82f6');
define('SUCCESS_COLOR', '#10b981');
define('WARNING_COLOR', '#f59e0b');
define('DANGER_COLOR', '#ef4444');
define('INFO_COLOR', '#06b6d4');
define('DARK_COLOR', '#1f2937');
define('LIGHT_COLOR', '#f3f4f6');

// Couleurs pour les types d'actualités
define('COLOR_PROJET', '#3b82f6');
define('COLOR_PUBLICATION', '#10b981');
define('COLOR_COLLABORATION', '#f59e0b');
define('COLOR_SOUTENANCE', '#8b5cf6');
define('COLOR_EVENEMENT', '#ec4899');

// Couleurs pour les statuts de projets
define('STATUS_EN_COURS', '#10b981');      // Vert
define('STATUS_TERMINE', '#6b7280');       // Gris
define('STATUS_SOUMIS', '#f59e0b');        // Orange
define('STATUS_PLANIFIE', '#3b82f6');      // Bleu
define('STATUS_SUSPENDU', '#ef4444');      // Rouge

// Couleurs pour les postes (organigramme)
define('COLOR_DIRECTEUR', '#3b82f6');
define('COLOR_CHEF', '#10b981');
define('COLOR_MEMBRE_DEFAULT', '#f59e0b');

// Couleurs pour les types de partenaires
define('COLOR_UNIVERSITE', '#3b82f6');
define('COLOR_ENTREPRISE', '#10b981');
define('COLOR_ORGANISME', '#f59e0b');

// Couleurs pour les badges/labels
define('BADGE_INFO', '#3b82f6');
define('BADGE_SUCCESS', '#10b981');
define('BADGE_WARNING', '#f59e0b');
define('BADGE_DANGER', '#ef4444');
define('BADGE_PURPLE', '#8b5cf6');
define('BADGE_PINK', '#ec4899');
define('BADGE_INDIGO', '#6366f1');
define('BADGE_TEAL', '#14b8a6');
define('BADGE_DEFAULT', '#6b7280');

// Couleurs de texte
define('TEXT_DARK', '#1f2937');
define('TEXT_GRAY', '#6b7280');
define('TEXT_LIGHT', '#9ca3af');
define('TEXT_WHITE', '#ffffff');

// Couleurs de fond
define('BG_WHITE', '#ffffff');
define('BG_GRAY_LIGHT', '#f9fafb');
define('BG_GRAY', '#f3f4f6');
define('BG_GRAY_DARK', '#e5e7eb');

// Couleurs de bordure
define('BORDER_GRAY', '#e5e7eb');
define('BORDER_GRAY_LIGHT', '#f3f4f6');

// Espacements standards
define('SPACING_XS', '0.25rem');
define('SPACING_SM', '0.5rem');
define('SPACING_MD', '1rem');
define('SPACING_LG', '1.5rem');
define('SPACING_XL', '2rem');
define('SPACING_2XL', '3rem');

// Rayons de bordure
define('RADIUS_SM', '6px');
define('RADIUS_MD', '8px');
define('RADIUS_LG', '12px');
define('RADIUS_FULL', '9999px');

// Ombres
define('SHADOW_SM', '0 1px 2px rgba(0, 0, 0, 0.05)');
define('SHADOW_MD', '0 1px 3px rgba(0, 0, 0, 0.1)');
define('SHADOW_LG', '0 4px 6px rgba(0, 0, 0, 0.1)');
define('SHADOW_HOVER', '0 4px 12px rgba(0, 0, 0, 0.15)');

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