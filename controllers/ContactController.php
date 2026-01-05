<?php
require_once __DIR__ . '/../models/ContactModel.php';
require_once __DIR__ . '/../views/ContactView.php';

class ContactController
{
    private $model;
    private $view;

    public function __construct()
    {
        $this->model = new ContactModel();
        $this->view = new ContactView();
    }

    /**
     * Afficher le formulaire de contact
     */
    public function index()
    {
        $this->view->renderForm();
    }

    /**
     * Traiter l'envoi du formulaire
     */
    public function send()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=contact');
            exit;
        }

        // Validation
        $errors = [];

        if (empty($_POST['nom'])) {
            $errors[] = 'Le nom est requis';
        }

        if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email invalide';
        }

        if (empty($_POST['sujet'])) {
            $errors[] = 'Le sujet est requis';
        }

        if (empty($_POST['message'])) {
            $errors[] = 'Le message est requis';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: ?page=contact');
            exit;
        }

        // Enregistrer le message
        $messageId = $this->model->createMessage([
            'nom' => $_POST['nom'],
            'email' => $_POST['email'],
            'sujet' => $_POST['sujet'],
            'message' => $_POST['message']
        ]);

        if ($messageId) {
            require_once __DIR__ . '/../views/BaseView.php';
            BaseView::setFlash('Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.', 'success');

            // Nettoyer les données du formulaire
            unset($_SESSION['form_data']);
        } else {
            BaseView::setFlash('Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer.', 'error');
        }

        header('Location: ?page=contact');
        exit;
    }
}
?>