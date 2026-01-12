<?php

return [
    'title' => 'Laboratoire Universitaire - ESI',
    'charset' => 'UTF-8',
    'viewport' => 'width=device-width, initial-scale=1.0',
    'description' => 'Laboratoire de recherche en informatique de l\'École Supérieure d\'Informatique',
    'keywords' => 'laboratoire, recherche, informatique, ESI, Algérie',
    'author' => 'Laboratoire Universitaire',
    'favicon' => ASSETS_URL . 'images/favicon.ico',
    'lang' => 'fr',
    
    'stylesheets' => [
        ASSETS_URL . 'css/style.css',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
    ],
    
    'scripts' => [
        [
            'src' => ASSETS_URL . 'js/main.js',
            'position' => 'body', // Chargé en fin de page
            'defer' => true
        ]
    ],
    
    'meta_tags' => [
        'robots' => 'index, follow',
        'theme-color' => '#3b82f6'
    ],
    
    'open_graph' => [
        'title' => 'Laboratoire Universitaire - ESI',
        'type' => 'website',
        'url' => 'https://lab-esi.dz',
        'image' => ASSETS_URL . 'images/og-image.jpg',
        'description' => 'Laboratoire de recherche en informatique'
    ],
    
    'preload' => [
        // ['href' => ASSETS_URL . 'fonts/main.woff2', 'as' => 'font', 'type' => 'font/woff2']
    ]
];