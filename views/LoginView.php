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
    public function render($errors = [], $username = '')
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
                            'label' => 'Nom d\'utilisateur',
                            'name' => 'username',
                            'type' => 'text',
                            'value' => $username,
                            'placeholder' => 'admin',
                            'icon' => 'fa-user',
                            'required' => true,
                            'error' => $errors['username'] ?? null
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
                background: #f7fafc;
                padding: 2rem;
            }

            .login-wrapper {
                display: grid;
                grid-template-columns: 1fr 1fr;
                max-width: 900px;
                width: 100%;
                background: white;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
                border: 1px solid #e2e8f0;
            }

            .login-card {
                padding: 3rem;
            }

            .login-header {
                text-align: center;
                margin-bottom: 2rem;
            }

            .login-icon {
                width: 64px;
                height: 64px;
                background: #4169E1;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1.5rem;
            }

            .login-icon i {
                font-size: 1.75rem;
                color: white;
            }

            .login-header h1 {
                font-size: 1.75rem;
                color: #1a202c;
                margin-bottom: 0.5rem;
                font-weight: 600;
            }

            .login-header p {
                color: #718096;
                font-size: 0.95rem;
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
                font-size: 0.9rem;
            }

            .checkbox-label input[type="checkbox"] {
                width: 16px;
                height: 16px;
                cursor: pointer;
            }

            .login-footer {
                text-align: center;
                margin-top: 1.5rem;
                padding-top: 1.5rem;
                border-top: 1px solid #e2e8f0;
            }

            .login-footer a {
                color: #4169E1;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.9rem;
            }

            .login-footer a:hover {
                text-decoration: underline;
            }

            .login-info {
                background: #4169E1;
                padding: 3rem;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
            }

            .info-content {
                text-align: center;
            }

            .info-content h2 {
                font-size: 1.75rem;
                margin-bottom: 1rem;
                font-weight: 600;
            }

            .info-content p {
                opacity: 0.95;
                line-height: 1.7;
                font-size: 1rem;
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