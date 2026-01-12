<?php

return [
    'logo' => [
        'text' => 'Lab Universitaire',
        'icon' => 'fas fa-flask',
        'url' => '?page=accueil'
    ],
    
    'menu_items' => [
        [
            'text' => 'Accueil',
            'url' => '?page=accueil',
            'page' => 'accueil',
            'show_when' => 'always'
        ],
        [
            'text' => 'Projets',
            'url' => '?page=projets',
            'page' => 'projets',
            'show_when' => 'always'
        ],
        [
            'text' => 'Publications',
            'url' => '?page=publications',
            'page' => 'publications',
            'show_when' => 'always'
        ],
        [
            'text' => 'Équipements',
            'url' => '?page=equipements',
            'page' => 'equipements',
            'show_when' => 'always'
        ],
        [
            'text' => 'Membres',
            'url' => '?page=membres',
            'page' => 'membres',
            'show_when' => 'always'
        ],
        [
            'text' => 'Événements',
            'url' => '?page=evenements',
            'page' => 'evenements',
            'show_when' => 'always'
        ],
        [
            'text' => 'Opportunités',
            'url' => '?page=offres',
            'page' => 'offres',
            'show_when' => 'always'
        ],
        [
            'text' => 'Contact',
            'url' => '?page=contact',
            'page' => 'contact',
            'show_when' => 'always'
        ]
    ],

    // Items conditionnels basés sur permissions/rôles
    'conditional_items' => [
        [
            'text' => 'Administration',
            'url' => '?page=admin',
            'icon' => 'fas fa-cog',
            'class' => 'admin-link',
            'condition' => function($user) {
                if (empty($user)) {
                    return false;
                }
                
                // Admin complet
                if ($user['role'] === 'admin' || ($user['role_systeme'] ?? '') === 'admin') {
                    return true;
                }
                
                // Utilisateur avec permissions admin
                $adminPermissions = [
                    'view_projets', 'create_projet', 'edit_projet', 'delete_projet',
                    'view_publications', 'create_publication', 'edit_publication',
                    'view_equipements', 'create_equipement', 'edit_equipement',
                    'view_membres', 'create_membre', 'edit_membre',
                    'view_equipes', 'create_equipe', 'edit_equipe',
                    'view_evenements', 'create_evenement', 'edit_evenement',
                    'view_annonces', 'create_annonce', 'edit_annonce',
                    'view_messages', 'respond_message', 'delete_message',
                    'view_offres', 'create_offre', 'edit_offre'
                ];

                foreach ($adminPermissions as $perm) {
                    if (hasPermission($perm)) {
                        return true;
                    }
                }
                
                return false;
            }
        ],
        // Vous pouvez ajouter d'autres items conditionnels ici
        [
            'text' => 'Gestion',
            'url' => '?page=admin',
            'icon' => 'fas fa-cog',
            'class' => 'admin-link',
            'condition' => function($user) {
                // Afficher "Gestion" au lieu de "Administration" pour les non-admins avec permissions
                if (empty($user)) {
                    return false;
                }
                
                if ($user['role'] === 'admin' || ($user['role_systeme'] ?? '') === 'admin') {
                    return false; // Admin voit "Administration" à la place
                }
                
                // A des permissions mais n'est pas admin
                $adminPermissions = [
                    'view_projets', 'create_projet', 'view_publications', 
                    'view_equipements', 'view_membres', 'view_evenements'
                ];

                foreach ($adminPermissions as $perm) {
                    if (hasPermission($perm)) {
                        return true;
                    }
                }
                
                return false;
            }
        ]
    ],

    'user_menu' => [
        'profile' => [
            'text' => 'Profil',
            'url' => '?page=profil',
            'icon' => 'fas fa-user',
            'show_when' => 'authenticated'
        ],
        'logout' => [
            'text' => 'Déconnexion',
            'url' => '?page=logout',
            'icon' => 'fas fa-sign-out-alt',
            'show_when' => 'authenticated'
        ],
        'login' => [
            'text' => 'Connexion',
            'url' => '?page=login',
            'class' => 'btn-login',
            'show_when' => 'guest'
        ]
    ],

    'mobile_enabled' => true,
    'container_class' => 'container'
];