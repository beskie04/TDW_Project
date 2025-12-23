<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../views/BaseView.php';

class LoginController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Afficher le formulaire de connexion
     */
    public function index()
    {
        // Si déjà connecté, rediriger
        if (isset($_SESSION['user'])) {
            header('Location: ?page=accueil');
            exit;
        }

        $this->renderLoginForm();
    }

    /**
     * Traiter la connexion
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=login');
            exit;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validation simple
        if (empty($email) || empty($password)) {
            BaseView::setFlash('Veuillez remplir tous les champs', 'error');
            header('Location: ?page=login');
            exit;
        }

        // Connexion spéciale admin/user sans BD
        if ($email === 'admin' && $password === 'admin') {
            $_SESSION['user'] = [
                'id_membre' => 1,
                'nom' => 'Admin',
                'prenom' => 'Système',
                'email' => 'admin@esi.dz',
                'role' => 'admin'
            ];
            BaseView::setFlash('Bienvenue Admin !', 'success');
            header('Location: ?page=admin&section=projets');
            exit;
        }

        if ($email === 'user' && $password === 'user') {
            $_SESSION['user'] = [
                'id_membre' => 2,
                'nom' => 'Utilisateur',
                'prenom' => 'Test',
                'email' => 'user@esi.dz',
                'role' => 'membre'
            ];
            BaseView::setFlash('Bienvenue !', 'success');
            header('Location: ?page=accueil');
            exit;
        }

        // Rechercher l'utilisateur dans la BD
        $sql = "SELECT * FROM membres WHERE email = :email AND actif = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            BaseView::setFlash('Email ou mot de passe incorrect', 'error');
            header('Location: ?page=login');
            exit;
        }

        // Vérifier le mot de passe
        if ($user['mot_de_passe'] && password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['user'] = [
                'id_membre' => $user['id_membre'],
                'nom' => $user['nom'],
                'prenom' => $user['prenom'],
                'email' => $user['email'],
                'role' => $user['role']
            ];

            BaseView::setFlash('Connexion réussie !', 'success');

            if ($user['role'] === 'admin') {
                header('Location: ?page=admin&section=projets');
            } else {
                header('Location: ?page=accueil');
            }
            exit;
        }

        BaseView::setFlash('Email ou mot de passe incorrect', 'error');
        header('Location: ?page=login');
        exit;
    }

    /**
     * Afficher le formulaire de connexion
     */
    private function renderLoginForm()
    {
        ?>
        <!DOCTYPE html>
        <html lang="fr">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Connexion - Laboratoire</title>
            <link rel="stylesheet" href="<?= ASSETS_URL ?>css/style.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <style>
                .login-wrapper {
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
                    padding: 2rem;
                }

                .login-container {
                    background: var(--white);
                    padding: 3rem;
                    border-radius: var(--border-radius);
                    box-shadow: var(--box-shadow-lg);
                    max-width: 450px;
                    width: 100%;
                }

                .login-header {
                    text-align: center;
                    margin-bottom: 2rem;
                }

                .login-header h1 {
                    font-size: 2rem;
                    color: var(--dark-color);
                    margin-bottom: 0.5rem;
                }

                .login-header p {
                    color: var(--gray-600);
                }

                .login-form .form-group {
                    margin-bottom: 1.5rem;
                }

                .login-form label {
                    display: block;
                    font-weight: 500;
                    color: var(--gray-700);
                    margin-bottom: 0.5rem;
                }

                .login-form input {
                    width: 100%;
                    padding: 0.75rem;
                    border: 1px solid var(--gray-300);
                    border-radius: var(--border-radius);
                    font-size: 1rem;
                }

                .login-form input:focus {
                    outline: none;
                    border-color: var(--primary-color);
                    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
                }

                .login-btn {
                    width: 100%;
                    padding: 1rem;
                    background: var(--primary-color);
                    color: var(--white);
                    border: none;
                    border-radius: var(--border-radius);
                    font-size: 1rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: var(--transition);
                }

                .login-btn:hover {
                    background: var(--secondary-color);
                }

                .login-info {
                    margin-top: 2rem;
                    padding: 1rem;
                    background: var(--light-color);
                    border-radius: var(--border-radius);
                    font-size: 0.9rem;
                }

                .login-info strong {
                    display: block;
                    margin-bottom: 0.5rem;
                    color: var(--dark-color);
                }

                .back-link {
                    text-align: center;
                    margin-top: 1.5rem;
                }

                .back-link a {
                    color: var(--primary-color);
                    text-decoration: none;
                }

                .back-link a:hover {
                    text-decoration: underline;
                }
            </style>
        </head>

        <body>
            <div class="login-wrapper">
                <div class="login-container">
                    <?php if (isset($_SESSION['flash_message'])): ?>
                        <div class="flash-message flash-<?= $_SESSION['flash_type'] ?? 'info' ?>">
                            <?= htmlspecialchars($_SESSION['flash_message']) ?>
                        </div>
                        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                    <?php endif; ?>

                    <div class="login-header">
                        <h1><i class="fas fa-flask"></i> Connexion</h1>
                        <p>Laboratoire Universitaire</p>
                    </div>

                    <form method="POST" action="?page=login&action=authenticate" class="login-form">
                        <div class="form-group">
                            <label for="email">Email / Identifiant</label>
                            <input type="text" id="email" name="email" required autofocus>
                        </div>

                        <div class="form-group">
                            <label for="password">Mot de passe</label>
                            <input type="password" id="password" name="password" required>
                        </div>

                        <button type="submit" class="login-btn">
                            <i class="fas fa-sign-in-alt"></i> Se connecter
                        </button>
                    </form>

                    <div class="login-info">
                        <strong>Comptes de test :</strong>
                        Admin : <code>admin / admin</code><br>
                        User : <code>user / user</code>
                    </div>

                    <div class="back-link">
                        <a href="?page=accueil"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>
                    </div>
                </div>
            </div>

            <script src="<?= ASSETS_URL ?>js/main.js"></script>
        </body>

        </html>
        <?php
    }
}
?>