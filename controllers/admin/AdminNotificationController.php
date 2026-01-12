<?php
// Dans controllers/admin/AdminNotificationController.php

require_once __DIR__ . '/../../models/NotificationModel.php';
require_once __DIR__ . '/../../models/EvenementModel.php';

class AdminNotificationController
{
    public function sendReminders()
    {
        // Vérifier que c'est un admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: ?page=accueil');
            exit;
        }

        require_once __DIR__ . '/../../models/NotificationModel.php';
        $notificationModel = new NotificationModel();
        
        try {
            // Appeler la procédure stockée
            $notificationModel->sendEventReminders();
            
            $_SESSION['flash_message'] = 'Rappels envoyés avec succès !';
            $_SESSION['flash_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['flash_message'] = 'Erreur: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'error';
        }

        header('Location: ?page=admin&section=evenements');
        exit;
    }
}