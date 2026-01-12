<?php
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../views/NotificationView.php';

class NotificationController
{
    private $model;
    private $view;

    public function __construct()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header('Location: ?page=login');
            exit;
        }

        $this->model = new NotificationModel();
        $this->view = new NotificationView();
    }

    /**
     * Display notifications page
     */
    public function index()
    {
        $membreId = $_SESSION['user']['id_membre'];
        $notifications = $this->model->getByMembre($membreId, 100);
        
        $this->view->renderIndex($notifications);
    }

    /**
     * Get unread count (AJAX)
     */
    public function getUnreadCount()
    {
        header('Content-Type: application/json');
        
        $membreId = $_SESSION['user']['id_membre'];
        $count = $this->model->getUnreadCount($membreId);
        
        echo json_encode(['count' => $count]);
        exit;
    }

    /**
     * Get recent notifications (AJAX)
     */
    public function getRecent()
    {
        header('Content-Type: application/json');
        
        $membreId = $_SESSION['user']['id_membre'];
        $limit = $_GET['limit'] ?? 10;
        
        $notifications = $this->model->getRecent($membreId, $limit);
        
        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $this->model->getUnreadCount($membreId)
        ]);
        exit;
    }

    /**
     * Mark notification as read (AJAX)
     */
    public function markAsRead()
    {
        header('Content-Type: application/json');
        
        $notificationId = $_POST['id'] ?? null;
        $membreId = $_SESSION['user']['id_membre'];
        
        if (!$notificationId) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            exit;
        }
        
        $result = $this->model->markAsRead($notificationId, $membreId);
        
        echo json_encode([
            'success' => $result,
            'unread_count' => $this->model->getUnreadCount($membreId)
        ]);
        exit;
    }

    /**
     * Mark all as read (AJAX)
     */
    public function markAllAsRead()
    {
        header('Content-Type: application/json');
        
        $membreId = $_SESSION['user']['id_membre'];
        $result = $this->model->markAllAsRead($membreId);
        
        echo json_encode([
            'success' => $result,
            'unread_count' => 0
        ]);
        exit;
    }

    /**
     * Delete notification (AJAX)
     */
    public function delete()
    {
        header('Content-Type: application/json');
        
        $notificationId = $_POST['id'] ?? null;
        $membreId = $_SESSION['user']['id_membre'];
        
        if (!$notificationId) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            exit;
        }
        
        $result = $this->model->deleteNotification($notificationId, $membreId);
        
        echo json_encode([
            'success' => $result,
            'unread_count' => $this->model->getUnreadCount($membreId)
        ]);
        exit;
    }

    /**
     * Test notification creation
     */
    public function test()
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: ?page=accueil');
            exit;
        }

        $membreId = $_SESSION['user']['id_membre'];
        
        $this->model->create([
            'id_membre' => $membreId,
            'type' => 'systeme',
            'titre' => 'Test de notification',
            'message' => 'Ceci est un test du système de notifications.',
            'lien' => '?page=notifications'
        ]);

        $_SESSION['flash_message'] = 'Notification de test créée';
        $_SESSION['flash_type'] = 'success';
        
        header('Location: ?page=notifications');
        exit;
    }
}
?>