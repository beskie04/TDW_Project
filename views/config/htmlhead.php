<?php
/**
 * HTML Head Configuration
 * 
 * Mix of static technical settings and dynamic database values
 */

// Load settings from database
require_once __DIR__ . '/../../models/SettingsModel.php';
$settingsModel = new SettingsModel();
$dbSettings = $settingsModel->getAllSettings();

// Build configuration
return [
    // === DYNAMIC FROM DATABASE === //
    'title' => $dbSettings['site_title'] ?? 'Laboratoire Universitaire - ESI',
    'description' => $dbSettings['site_description'] ?? 'Laboratoire de recherche en informatique de l\'École Supérieure d\'Informatique',
    'keywords' => $dbSettings['site_keywords'] ?? 'laboratoire, recherche, informatique, ESI, Algérie',
    'author' => $dbSettings['site_author'] ?? 'Laboratoire Universitaire',
    'favicon' => !empty($dbSettings['site_favicon']) 
        ? ASSETS_URL . 'uploads/' . $dbSettings['site_favicon']
        : ASSETS_URL . 'images/favicon.ico',
    
    // === STATIC TECHNICAL SETTINGS === //
    'charset' => 'UTF-8',
    'viewport' => 'width=device-width, initial-scale=1.0',
    'lang' => 'fr', // Could be from DB if multi-language
    
    // === CSS FILES === //
    'stylesheets' => [
        ASSETS_URL . 'css/style.css',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
    ],
    
    // === JAVASCRIPT FILES === //
    'scripts' => [
        [
            'src' => ASSETS_URL . 'js/main.js',
            'position' => 'body', // Load at end of page
            'defer' => true
        ]
    ],
    
    // === ADDITIONAL META TAGS === //
    'meta_tags' => [
        'robots' => 'index, follow',
        'theme-color' => $dbSettings['theme_color'] ?? '#3b82f6'
    ],
    
    // === OPEN GRAPH (Social Media Sharing) === //
    'open_graph' => [
        'title' => $dbSettings['og_title'] ?? $dbSettings['site_title'] ?? 'Laboratoire Universitaire - ESI',
        'type' => 'website',
        'url' => $dbSettings['site_url'] ?? 'https://lab-esi.dz',
        'image' => !empty($dbSettings['og_image'])
            ? ASSETS_URL . 'uploads/' . $dbSettings['og_image']
            : ASSETS_URL . 'images/og-image.jpg',
        'description' => $dbSettings['og_description'] ?? $dbSettings['site_description'] ?? 'Laboratoire de recherche en informatique'
    ],
    
    // === PERFORMANCE OPTIMIZATION === //
    'preload' => [
        // Uncomment to preload fonts for better performance
        // ['href' => ASSETS_URL . 'fonts/main.woff2', 'as' => 'font', 'type' => 'font/woff2', 'crossorigin' => true]
    ]
];