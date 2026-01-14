<?php

return [
    
    
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

    
    'conditional_items' => [
        // ADMINS
        [
            'text' => 'Administration',
            'url' => '?page=admin',
            'icon' => 'fas fa-cog',
            'class' => 'btn-admin',
            'condition' => function($user) {
                if (empty($user)) {
                    return false;
                }
                
                // Uniquement pour les  admins
                return ($user['role'] === 'admin' || ($user['role_systeme'] ?? '') === 'admin');
            }
        ],
        
        // NON-ADMINS 
        [
            'text' => 'Gestion',
            'url' => '?page=admin',
            'icon' => 'fas fa-cog',
            'class' => 'btn-gestion',
            'condition' => function($user) {
                if (empty($user)) {
                    return false;
                }
                
          
                if ($user['role'] === 'admin' || ($user['role_systeme'] ?? '') === 'admin') {
                    return false;
                }
                
                // Vérifier si l'utilisateur a au moins une permission de gestion
                require_once __DIR__ . '/../../utils/PermissionHelper.php';
                
                $managementPermissions = [
                    // Permissions "own" (gérer ses propres contenus)
                    'create_own_projet', 'edit_own_projet', 'delete_own_projet',
                    'create_own_publication', 'edit_own_publication', 'delete_own_publication',
                    
                    // Permissions de consultation
                    'view_projets', 'view_publications', 'view_equipements',
                    'view_membres', 'view_equipes', 'view_evenements'
                ];

                foreach ($managementPermissions as $perm) {
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
            'class' => 'btn-profile',
            'show_when' => 'authenticated'
        ],
        'logout' => [
            'text' => 'Déconnexion',
            'url' => '?page=logout',
            'icon' => 'fas fa-sign-out-alt',
            'class' => 'btn-logout',
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