<?php

return [
    'about' => [
        'title' => 'À propos',
        'description' => 'Laboratoire de recherche en informatique de l\'École Supérieure d\'Informatique.'
    ],
    'quick_links' => [
        'title' => 'Liens rapides',
        'links' => [
            ['text' => 'Accueil', 'url' => '?page=accueil'],
            ['text' => 'Projets', 'url' => '?page=projets'],
            ['text' => 'Publications', 'url' => '?page=publications'],
            ['text' => 'Membres', 'url' => '?page=membres'],
        ]
    ],
    'contact' => [
        'title' => 'Contact',
        'address' => 'Alger, Algérie',
        'phone' => '+213 XXX XXX XXX',
        'email' => 'contact@lab-esi.dz'
    ],
    'logo' => [
        'text' => 'Lab Universitaire',
        'icon' => 'fas fa-flask',
        'url' => '?page=accueil'
    ],
    'copyright' => [
        'text' => 'Laboratoire Universitaire - École Supérieure d\'Informatique. Tous droits réservés.',
        'show_year' => true
    ],
    // Optionnel: Réseaux sociaux
    'social_links' => [
        // ['name' => 'Facebook', 'url' => 'https://facebook.com/...', 'icon' => 'fab fa-facebook'],
        // ['name' => 'Twitter', 'url' => 'https://twitter.com/...', 'icon' => 'fab fa-twitter'],
        // ['name' => 'LinkedIn', 'url' => 'https://linkedin.com/...', 'icon' => 'fab fa-linkedin'],
    ]
];