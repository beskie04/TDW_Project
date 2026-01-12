<?php
require_once __DIR__ . '/BaseView.php';

// Import Components
require_once __DIR__ . '/components/Section.php';
require_once __DIR__ . '/components/FormGroup.php';
require_once __DIR__ . '/components/FormInput.php';
require_once __DIR__ . '/components/FormActions.php';
require_once __DIR__ . '/components/Card.php';

class ContactView extends BaseView
{
    public function __construct()
    { parent::__construct();
        $this->currentPage = 'contact';
        $this->pageTitle = 'Nous Contacter';
    }

    /**
     * Afficher le formulaire de contact
     */
    public function renderForm()
    {
        $this->renderHeader();
        $this->renderFlashMessage();

        // Récupérer les données du formulaire en cas d'erreur
        $formData = $_SESSION['form_data'] ?? [];
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['form_data'], $_SESSION['errors']);
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Page Header -->
                <div class="page-header">
                    <h1><i class="fas fa-envelope"></i> Contactez-nous</h1>
                    <p class="subtitle">Une question ? Un projet de collaboration ? N'hésitez pas à nous contacter</p>
                </div>

                <!-- Afficher les erreurs -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" style="margin-bottom: 2rem;">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <?php foreach ($errors as $error): ?>
                                <p style="margin: 0.25rem 0;">
                                    <?= htmlspecialchars($error) ?>
                                </p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: 1fr 400px; gap: 2rem; align-items: start;">
                    <!-- Formulaire de contact -->
                    <div>
                        <?php
                        Section::render([
                            'title' => 'Envoyez-nous un message',
                            'icon' => 'fas fa-paper-plane'
                        ], function () use ($formData) {
                            ?>
                            <div
                                style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                <form action="?page=contact&action=send" method="POST">
                                    <?php
                                    // Nom
                                    FormGroup::render([
                                        'label' => 'Nom complet',
                                        'required' => true
                                    ], function () use ($formData) {
                                        FormInput::render([
                                            'type' => 'text',
                                            'name' => 'nom',
                                            'placeholder' => 'Votre nom et prénom',
                                            'value' => $formData['nom'] ?? '',
                                            'required' => true
                                        ]);
                                    });

                                    // Email
                                    FormGroup::render([
                                        'label' => 'Adresse email',
                                        'required' => true
                                    ], function () use ($formData) {
                                        FormInput::render([
                                            'type' => 'email',
                                            'name' => 'email',
                                            'placeholder' => 'votre.email@exemple.com',
                                            'value' => $formData['email'] ?? '',
                                            'required' => true
                                        ]);
                                    });

                                    // Sujet
                                    FormGroup::render([
                                        'label' => 'Sujet',
                                        'required' => true
                                    ], function () use ($formData) {
                                        FormInput::render([
                                            'type' => 'text',
                                            'name' => 'sujet',
                                            'placeholder' => 'Objet de votre message',
                                            'value' => $formData['sujet'] ?? '',
                                            'required' => true
                                        ]);
                                    });

                                    // Message
                                    FormGroup::render([
                                        'label' => 'Message',
                                        'required' => true
                                    ], function () use ($formData) {
                                        ?>
                                        <textarea name="message" class="form-control" rows="8"
                                            placeholder="Décrivez votre demande, votre question ou votre projet..."
                                            required><?= htmlspecialchars($formData['message'] ?? '') ?></textarea>
                                        <?php
                                    });

                                    // Actions
                                    FormActions::render([
                                        'align' => 'space-between'
                                    ], function () {
                                        ?>
                                        <button type="reset" class="btn btn-outline">
                                            <i class="fas fa-redo"></i> Réinitialiser
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i> Envoyer le message
                                        </button>
                                        <?php
                                    });
                                    ?>
                                </form>
                            </div>
                            <?php
                        });
                        ?>
                    </div>

                    <!-- Informations de contact -->
                    <div>
                        <?php
                        Section::render([
                            'title' => 'Informations',
                            'icon' => 'fas fa-info-circle'
                        ], function () {
                            $contacts = [
                                [
                                    'icon' => 'fas fa-map-marker-alt',
                                    'title' => 'Adresse',
                                    'info' => 'École Supérieure d\'Informatique<br>Oued Smar, Alger, Algérie'
                                ],
                                [
                                    'icon' => 'fas fa-phone',
                                    'title' => 'Téléphone',
                                    'info' => '+213 XXX XXX XXX'
                                ],
                                [
                                    'icon' => 'fas fa-envelope',
                                    'title' => 'Email',
                                    'info' => 'contact@lab-esi.dz'
                                ],
                                [
                                    'icon' => 'fas fa-clock',
                                    'title' => 'Horaires',
                                    'info' => 'Dimanche - Jeudi<br>8h00 - 17h00'
                                ]
                            ];

                            foreach ($contacts as $contact) {
                                ?>
                                <div
                                    style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 1rem;">
                                    <div style="display: flex; gap: 1rem; align-items: start;">
                                        <div
                                            style="width: 48px; height: 48px; background: linear-gradient(135deg, var(--primary-color), var(--accent-color)); 
                                                    border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="<?= $contact['icon'] ?>" style="color: white; font-size: 1.25rem;"></i>
                                        </div>
                                        <div style="flex: 1;">
                                            <h4 style="margin: 0 0 0.5rem 0; color: var(--dark-color); font-size: 1rem;">
                                                <?= $contact['title'] ?>
                                            </h4>
                                            <p style="margin: 0; color: var(--gray-600); font-size: 0.9rem; line-height: 1.5;">
                                                <?= $contact['info'] ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        });

                        // Réseaux sociaux
                        Section::render([
                            'title' => 'Suivez-nous',
                            'icon' => 'fas fa-share-alt'
                        ], function () {
                            ?>
                            <div
                                style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                <div style="display: flex; gap: 1rem; justify-content: center;">
                                    <a href="#" class="social-link" style="width: 48px; height: 48px; background: #1877f2; border-radius: 12px; 
                                       display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; 
                                       transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'"
                                        onmouseout="this.style.transform='scale(1)'">
                                        <i class="fab fa-facebook-f" style="font-size: 1.25rem;"></i>
                                    </a>
                                    <a href="#" class="social-link" style="width: 48px; height: 48px; background: #1da1f2; border-radius: 12px; 
                                       display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; 
                                       transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'"
                                        onmouseout="this.style.transform='scale(1)'">
                                        <i class="fab fa-twitter" style="font-size: 1.25rem;"></i>
                                    </a>
                                    <a href="#" class="social-link" style="width: 48px; height: 48px; background: #0077b5; border-radius: 12px; 
                                       display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; 
                                       transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'"
                                        onmouseout="this.style.transform='scale(1)'">
                                        <i class="fab fa-linkedin-in" style="font-size: 1.25rem;"></i>
                                    </a>
                                    <a href="#" class="social-link" style="width: 48px; height: 48px; background: #c13584; border-radius: 12px; 
                                       display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; 
                                       transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'"
                                        onmouseout="this.style.transform='scale(1)'">
                                        <i class="fab fa-instagram" style="font-size: 1.25rem;"></i>
                                    </a>
                                </div>
                            </div>
                            <?php
                        });
                        ?>
                    </div>
                </div>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }
}
?>