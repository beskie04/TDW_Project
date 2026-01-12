<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../views/LoginView.php';

class LoginController
{
    private $model;
    private $view;

    public function __construct()
    {
        $this->model = new UserModel();
        $this->view = new LoginView();
    }

    /**
     * Afficher le formulaire de connexion
     */
    public function index()
    {
        // Rediriger si déjà connecté
        if (isset($_SESSION['user'])) {
            $this->redirectToDashboard();
            return;
        }

        $this->view->render();
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

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        // Validation
        $errors = $this->validateLogin($username, $password);

        if (!empty($errors)) {
            $this->view->render($errors, $username);
            return;
        }

        // Authentification
        $user = $this->model->authenticate($username, $password);

        if (!$user) {
            $this->view->render(['general' => 'Nom d\'utilisateur ou mot de passe incorrect'], $username);
            return;
        }

        // Créer la session
        $this->createSession($user, $remember);

        // Rediriger selon le rôle
        $this->redirectToDashboard();
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        // Détruire la session
        session_destroy();

        // Supprimer les cookies de remember me
        if (isset($_COOKIE['remember_user'])) {
            setcookie('remember_user', '', time() - 3600, '/');
        }

        BaseView::setFlash('Vous avez été déconnecté avec succès', 'success');
        header('Location: ?page=login');
        exit;
    }

    /**
     * Valider les données de connexion
     */
    private function validateLogin($username, $password)
    {
        $errors = [];

        if (empty($username)) {
            $errors['username'] = 'Le nom d\'utilisateur est requis';
        } elseif (strlen($username) < 3) {
            $errors['username'] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères';
        }

        if (empty($password)) {
            $errors['password'] = 'Le mot de passe est requis';
        }

        return $errors;
    }

    /**
     * Créer la session utilisateur
     */
    private function createSession($user, $remember = false)
    {
        // Régénérer l'ID de session pour la sécurité
        session_regenerate_id(true);

        // Stocker les informations utilisateur
        $_SESSION['user'] = [
            'id' => $user['id_membre'],
            'id_membre' => $user['id_membre'],
            'username' => $user['username'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'email' => $user['email'],
            'role' => $user['role_systeme'],
            'type_membre' => $user['role'],
            'photo' => $user['photo'],
            'poste' => $user['poste'],
            'role_systeme' => $user['role_systeme'] ?? $user['role']
        ];
        
        // Debug: Verify the session
        error_log("Session created for user: " . print_r($_SESSION['user'], true));
        
        // Cookie "Remember Me"
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_user', $token, time() + (86400 * 30), '/', '', false, true);
            // TODO: Stocker le token en BDD pour plus de sécurité
        }

        BaseView::setFlash('Connexion réussie! Bienvenue ' . $user['prenom'], 'success');
    }

    /**
     * Rediriger vers le tableau de bord approprié
     */
    private function redirectToDashboard()
    {
        if ($_SESSION['user']['role'] === 'admin') {
            header('Location: ?page=admin');
        } else {
            header('Location: ?page=profil');
        }
        exit;
    }

    /**
     * Vérifier si l'utilisateur est connecté
     */
    public static function requireLogin()
    {
        if (!isset($_SESSION['user'])) {
            BaseView::setFlash('Vous devez être connecté pour accéder à cette page', 'error');
            header('Location: ?page=login');
            exit;
        }
    }

    /**
     * Vérifier si l'utilisateur est admin
     */
    public static function requireAdmin()
    {
        self::requireLogin();

        if ($_SESSION['user']['role'] !== 'admin') {
            BaseView::setFlash('Accès refusé. Vous n\'avez pas les permissions nécessaires', 'error');
            header('Location: ?page=profil');
            exit;
        }
    }
}
?>