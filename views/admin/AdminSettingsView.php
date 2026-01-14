<?php
require_once __DIR__ . '/../BaseView.php';

class AdminSettingsView extends BaseView
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Paramètres du Site';
    }

    public function render($settings)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container" style="max-width: 900px; margin: 2rem auto;">
                
                <!-- Header -->
                <div style="margin-bottom: 2rem;">
                    <h1><i class="fas fa-cog"></i> Paramètres du Site</h1>
                    <p style="color: #6b7280;">Gérer les informations et configurations du laboratoire</p>
                </div>

                <form method="POST" action="?page=admin&section=settings&action=update" style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    
                    <!-- Site Information -->
                    <div class="settings-section">
                        <h2><i class="fas fa-info-circle"></i> Informations du Site</h2>
                        
                        <div class="form-group">
                            <label for="site_title">Titre du Site *</label>
                            <input type="text" id="site_title" name="site_title" class="form-control" 
                                   value="<?= htmlspecialchars($settings['site_title'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="site_description">Description du Site</label>
                            <textarea id="site_description" name="site_description" class="form-control" rows="3"><?= htmlspecialchars($settings['site_description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="site_keywords">Mots-clés (séparés par des virgules)</label>
                            <input type="text" id="site_keywords" name="site_keywords" class="form-control" 
                                   value="<?= htmlspecialchars($settings['site_keywords'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="site_author">Auteur</label>
                            <input type="text" id="site_author" name="site_author" class="form-control" 
                                   value="<?= htmlspecialchars($settings['site_author'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="site_url">URL du Site</label>
                            <input type="url" id="site_url" name="site_url" class="form-control" 
                                   value="<?= htmlspecialchars($settings['site_url'] ?? '') ?>" 
                                   placeholder="https://lab-esi.dz">
                        </div>

                        <div class="form-group">
                            <label for="theme_color">Couleur du Thème</label>
                            <input type="color" id="theme_color" name="theme_color" class="form-control" 
                                   value="<?= htmlspecialchars($settings['theme_color'] ?? '#3b82f6') ?>">
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="settings-section">
                        <h2><i class="fas fa-address-card"></i> Informations de Contact</h2>
                        
                        <div class="form-group">
                            <label for="contact_adresse">Adresse</label>
                            <input type="text" id="contact_adresse" name="contact_adresse" class="form-control" 
                                   value="<?= htmlspecialchars($settings['contact_adresse'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="contact_telephone">Téléphone</label>
                            <input type="text" id="contact_telephone" name="contact_telephone" class="form-control" 
                                   value="<?= htmlspecialchars($settings['contact_telephone'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="contact_email">Email</label>
                            <input type="email" id="contact_email" name="contact_email" class="form-control" 
                                   value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="contact_fax">Fax</label>
                            <input type="text" id="contact_fax" name="contact_fax" class="form-control" 
                                   value="<?= htmlspecialchars($settings['contact_fax'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="horaires_ouverture">Horaires d'Ouverture</label>
                            <input type="text" id="horaires_ouverture" name="horaires_ouverture" class="form-control" 
                                   value="<?= htmlspecialchars($settings['horaires_ouverture'] ?? '') ?>" 
                                   placeholder="Lun-Ven: 8h-17h">
                        </div>
                    </div>

                    <!-- Social Networks -->
                    <div class="settings-section">
                        <h2><i class="fas fa-share-alt"></i> Réseaux Sociaux</h2>
                        
                        <div class="form-group">
                            <label for="reseaux_facebook">Facebook</label>
                            <input type="url" id="reseaux_facebook" name="reseaux_facebook" class="form-control" 
                                   value="<?= htmlspecialchars($settings['reseaux_facebook'] ?? '') ?>" 
                                   placeholder="https://facebook.com/labo">
                        </div>

                        <div class="form-group">
                            <label for="reseaux_twitter">Twitter</label>
                            <input type="url" id="reseaux_twitter" name="reseaux_twitter" class="form-control" 
                                   value="<?= htmlspecialchars($settings['reseaux_twitter'] ?? '') ?>" 
                                   placeholder="https://twitter.com/labo">
                        </div>

                        <div class="form-group">
                            <label for="reseaux_linkedin">LinkedIn</label>
                            <input type="url" id="reseaux_linkedin" name="reseaux_linkedin" class="form-control" 
                                   value="<?= htmlspecialchars($settings['reseaux_linkedin'] ?? '') ?>" 
                                   placeholder="https://linkedin.com/company/labo">
                        </div>
                    </div>

                    <!-- Open Graph -->
                    <div class="settings-section">
                        <h2><i class="fas fa-image"></i> Partage sur les Réseaux Sociaux (Open Graph)</h2>
                        
                        <div class="form-group">
                            <label for="og_title">Titre OG</label>
                            <input type="text" id="og_title" name="og_title" class="form-control" 
                                   value="<?= htmlspecialchars($settings['og_title'] ?? '') ?>">
                            <small>Titre affiché lors du partage sur les réseaux sociaux</small>
                        </div>

                        <div class="form-group">
                            <label for="og_description">Description OG</label>
                            <textarea id="og_description" name="og_description" class="form-control" rows="2"><?= htmlspecialchars($settings['og_description'] ?? '') ?></textarea>
                            <small>Description affichée lors du partage</small>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e5e7eb;">
                        <a href="?page=admin" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </main>

        <style>
            .settings-section {
                margin-bottom: 2.5rem;
                padding-bottom: 2rem;
                border-bottom: 2px solid #f3f4f6;
            }

            .settings-section:last-of-type {
                border-bottom: none;
            }

            .settings-section h2 {
                color: #1f2937;
                font-size: 1.3rem;
                margin-bottom: 1.5rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .settings-section h2 i {
                color: #3b82f6;
            }

            .form-group {
                margin-bottom: 1.5rem;
            }

            .form-group label {
                display: block;
                font-weight: 600;
                color: #374151;
                margin-bottom: 0.5rem;
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
                border-color: #3b82f6;
            }

            .form-group small {
                display: block;
                color: #6b7280;
                font-size: 0.85rem;
                margin-top: 0.25rem;
            }

            input[type="color"].form-control {
                height: 50px;
                cursor: pointer;
            }

            .btn {
                padding: 0.75rem 1.5rem;
                border: none;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                transition: all 0.2s;
            }

            .btn-primary {
                background: #3b82f6;
                color: white;
            }

            .btn-primary:hover {
                background: #2563eb;
            }

            .btn-secondary {
                background: #6b7280;
                color: white;
            }

            .btn-secondary:hover {
                background: #4b5563;
            }
        </style>

        <?php
        $this->renderFooter();
    }
}