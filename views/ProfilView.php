<?php
require_once __DIR__ . '/BaseView.php';
require_once __DIR__ . '/components/Card.php';
require_once __DIR__ . '/components/StatCard.php';
require_once __DIR__ . '/components/Avatar.php';
require_once __DIR__ . '/components/Badge.php';
require_once __DIR__ . '/components/Button.php';
require_once __DIR__ . '/components/Alert.php';
require_once __DIR__ . '/components/FormInput.php';
require_once __DIR__ . '/components/EmptyState.php';

class ProfilView extends BaseView
{   
    protected $pageTitle = 'Mon Profil - Laboratoire Universitaire';
    protected $currentPage = 'profil';

    /**
     * Rendre le tableau de bord
     */
    public function renderDashboard($profile, $projets, $publications, $reservations, $equipes)
    {
        $this->renderHeader();
        ?>

        <div class="container dashboard-container">
            <?php $this->renderFlashMessage(); ?>

            <!-- En-tête du profil -->
            <div class="profile-header">
                <div class="profile-header-content">
                    <div class="profile-avatar-section">
                        <?php
                        $photoPath = null;
                        if (!empty($profile['photo'])) {
                            $photoPath = UPLOADS_URL . 'photos/' . $profile['photo'];
                        }
                        
                        Avatar::render([
                            'src' => $photoPath,
                            'name' => $profile['prenom'] . ' ' . $profile['nom'],
                            'size' => 'xl'
                        ]);
                        ?>
                        <div class="profile-info">
                            <h1><?= htmlspecialchars($profile['prenom'] . ' ' . $profile['nom']) ?></h1>
                            <p class="profile-subtitle">
                                <?= htmlspecialchars($profile['poste'] ?? 'Membre') ?> • 
                                <?= htmlspecialchars($profile['grade'] ?? 'N/A') ?>
                            </p>
                            <div class="profile-badges">
                                <?php
                                Badge::render([
                                    'text' => $profile['role'] ?? 'membre',
                                    'variant' => 'secondary'
                                ]);

                                if (isset($profile['role_systeme']) && $profile['role_systeme'] === 'admin') {
                                    Badge::render([
                                        'text' => 'Administrateur',
                                        'variant' => 'danger'
                                    ]);
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="profile-actions">
                        <?php
                        Button::render([
                            'text' => 'Modifier le profil',
                            'href' => '?page=profil&action=edit',
                            'variant' => 'primary',
                            'icon' => 'fa-edit'
                        ]);
                        ?>
                    </div>
                </div>
            </div>

         

            <!-- Onglets -->
            <div class="dashboard-tabs">
                <button class="tab-btn active" data-tab="projets">
                    <i class="fas fa-project-diagram"></i> Mes Projets
                </button>
                <button class="tab-btn" data-tab="publications">
                    <i class="fas fa-file-alt"></i> Mes Publications
                </button>
                <button class="tab-btn" data-tab="reservations">
                    <i class="fas fa-calendar-check"></i> Mes Réservations
                </button>
                <button class="tab-btn" data-tab="equipes">
                    <i class="fas fa-users"></i> Mes Équipes
                </button>
                <button class="tab-btn" data-tab="documents">
                    <i class="fas fa-folder"></i> Mes Documents
                </button>
            </div>

            <!-- Contenu des onglets -->
            <div class="tab-content active" id="projets-tab">
                <?php $this->renderProjetsSection($projets); ?>
            </div>

            <div class="tab-content" id="publications-tab">
                <?php $this->renderPublicationsSection($publications); ?>
            </div>

            <div class="tab-content" id="reservations-tab">
                <?php $this->renderReservationsSection($reservations); ?>
            </div>

            <div class="tab-content" id="equipes-tab">
                <?php $this->renderEquipesSection($equipes); ?>
            </div>

            <div class="tab-content" id="documents-tab">
                <?php $this->renderDocumentsSection(); ?>
            </div>
        </div>

        <style>
            .profile-header {
                background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
                border-radius: 12px;
                padding: 2rem;
                margin-bottom: 2rem;
                color: white;
            }

            .profile-header-content {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 1.5rem;
            }

            .profile-avatar-section {
                display: flex;
                align-items: center;
                gap: 1.5rem;
            }

            .profile-info h1 {
                font-size: 1.8rem;
                margin-bottom: 0.5rem;
                color: white;
            }

            .profile-subtitle {
                color: rgba(255, 255, 255, 0.9);
                margin-bottom: 0.75rem;
            }

            .profile-badges {
                display: flex;
                gap: 0.5rem;
                flex-wrap: wrap;
            }

            /* Stats Cards */
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }

            .stat-card {
                background: white;
                border-radius: 12px;
                padding: 1.5rem;
                display: flex;
                align-items: center;
                gap: 1.5rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                transition: transform 0.2s, box-shadow 0.2s;
            }

            .stat-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }

            .stat-icon {
                width: 60px;
                height: 60px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
                color: white;
            }

            .stat-blue .stat-icon {
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            }

            .stat-green .stat-icon {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            }

            .stat-orange .stat-icon {
                background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            }

            .stat-purple .stat-icon {
                background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            }

            .stat-content h3 {
                font-size: 2rem;
                font-weight: bold;
                color: #1f2937;
                margin: 0;
            }

            .stat-content p {
                color: #6b7280;
                margin: 0;
                font-size: 0.95rem;
            }

            /* Tabs */
            .dashboard-tabs {
                display: flex;
                gap: 0.5rem;
                margin-bottom: 2rem;
                border-bottom: 2px solid #e5e7eb;
                overflow-x: auto;
                flex-wrap: wrap;
            }

            .tab-btn {
                padding: 0.75rem 1.5rem;
                background: none;
                border: none;
                color: #6b7280;
                font-weight: 500;
                cursor: pointer;
                border-bottom: 3px solid transparent;
                transition: all 0.2s;
                white-space: nowrap;
            }

            .tab-btn:hover {
                color: #1e40af;
                background: #f3f4f6;
            }

            .tab-btn.active {
                color: #1e40af;
                border-bottom-color: #1e40af;
            }

            .tab-btn i {
                margin-right: 0.5rem;
            }

            .tab-content {
                display: none;
            }

            .tab-content.active {
                display: block;
            }
        </style>

        <script>
            // Gestion des onglets
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const tabName = btn.dataset.tab;

                    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

                    btn.classList.add('active');
                    document.getElementById(tabName + '-tab').classList.add('active');
                });
            });
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Section Projets
     */
    private function renderProjetsSection($projets)
    {
        if (empty($projets)) {
            EmptyState::render([
                'icon' => 'fa-project-diagram',
                'title' => 'Aucun projet',
                'message' => 'Vous ne participez à aucun projet pour le moment.'
            ]);
            return;
        }

        echo '<div class="projets-grid">';
        foreach ($projets as $projet) {
            ?>
            <div class="projet-card">
                <div class="projet-header">
                    <h3>
                        <a href="?page=projets&action=details&id=<?= $projet['id_projet'] ?>">
                            <?= htmlspecialchars($projet['titre']) ?>
                        </a>
                    </h3>
                    <?php
                    if (isset($projet['nom_statut']) && $projet['nom_statut']) {
                        Badge::render([
                            'text' => $projet['nom_statut'],
                            'variant' => 'secondary'
                        ]);
                    }
                    ?>
                </div>
                <p class="projet-description">
                    <?= htmlspecialchars(substr($projet['description'], 0, 150)) ?>...
                </p>
                <div class="projet-meta">
                    <span><i class="fas fa-tag"></i> <?= htmlspecialchars($projet['nom_thematique']) ?></span>
                    <span><i class="fas fa-user"></i> <?= htmlspecialchars($projet['role_projet']) ?></span>
                    <span><i class="fas fa-calendar"></i> <?= date('Y', strtotime($projet['date_debut'])) ?></span>
                </div>
            </div>
            <?php
        }
        echo '</div>';
    }

    /**
     * Section Publications
     */
    private function renderPublicationsSection($publications)
    {
        if (empty($publications)) {
            EmptyState::render([
                'icon' => 'fa-file-alt',
                'title' => 'Aucune publication',
                'message' => 'Vous n\'avez pas encore de publications enregistrées.'
            ]);
            return;
        }

        $typeIcons = [
            'article' => 'fa-file-alt',
            'communication' => 'fa-comments',
            'rapport' => 'fa-file-pdf',
            'these' => 'fa-graduation-cap',
            'poster' => 'fa-image',
            'chapitre' => 'fa-book'
        ];

        echo '<div class="publications-list">';
        foreach ($publications as $pub) {
            $icon = $typeIcons[strtolower($pub['type'])] ?? 'fa-file-alt';
            ?>
            <div class="publication-item">
                <div class="publication-icon">
                    <i class="fas <?= $icon ?>"></i>
                </div>
                <div class="publication-content">
                    <h4><?= htmlspecialchars($pub['titre']) ?></h4>
                    <p class="publication-meta">
                        <?= htmlspecialchars($pub['auteurs']) ?> • 
                        <?= $pub['annee'] ?> • 
                        <?= htmlspecialchars($pub['type']) ?>
                    </p>
                    <?php if (!empty($pub['resume'])): ?>
                        <p class="publication-resume">
                            <?= htmlspecialchars(substr($pub['resume'], 0, 200)) ?>...
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
        echo '</div>';
    }

    /**
     * Section Réservations
     */
    private function renderReservationsSection($reservations)
    {
        if (empty($reservations)) {
            EmptyState::render([
                'icon' => 'fa-calendar-check',
                'title' => 'Aucune réservation',
                'message' => 'Vous n\'avez aucune réservation active.',
                'actionText' => 'Réserver un équipement',
                'actionUrl' => '?page=equipements'
            ]);
            return;
        }

        echo '<div class="reservations-list">';
        foreach ($reservations as $res) {
            $statusClass = '';
            $statusText = ucfirst($res['statut']);

            switch ($res['statut']) {
                case 'confirmee':
                    $statusClass = 'success';
                    break;
                case 'en_attente':
                    $statusClass = 'warning';
                    break;
                case 'annulee':
                    $statusClass = 'danger';
                    break;
                case 'terminee':
                    $statusClass = 'secondary';
                    break;
            }
            ?>
            <div class="reservation-card">
                <div class="reservation-header">
                    <h4><?= htmlspecialchars($res['equipement_nom']) ?></h4>
                    <?php
                    Badge::render([
                        'text' => $statusText,
                        'variant' => $statusClass
                    ]);
                    ?>
                </div>
                <div class="reservation-details">
                    <p><i class="fas fa-info-circle"></i> <?= htmlspecialchars($res['type']) ?></p>
                    <p><i class="fas fa-calendar"></i> Du <?= date('d/m/Y H:i', strtotime($res['date_debut'])) ?></p>
                    <p><i class="fas fa-calendar"></i> Au <?= date('d/m/Y H:i', strtotime($res['date_fin'])) ?></p>
                </div>
            </div>
            <?php
        }
        echo '</div>';
    }

    /**
     * Section Équipes
     */
    private function renderEquipesSection($equipes)
    {
        if (empty($equipes)) {
            EmptyState::render([
                'icon' => 'fa-users',
                'title' => 'Aucune équipe',
                'message' => 'Vous ne faites partie d\'aucune équipe de recherche.'
            ]);
            return;
        }

        echo '<div class="equipes-grid">';
        foreach ($equipes as $equipe) {
            ?>
            <div class="equipe-card">
                <h3><?= htmlspecialchars($equipe['nom']) ?></h3>
                <p><?= htmlspecialchars(substr($equipe['description'], 0, 100)) ?>...</p>
                <div class="equipe-meta">
                    <div class="equipe-chef">
                        <?php
                        Avatar::render([
                            'src' => $equipe['chef_photo'] ? ASSETS_URL . 'uploads/photos/' . $equipe['chef_photo'] : null,
                            'name' => $equipe['chef_prenom'] . ' ' . $equipe['chef_nom'],
                            'size' => 'sm'
                        ]);
                        ?>
                        <span>Chef: <?= htmlspecialchars($equipe['chef_prenom'] . ' ' . $equipe['chef_nom']) ?></span>
                    </div>
                    <span><i class="fas fa-users"></i> <?= $equipe['nb_membres'] ?> membres</span>
                </div>
            </div>
            <?php
        }
        echo '</div>';
    }

    /**
     * Section Documents
     */
    private function renderDocumentsSection()
{
    ?>
    <div class="documents-section">
        <!-- Upload Form -->
        <div class="documents-upload-card">
            <h3><i class="fas fa-cloud-upload-alt"></i> Uploader un nouveau document</h3>
            <form method="POST" action="?page=profil&action=uploadDocument" enctype="multipart/form-data" class="upload-form">
                
                <div class="form-group">
                    <label for="nom_document" class="form-label">
                        <i class="fas fa-tag"></i> Nom du document *
                    </label>
                    <input 
                        type="text" 
                        id="nom_document"
                        name="nom_document" 
                        class="form-control"
                        placeholder="Ex: CV 2024, Diplôme Master..."
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="type_document" class="form-label">
                        <i class="fas fa-folder"></i> Type de document *
                    </label>
                    <select id="type_document" name="type_document" class="form-control" required>
                        <option value="">-- Sélectionnez un type --</option>
                        <option value="CV">CV</option>
                        <option value="Diplôme">Diplôme</option>
                        <option value="Article">Article</option>
                        <option value="Rapport">Rapport</option>
                        <option value="Certificat">Certificat</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="document" class="form-label">
                        <i class="fas fa-file-upload"></i> Fichier *
                    </label>
                    <input 
                        type="file" 
                        id="document"
                        name="document" 
                        class="form-control file-input"
                        accept=".pdf,.doc,.docx"
                        required
                    >
                    <small class="form-help">
                        <i class="fas fa-info-circle"></i> Formats acceptés: PDF, Word (DOC, DOCX) - Max 10MB
                    </small>
                </div>

                <button type="submit" class="btn btn-primary btn-upload">
                    <i class="fas fa-upload"></i> Uploader le document
                </button>
            </form>
        </div>

        <!-- Documents List -->
        <div class="documents-list-card">
            <h3><i class="fas fa-folder-open"></i> Mes documents</h3>
            <div id="documents-list-container">
                <div class="loading-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Chargement des documents...</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .documents-section {
            display: grid;
            gap: 2rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 968px) {
            .documents-section {
                grid-template-columns: 400px 1fr;
            }
        }

        /* Upload Card */
        .documents-upload-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            height: fit-content;
        }

        .documents-upload-card h3 {
            color: #1f2937;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .documents-upload-card h3 i {
            color: #1e40af;
        }

        /* Form Styles */
        .upload-form .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-label i {
            color: #1e40af;
            margin-right: 0.5rem;
            width: 16px;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.2s;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: #1e40af;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        select.form-control {
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            appearance: none;
        }

        .file-input {
            cursor: pointer;
            padding: 0.5rem;
        }

        .file-input::file-selector-button {
            padding: 0.5rem 1rem;
            border: none;
            background: #f3f4f6;
            border-radius: 6px;
            color: #374151;
            cursor: pointer;
            font-weight: 500;
            margin-right: 1rem;
            transition: background 0.2s;
        }

        .file-input::file-selector-button:hover {
            background: #e5e7eb;
        }

        .form-help {
            display: block;
            margin-top: 0.5rem;
            color: #6b7280;
            font-size: 0.85rem;
        }

        .form-help i {
            margin-right: 0.25rem;
        }

        .btn-upload {
            width: 100%;
            padding: 0.875rem 1.5rem;
            background: #1e40af;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 1rem;
            margin-top: 2rem;
        }

        .btn-upload:hover {
            background: #1e3a8a;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
        }

        .btn-upload:active {
            transform: translateY(0);
        }

        /* Documents List Card */
        .documents-list-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .documents-list-card h3 {
            color: #1f2937;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .documents-list-card h3 i {
            color: #1e40af;
        }

        .loading-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
        }

        .loading-state i {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #1e40af;
        }

        .loading-state p {
            margin: 0;
        }

        /* Documents List Items */
        .documents-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .document-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f9fafb;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            transition: all 0.2s;
        }

        .document-item:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
        }

        .document-icon {
            width: 48px;
            height: 48px;
            background: #fee2e2;
            color: #dc2626;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .document-info {
            flex: 1;
            min-width: 0;
        }

        .document-info h4 {
            margin: 0 0 0.25rem 0;
            font-size: 1rem;
            color: #1f2937;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .document-info p {
            margin: 0;
            font-size: 0.85rem;
            color: #6b7280;
        }

        .document-actions {
            display: flex;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            background: white;
            color: #6b7280;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-icon:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
            color: #374151;
        }

        .btn-icon.btn-danger {
            color: #dc2626;
        }

        .btn-icon.btn-danger:hover {
            background: #fef2f2;
            border-color: #fecaca;
        }
    </style>

    <script>
        // Load documents via AJAX
        fetch('?page=profil&action=getDocuments')
            .then(res => res.json())
            .then(data => {
                document.getElementById('documents-list-container').innerHTML = data.html;
            })
            .catch(err => {
                document.getElementById('documents-list-container').innerHTML = 
                    '<div class="loading-state"><i class="fas fa-exclamation-triangle"></i><p>Erreur lors du chargement des documents</p></div>';
            });
    </script>
    <?php
}

 /**
     * Rendre la page d'édition du profil 
     */
    public function renderEdit($profile, $errors = [])
    {
        $this->renderHeader();
        ?>

        <div class="container edit-profile-container">
            <?php $this->renderFlashMessage(); ?>

            <div class="page-header">
                <h1><i class="fas fa-edit"></i> Modifier mon profil</h1>
                <a href="?page=profil" class="btn-back"><i class="fas fa-arrow-left"></i> Retour au profil</a>
            </div>

            <div class="edit-profile-grid">
                <!-- Photo de profil -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-camera"></i>
                        <h3>Photo de profil</h3>
                    </div>
                    <div class="card-body">
                        <?= $this->getPhotoUploadForm($profile) ?>
                    </div>
                </div>

                <!-- Informations personnelles -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user"></i>
                        <h3>Informations personnelles</h3>
                    </div>
                    <div class="card-body">
                        <?= $this->getInfoForm($profile, $errors) ?>
                    </div>
                </div>

                <!-- Changement de mot de passe -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-key"></i>
                        <h3>Changer le mot de passe</h3>
                    </div>
                    <div class="card-body">
                        <?= $this->getPasswordForm() ?>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .edit-profile-container {
                padding: 2rem 0;
                max-width: 1200px;
                margin: 0 auto;
            }

            .page-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 2rem;
                padding-bottom: 1rem;
                border-bottom: 2px solid #e5e7eb;
            }

            .page-header h1 {
                color: #1f2937;
                font-size: 1.8rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .btn-back {
                color: #1e40af;
                text-decoration: none;
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                transition: color 0.2s;
            }

            .btn-back:hover {
                color: #1e3a8a;
                text-decoration: underline;
            }

            .edit-profile-grid {
                display: grid;
                gap: 2rem;
                grid-template-columns: 1fr;
            }

            @media (min-width: 768px) {
                .edit-profile-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
                
                .edit-profile-grid > .card:first-child {
                    grid-column: 1 / 2;
                }
                
                .edit-profile-grid > .card:nth-child(2) {
                    grid-column: 2 / 3;
                    grid-row: 1 / 3;
                }
                
                .edit-profile-grid > .card:last-child {
                    grid-column: 1 / 2;
                }
            }

            .card {
                background: white;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }

            .card-header {
                background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
                color: white !important;
                padding: 1.25rem 1.5rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .card-header i {
                font-size: 1.25rem;
                color: white !important;
            }

            .card-header h3 {
                margin: 0;
                font-size: 1.1rem;
                font-weight: 600;
                color: white !important;
            }

            .card-body {
                padding: 1.5rem;
            }

            .photo-upload-form {
                text-align: center;
            }

            .current-photo {
                margin-bottom: 1.5rem;
            }

            .photo-upload-form form {
                max-width: 400px;
                margin: 0 auto;
            }

            .info-form,
            .password-form {
                max-width: 100%;
            }

            .form-row {
                margin-bottom: 1.5rem;
            }

            .form-row:last-child {
                margin-bottom: 0;
            }

            .form-actions {
                margin-top: 2rem;
                padding-top: 2rem;
                border-top: 1px solid #e5e7eb;
            }
        </style>

        <?php
        $this->renderFooter();
    }
   /**
     * Formulaire de photo
     */
    private function getPhotoUploadForm($profile)
    {
        ob_start();
        ?>
        <div class="photo-upload-form">
            <div class="current-photo">
                <?php
                $photoPath = null;
                if (!empty($profile['photo'])) {
                    $photoPath = ASSETS_URL . 'uploads/photos/' . $profile['photo'];
                }
                
                Avatar::render([
                    'src' => $photoPath,
                    'name' => $profile['prenom'] . ' ' . $profile['nom'],
                    'size' => 'xxl'
                ]);
                ?>
            </div>
            <form method="POST" action="?page=profil&action=updatePhoto" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="photo" class="form-label">
                        <i class="fas fa-image"></i> Sélectionner une nouvelle photo
                    </label>
                    <input 
                        type="file" 
                        id="photo" 
                        name="photo" 
                        class="form-control" 
                        accept="image/jpeg,image/jpg,image/png,image/gif"
                        required
                    >
                    <small class="form-help">Formats acceptés: JPG, PNG, GIF (Max 5MB)</small>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-upload"></i> Mettre à jour la photo
                </button>
            </form>
        </div>
        
        <style>
            .photo-upload-form {
                text-align: center;
            }
            
            .current-photo {
                margin-bottom: 2rem;
                padding: 1rem;
                background: #f9fafb;
                border-radius: 8px;
            }
            
            .form-group {
                margin-bottom: 1.5rem;
                text-align: left;
            }
            
            .form-label {
                display: block;
                font-weight: 600;
                color: #374151;
                margin-bottom: 0.5rem;
                font-size: 0.95rem;
            }
            
            .form-label i {
                color: #1e40af;
                margin-right: 0.5rem;
            }
            
            .form-control {
                width: 100%;
                padding: 0.75rem;
                border: 2px solid #e5e7eb;
                border-radius: 8px;
                font-size: 0.95rem;
                transition: border-color 0.2s;
            }
            
            .form-control:focus {
                outline: none;
                border-color: #1e40af;
            }
            
            .form-help {
                display: block;
                margin-top: 0.5rem;
                color: #6b7280;
                font-size: 0.85rem;
            }
            
            .btn {
                padding: 0.75rem 1.5rem;
                border: none;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                justify-content: center;
            }
            
            .btn-primary {
                background: #1e40af;
                color: white;
            }
            
            .btn-primary:hover {
                background: #1e3a8a;
            }
            
            .btn-block {
                width: 100%;
            }
        </style>
        <?php
        return ob_get_clean();
    }

    /**
     * Formulaire d'informations
     */
    private function getInfoForm($profile, $errors)
    {
        ob_start();
        ?>
        <form method="POST" action="?page=profil&action=update" class="info-form">
            <div class="form-group">
                <label for="poste" class="form-label">
                    <i class="fas fa-briefcase"></i> Poste *
                </label>
                <input 
                    type="text" 
                    id="poste" 
                    name="poste" 
                    class="form-control <?= isset($errors['poste']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($profile['poste'] ?? '') ?>"
                    placeholder="Ex: Enseignant-Chercheur, Directeur du Laboratoire"
                    required
                >
                <?php if (isset($errors['poste'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['poste']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="grade" class="form-label">
                    <i class="fas fa-graduation-cap"></i> Grade *
                </label>
                <input 
                    type="text" 
                    id="grade" 
                    name="grade" 
                    class="form-control <?= isset($errors['grade']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($profile['grade'] ?? '') ?>"
                    placeholder="Ex: Professeur, Maître de Conférences A"
                    required
                >
                <?php if (isset($errors['grade'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['grade']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="domaine_recherche" class="form-label">
                    <i class="fas fa-microscope"></i> Domaine de recherche
                </label>
                <textarea 
                    id="domaine_recherche" 
                    name="domaine_recherche" 
                    class="form-control"
                    rows="4"
                    placeholder="Ex: Intelligence Artificielle, Machine Learning, Systèmes Distribués..."
                ><?= htmlspecialchars($profile['domaine_recherche'] ?? '') ?></textarea>
                <small class="form-help">Décrivez vos principaux domaines de recherche et axes de travail</small>
            </div>

            <div class="form-group">
                <label for="biographie" class="form-label">
                    <i class="fas fa-user-circle"></i> Biographie / Parcours académique
                </label>
                <textarea 
                    id="biographie" 
                    name="biographie" 
                    class="form-control"
                    rows="6"
                    placeholder="Décrivez votre parcours académique et professionnel, vos diplômes, vos expériences..."
                ><?= htmlspecialchars($profile['biographie'] ?? '') ?></textarea>
                <small class="form-help">Votre parcours académique, expériences, diplômes obtenus, etc.</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
            </div>
        </form>

        <style>
            .info-form {
                max-width: 100%;
            }

            .form-group {
                margin-bottom: 1.5rem;
            }
            
            .form-label {
                display: block;
                font-weight: 600;
                color: #374151;
                margin-bottom: 0.5rem;
                font-size: 0.95rem;
            }
            
            .form-label i {
                color: #1e40af;
                margin-right: 0.5rem;
            }
            
            .form-control {
                width: 100%;
                padding: 0.75rem;
                border: 2px solid #e5e7eb;
                border-radius: 8px;
                font-size: 0.95rem;
                transition: border-color 0.2s;
                font-family: inherit;
            }
            
            .form-control:focus {
                outline: none;
                border-color: #1e40af;
            }
            
            .form-control.is-invalid {
                border-color: #dc2626;
            }
            
            .invalid-feedback {
                display: block;
                color: #dc2626;
                font-size: 0.875rem;
                margin-top: 0.25rem;
            }
            
            .form-help {
                display: block;
                margin-top: 0.5rem;
                color: #6b7280;
                font-size: 0.85rem;
            }
            
            textarea.form-control {
                resize: vertical;
                min-height: 100px;
            }

            .form-actions {
                margin-top: 2rem;
                padding-top: 2rem;
                border-top: 1px solid #e5e7eb;
            }
            
            .btn {
                padding: 0.75rem 1.5rem;
                border: none;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                justify-content: center;
            }
            
            .btn-primary {
                background: #1e40af;
                color: white;
            }
            
            .btn-primary:hover {
                background: #1e3a8a;
            }
            
            .btn-block {
                width: 100%;
            }
        </style>
        <?php
        return ob_get_clean();
    }

    /**
     * Formulaire de mot de passe
     */
    private function getPasswordForm()
    {
        $errors = $_SESSION['password_errors'] ?? [];
        unset($_SESSION['password_errors']);

        ob_start();
        ?>
        <form method="POST" action="?page=profil&action=changePassword" class="password-form">
            <div class="form-group">
                <label for="current_password" class="form-label">
                    <i class="fas fa-lock"></i> Mot de passe actuel *
                </label>
                <input 
                    type="password" 
                    id="current_password" 
                    name="current_password" 
                    class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>"
                    placeholder="••••••••"
                    required
                >
                <?php if (isset($errors['current_password'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['current_password']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="new_password" class="form-label">
                    <i class="fas fa-key"></i> Nouveau mot de passe *
                </label>
                <input 
                    type="password" 
                    id="new_password" 
                    name="new_password" 
                    class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>"
                    placeholder="••••••••"
                    required
                >
                <?php if (isset($errors['new_password'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['new_password']) ?></div>
                <?php else: ?>
                    <small class="form-help">Au moins 6 caractères</small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="confirm_password" class="form-label">
                    <i class="fas fa-check-circle"></i> Confirmer le nouveau mot de passe *
                </label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                    placeholder="••••••••"
                    required
                >
                <?php if (isset($errors['confirm_password'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-danger btn-block">
                    <i class="fas fa-key"></i> Changer le mot de passe
                </button>
            </div>
        </form>

        <style>
            .password-form {
                max-width: 100%;
            }

            .password-form .form-group {
                margin-bottom: 1.5rem;
            }
            
            .form-label {
                display: block;
                font-weight: 600;
                color: #374151;
                margin-bottom: 0.5rem;
                font-size: 0.95rem;
            }
            
            .form-label i {
                color: #1e40af;
                margin-right: 0.5rem;
            }
            
            .form-control {
                width: 100%;
                padding: 0.75rem;
                border: 2px solid #e5e7eb;
                border-radius: 8px;
                font-size: 0.95rem;
                transition: border-color 0.2s;
            }
            
            .form-control:focus {
                outline: none;
                border-color: #1e40af;
            }
            
            .form-control.is-invalid {
                border-color: #dc2626;
            }
            
            .invalid-feedback {
                display: block;
                color: #dc2626;
                font-size: 0.875rem;
                margin-top: 0.25rem;
            }
            
            .form-help {
                display: block;
                margin-top: 0.5rem;
                color: #6b7280;
                font-size: 0.85rem;
            }

            .password-form .form-actions {
                margin-top: 2rem;
                padding-top: 2rem;
                border-top: 1px solid #e5e7eb;
            }
            
            .btn {
                padding: 0.75rem 1.5rem;
                border: none;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                justify-content: center;
            }
            
            .btn-danger {
                background: #dc2626;
                color: white;
            }
            
            .btn-danger:hover {
                background: #b91c1c;
            }
            
            .btn-block {
                width: 100%;
            }
        </style>
        <?php
        return ob_get_clean();
    }

    /**
     * Rendre la liste des documents (AJAX)
     */
 


public function renderDocuments($documents)
{
    if (empty($documents)) {
        echo '<div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <p>Aucun document</p>
            <small>Vous n\'avez pas encore uploadé de documents.</small>
        </div>';
        return;
    }

    echo '<div class="documents-list">';
    foreach ($documents as $doc) {
      
       $filePath = 'assets/uploads/documents/' . $doc['chemin_fichier'];
       
      
        ?>
        <div class="document-item">
            <div class="document-icon">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div class="document-info">
                <h4><?= htmlspecialchars($doc['nom_document']) ?></h4>
                <p>
                    <?= htmlspecialchars($doc['type_document']) ?> • 
                    <?= $this->formatFileSize($doc['taille_fichier']) ?> • 
                    <?= date('d/m/Y', strtotime($doc['date_upload'])) ?>
                </p>
            </div>
            <div class="document-actions">
               
                <a href="?page=profil&action=downloadDocument&doc_id=<?= $doc['id_document'] ?>" 
   class="btn-icon" 
   title="Télécharger">
    <i class="fas fa-download"></i>
</a>
                <a href="?page=profil&action=deleteDocument&doc_id=<?= $doc['id_document'] ?>" 
                   class="btn-icon btn-danger" 
                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce document ?')" 
                   title="Supprimer">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
        </div>
        <?php
    }
    echo '</div>';

    echo '<style>
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #9ca3af;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }
        .empty-state p {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
            color: #6b7280;
        }
        .empty-state small {
            font-size: 0.9rem;
        }
    </style>';
}
    /**
     * Formater la taille d'un fichier
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
?>