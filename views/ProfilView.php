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
                        Avatar::render([
                            'src' => $profile['photo'] ? ASSETS_URL . 'uploads/photos/' . $profile['photo'] : null,
                            'name' => $profile['prenom'] . ' ' . $profile['nom'],
                            'size' => 'xl'
                        ]);
                        ?>
                        <div class="profile-info">
                            <h1>
                                <?= htmlspecialchars($profile['prenom'] . ' ' . $profile['nom']) ?>
                            </h1>
                            <p class="profile-subtitle">
                                <?= htmlspecialchars($profile['poste']) ?> •
                                <?= htmlspecialchars($profile['grade']) ?>
                            </p>
                            <div class="profile-badges">
                                <?php
                                Badge::render([
                                    'text' => $profile['role'],
                                    'variant' => 'secondary'
                                ]);

                                if ($profile['role_systeme'] === 'admin') {
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

            <!-- Statistiques -->
            <div class="stats-grid">
                <?php
                StatCard::render([
                    'title' => 'Projets',
                    'value' => $profile['stats']['projets'],
                    'icon' => 'fa-project-diagram',
                    'color' => 'blue',
                    'link' => '#projets-section'
                ]);

                StatCard::render([
                    'title' => 'Publications',
                    'value' => $profile['stats']['publications'],
                    'icon' => 'fa-file-alt',
                    'color' => 'green',
                    'link' => '#publications-section'
                ]);

                StatCard::render([
                    'title' => 'Réservations',
                    'value' => $profile['stats']['reservations'],
                    'icon' => 'fa-calendar-check',
                    'color' => 'orange',
                    'link' => '#reservations-section'
                ]);

                StatCard::render([
                    'title' => 'Équipes',
                    'value' => $profile['stats']['equipes'],
                    'icon' => 'fa-users',
                    'color' => 'purple',
                    'link' => '#equipes-section'
                ]);
                ?>
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
                    <span><i class="fas fa-tag"></i>
                        <?= htmlspecialchars($projet['nom_thematique']) ?>
                    </span>
                    <span><i class="fas fa-user"></i>
                        <?= htmlspecialchars($projet['role_projet']) ?>
                    </span>
                    <span><i class="fas fa-calendar"></i>
                        <?= date('Y', strtotime($projet['date_debut'])) ?>
                    </span>
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

        // Map publication types to icons
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
                    <h4>
                        <?= htmlspecialchars($pub['titre']) ?>
                    </h4>
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
                    <?php if (!empty($pub['fichier'])): ?>
                        <a href="<?= ASSETS_URL ?>uploads/publications/<?= htmlspecialchars($pub['fichier']) ?>" target="_blank"
                            class="publication-link">
                            <i class="fas fa-download"></i> Télécharger
                        </a>
                    <?php elseif (!empty($pub['doi'])): ?>
                        <a href="https://doi.org/<?= htmlspecialchars($pub['doi']) ?>" target="_blank" class="publication-link">
                            <i class="fas fa-external-link-alt"></i> DOI
                        </a>
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
                    <h4>
                        <?= htmlspecialchars($res['equipement_nom']) ?>
                    </h4>
                    <?php
                    Badge::render([
                        'text' => $statusText,
                        'variant' => $statusClass
                    ]);
                    ?>
                </div>
                <div class="reservation-details">
                    <p><i class="fas fa-info-circle"></i>
                        <?= htmlspecialchars($res['type']) ?>
                    </p>
                    <p><i class="fas fa-door-open"></i>
                        <?= htmlspecialchars($res['salle']) ?>
                    </p>
                    <p><i class="fas fa-calendar"></i> Du
                        <?= date('d/m/Y H:i', strtotime($res['date_debut'])) ?>
                    </p>
                    <p><i class="fas fa-calendar"></i> Au
                        <?= date('d/m/Y H:i', strtotime($res['date_fin'])) ?>
                    </p>
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
                <h3>
                    <h3>
                        <?= htmlspecialchars($equipe['nom']) ?>
                    </h3>
                </h3>
                <p>
                    <?= htmlspecialchars(substr($equipe['description'], 0, 100)) ?>...
                </p>
                <div class="equipe-meta">
                    <div class="equipe-chef">
                        <?php
                        Avatar::render([
                            'src' => $equipe['chef_photo'] ? ASSETS_URL . 'uploads/photos/' . $equipe['chef_photo'] : null,
                            'name' => $equipe['chef_prenom'] . ' ' . $equipe['chef_nom'],
                            'size' => 'sm'
                        ]);
                        ?>
                        <span>Chef:
                            <?= htmlspecialchars($equipe['chef_prenom'] . ' ' . $equipe['chef_nom']) ?>
                        </span>
                    </div>
                    <span><i class="fas fa-users"></i>
                        <?= $equipe['nb_membres'] ?> membres
                    </span>
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
            <div class="documents-upload">
                <h3>Uploader un document</h3>
                <form method="POST" action="?page=profil&action=uploadDocument" enctype="multipart/form-data">
                    <?php
                    FormInput::render([
                        'label' => 'Nom du document',
                        'name' => 'nom_document',
                        'type' => 'text',
                        'required' => true
                    ]);

                    FormInput::render([
                        'label' => 'Type de document',
                        'name' => 'type_document',
                        'type' => 'select',
                        'options' => [
                            'CV' => 'CV',
                            'Diplôme' => 'Diplôme',
                            'Article' => 'Article',
                            'Rapport' => 'Rapport',
                            'Autre' => 'Autre'
                        ],
                        'required' => true
                    ]);

                    FormInput::render([
                        'label' => 'Fichier (PDF, Word)',
                        'name' => 'document',
                        'type' => 'file',
                        'accept' => '.pdf,.doc,.docx',
                        'required' => true
                    ]);

                    Button::render([
                        'text' => 'Uploader',
                        'type' => 'submit',
                        'variant' => 'primary',
                        'icon' => 'fa-upload'
                    ]);
                    ?>
                </form>
            </div>

            <div id="documents-list-container">
                <p class="loading-text"><i class="fas fa-spinner fa-spin"></i> Chargement des documents...</p>
            </div>
        </div>

        <script>
            // Charger les documents via AJAX
            fetch('?page=profil&action=getDocuments')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('documents-list-container').innerHTML = data.html;
                })
                .catch(err => {
                    document.getElementById('documents-list-container').innerHTML =
                        '<p class="error-text">Erreur lors du chargement des documents</p>';
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
                <div class="profile-photo-section">
                    <div class="card">
                        <div class="card-header">
                            <h3>Photo de profil</h3>
                        </div>
                        <div class="card-body">
                            <?= $this->getPhotoUploadForm($profile) ?>
                        </div>
                    </div>
                </div>

                <!-- Informations de base -->
                <div class="profile-info-section">
                    <div class="card">
                        <div class="card-header">
                            <h3>Informations personnelles</h3>
                        </div>
                        <div class="card-body">
                            <?= $this->getInfoForm($profile, $errors) ?>
                        </div>
                    </div>
                </div>

                <!-- Changement de mot de passe -->
                <div class="profile-password-section">
                    <div class="card">
                        <div class="card-header">
                            <h3>Changer le mot de passe</h3>
                        </div>
                        <div class="card-body">
                            <?= $this->getPasswordForm() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                Avatar::render([
                    'src' => $profile['photo'] ? ASSETS_URL . 'uploads/photos/' . $profile['photo'] : null,
                    'name' => $profile['prenom'] . ' ' . $profile['nom'],
                    'size' => 'xxl'
                ]);
                ?>
            </div>
            <form method="POST" action="?page=profil&action=updatePhoto" enctype="multipart/form-data">
                <?php
                FormInput::render([
                    'label' => 'Changer la photo',
                    'name' => 'photo',
                    'type' => 'file',
                    'accept' => 'image/*',
                    'required' => true
                ]);

                Button::render([
                    'text' => 'Mettre à jour',
                    'type' => 'submit',
                    'variant' => 'primary',
                    'icon' => 'fa-upload',
                    'fullWidth' => true
                ]);
                ?>
            </form>
        </div>
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
        <form method="POST" action="?page=profil&action=update">
            <?php
            FormInput::render([
                'label' => 'Poste',
                'name' => 'poste',
                'type' => 'text',
                'value' => $profile['poste'],
                'required' => true,
                'error' => $errors['poste'] ?? null
            ]);

            FormInput::render([
                'label' => 'Grade',
                'name' => 'grade',
                'type' => 'text',
                'value' => $profile['grade'],
                'required' => true,
                'error' => $errors['grade'] ?? null
            ]);

            FormInput::render([
                'label' => 'Domaine de recherche',
                'name' => 'domaine_recherche',
                'type' => 'textarea',
                'value' => $profile['domaine_recherche'],
                'rows' => 3
            ]);

            FormInput::render([
                'label' => 'Biographie',
                'name' => 'biographie',
                'type' => 'textarea',
                'value' => $profile['biographie'],
                'rows' => 5
            ]);

            Button::render([
                'text' => 'Enregistrer',
                'type' => 'submit',
                'variant' => 'primary',
                'icon' => 'fa-save',
                'fullWidth' => true
            ]);
            ?>
        </form>
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
        <form method="POST" action="?page=profil&action=changePassword">
            <?php
            FormInput::render([
                'label' => 'Mot de passe actuel',
                'name' => 'current_password',
                'type' => 'password',
                'required' => true,
                'error' => $errors['current_password'] ?? null
            ]);

            FormInput::render([
                'label' => 'Nouveau mot de passe',
                'name' => 'new_password',
                'type' => 'password',
                'required' => true,
                'error' => $errors['new_password'] ?? null
            ]);

            FormInput::render([
                'label' => 'Confirmer le mot de passe',
                'name' => 'confirm_password',
                'type' => 'password',
                'required' => true,
                'error' => $errors['confirm_password'] ?? null
            ]);

            Button::render([
                'text' => 'Changer le mot de passe',
                'type' => 'submit',
                'variant' => 'danger',
                'icon' => 'fa-key',
                'fullWidth' => true
            ]);
            ?>
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * Rendre la liste des documents (AJAX)
     */
    public function renderDocuments($documents)
    {
        if (empty($documents)) {
            EmptyState::render([
                'icon' => 'fa-folder-open',
                'title' => 'Aucun document',
                'message' => 'Vous n\'avez pas encore uploadé de documents.'
            ]);
            return;
        }

        echo '<div class="documents-list">';
        foreach ($documents as $doc) {
            ?>
            <div class="document-item">
                <div class="document-icon">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <div class="document-info">
                    <h4>
                        <?= htmlspecialchars($doc['nom_document']) ?>
                    </h4>
                    <p>
                        <?= htmlspecialchars($doc['type_document']) ?> •
                        <?= $this->formatFileSize($doc['taille_fichier']) ?> •
                        <?= date('d/m/Y', strtotime($doc['date_upload'])) ?>
                    </p>
                </div>
                <div class="document-actions">
                    <a href="<?= ASSETS_URL ?>uploads/documents/<?= $doc['chemin_fichier'] ?>" target="_blank" class="btn-icon"
                        title="Télécharger">
                        <i class="fas fa-download"></i>
                    </a>
                    <a href="?page=profil&action=deleteDocument&doc_id=<?= $doc['id_document'] ?>" class="btn-icon btn-danger"
                        onclick="return confirm('Supprimer ce document ?')" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
            </div>
            <?php
        }
        echo '</div>';
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