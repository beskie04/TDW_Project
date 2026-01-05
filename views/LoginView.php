<?php
require_once __DIR__ . '/BaseView.php';
require_once __DIR__ . '/components/Alert.php';
require_once __DIR__ . '/components/FormInput.php';
require_once __DIR__ . '/components/Button.php';

class LoginView extends BaseView
{
    protected $pageTitle = 'Connexion - Laboratoire Universitaire';
    protected $currentPage = 'login';

    /**
     * Rendre la page de connexion
     */
    public function render($errors = [], $email = '')
    {
        $this->renderHeader();
        ?>

        <div class="login-container">
            <div class="login-wrapper">
                <div class="login-card">
                    <div class="login-header">
                        <div class="login-icon">
                            <i class="fas fa-flask"></i>
                        </div>
                        <h1>Connexion</h1>
                        <p>Accédez à votre espace membre</p>
                    </div>

                    <?php
                    $this->renderFlashMessage();

                    if (isset($errors['general'])):
                        Alert::render([
                            'variant' => 'danger',
                            'icon' => 'fas fa-exclamation-circle',
                            'dismissible' => true
                        ], function () use ($errors) {
                            echo htmlspecialchars($errors['general']);
                        });
                    endif;
                    ?>

                    <form method="POST" action="?page=login&action=login" class="login-form">
                        <?php
                        FormInput::render([
                            'label' => 'Adresse email',
                            'name' => 'email',
                            'type' => 'email',
                            'value' => $email,
                            'placeholder' => 'votre.email@example.com',
                            'icon' => 'fa-envelope',
                            'required' => true,
                            'error' => $errors['email'] ?? null
                        ]);

                        FormInput::render([
                            'label' => 'Mot de passe',
                            'name' => 'password',
                            'type' => 'password',
                            'placeholder' => '••••••••',
                            'icon' => 'fa-lock',
                            'required' => true,
                            'error' => $errors['password'] ?? null
                        ]);
                        ?>

                        <div class="form-options">
                            <label class="checkbox-label">
                                <input type="checkbox" name="remember">
                                <span>Se souvenir de moi</span>
                            </label>
                        </div>

                        <?php
                        Button::render([
                            'text' => 'Se connecter',
                            'type' => 'submit',
                            'variant' => 'primary',
                            'fullWidth' => true,
                            'icon' => 'fa-sign-in-alt'
                        ]);
                        ?>
                    </form>

                    <div class="login-footer">
                        <p><a href="?page=accueil"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a></p>
                    </div>
                </div>

                <div class="login-info">
                    <div class="info-content">
                        <h2>Bienvenue au Laboratoire</h2>
                        <p>Connectez-vous pour accéder à votre espace personnel et gérer vos projets, publications et
                            réservations.</p>

                        <div class="info-features">
                            <div class="feature-item">
                                <i class="fas fa-project-diagram"></i>
                                <h3>Gestion des projets</h3>
                                <p>Suivez vos projets de recherche</p>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-file-alt"></i>
                                <h3>Publications</h3>
                                <p>Gérez vos publications scientifiques</p>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-desktop"></i>
                                <h3>Réservations</h3>
                                <p>Réservez les équipements du laboratoire</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .login-container {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 2rem;
            }

            .login-wrapper {
                display: grid;
                grid-template-columns: 1fr 1fr;
                max-width: 1000px;
                width: 100%;
                background: white;
                border-radius: 20px;
                overflow: hidden;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            }

            .login-card {
                padding: 3rem;
            }

            .login-header {
                text-align: center;
                margin-bottom: 2rem;
            }

            .login-icon {
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1rem;
            }

            .login-icon i {
                font-size: 2rem;
                color: white;
            }

            .login-header h1 {
                font-size: 2rem;
                color: #2d3748;
                margin-bottom: 0.5rem;
            }

            .login-header p {
                color: #718096;
            }

            .login-form {
                margin-top: 2rem;
            }

            .form-options {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin: 1rem 0;
            }

            .checkbox-label {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                cursor: pointer;
                color: #4a5568;
            }

            .checkbox-label input[type="checkbox"] {
                width: 18px;
                height: 18px;
                cursor: pointer;
            }

            .login-footer {
                text-align: center;
                margin-top: 2rem;
                padding-top: 2rem;
                border-top: 1px solid #e2e8f0;
            }

            .login-footer a {
                color: #667eea;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }

            .login-footer a:hover {
                text-decoration: underline;
            }

            .login-info {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 3rem;
                display: flex;
                align-items: center;
                color: white;
            }

            .info-content h2 {
                font-size: 2rem;
                margin-bottom: 1rem;
            }

            .info-content p {
                opacity: 0.9;
                line-height: 1.6;
                margin-bottom: 2rem;
            }

            .info-features {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }

            .feature-item {
                display: flex;
                gap: 1rem;
                align-items: flex-start;
            }

            .feature-item i {
                font-size: 1.5rem;
                opacity: 0.9;
                margin-top: 0.25rem;
            }

            .feature-item h3 {
                font-size: 1.1rem;
                margin-bottom: 0.25rem;
            }

            .feature-item p {
                opacity: 0.8;
                font-size: 0.9rem;
                margin: 0;
            }

            @media (max-width: 768px) {
                .login-wrapper {
                    grid-template-columns: 1fr;
                }

                .login-info {
                    display: none;
                }

                .login-card {
                    padding: 2rem;
                }
            }
        </style>

        <?php
        $this->renderFooter();
    }
}
?>