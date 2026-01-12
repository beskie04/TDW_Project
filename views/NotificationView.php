<?php
require_once __DIR__ . '/BaseView.php';

class NotificationView extends BaseView
{
    public function __construct()
    {
        parent::__construct();
        $this->currentPage = 'notifications';
        $this->pageTitle = 'Notifications';
    }

    /**
     * Render notifications page
     */
    public function renderIndex($notifications)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h1><i class="fas fa-bell"></i> Notifications</h1>
                        <p class="subtitle">Restez informé des événements et actualités</p>
                    </div>
                    <button onclick="markAllAsRead()" class="btn btn-outline">
                        <i class="fas fa-check-double"></i> Tout marquer comme lu
                    </button>
                </div>

                <?php if (empty($notifications)): ?>
                    <div style="text-align: center; padding: 4rem 2rem; color: var(--gray-600);">
                        <i class="fas fa-bell-slash" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                        <h3>Aucune notification</h3>
                        <p>Vous n'avez pas encore de notifications</p>
                    </div>
                <?php else: ?>
                    <div class="notifications-list">
                        <?php foreach ($notifications as $notif): ?>
                            <?php $this->renderNotificationItem($notif); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <style>
        .notifications-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .notification-item {
            background: white;
            padding: 1.25rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .notification-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        .notification-item.unread {
            background: #f0f7ff;
            border-left-color: var(--primary-color);
        }

        .notification-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .notification-icon.evenement_rappel {
            background: #fff3cd;
            color: #856404;
        }

        .notification-icon.evenement_modification {
            background: #d1ecf1;
            color: #0c5460;
        }

        .notification-icon.evenement_annulation {
            background: #f8d7da;
            color: #721c24;
        }

        .notification-icon.inscription_confirmee {
            background: #d4edda;
            color: #155724;
        }

        .notification-icon.systeme {
            background: #e2e3e5;
            color: #383d41;
        }

        .notification-content {
            flex: 1;
            min-width: 0;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }

        .notification-title {
            font-weight: 600;
            color: var(--dark-color);
            margin: 0;
        }

        .notification-date {
            font-size: 0.85rem;
            color: var(--gray-600);
            white-space: nowrap;
        }

        .notification-message {
            color: var(--gray-700);
            line-height: 1.6;
            margin: 0.5rem 0;
        }

        .notification-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 0.75rem;
        }

        .notification-actions button,
        .notification-actions a {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .notification-actions .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .notification-actions .btn-outline {
            background: transparent;
            color: var(--gray-600);
            border: 1px solid var(--gray-300);
        }

        .notification-actions button:hover,
        .notification-actions a:hover {
            transform: translateY(-1px);
        }
        </style>

        <script>
        function markAsRead(id, link = null) {
            fetch('?page=notifications&action=markAsRead', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateNotificationBadge(data.unread_count);
                    if (link) {
                        window.location.href = link;
                    } else {
                        location.reload();
                    }
                }
            });
        }

        function deleteNotification(id) {
            if (!confirm('Supprimer cette notification ?')) return;
            
            fetch('?page=notifications&action=delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateNotificationBadge(data.unread_count);
                    location.reload();
                }
            });
        }

        function markAllAsRead() {
            fetch('?page=notifications&action=markAllAsRead', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateNotificationBadge(0);
                    location.reload();
                }
            });
        }

        function updateNotificationBadge(count) {
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            }
        }
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Render single notification item
     */
    private function renderNotificationItem($notif)
    {
        $isUnread = !$notif['est_lu'];
        $icon = $this->getNotificationIcon($notif['type_notification']);
        $date = $this->formatDate($notif['date_creation']);
        ?>
        
        <div class="notification-item <?= $isUnread ? 'unread' : '' ?>">
            <div class="notification-icon <?= htmlspecialchars($notif['type_notification']) ?>">
                <i class="<?= $icon ?>"></i>
            </div>
            
            <div class="notification-content">
                <div class="notification-header">
                    <h3 class="notification-title">
                        <?= htmlspecialchars($notif['titre']) ?>
                        <?php if ($isUnread): ?>
                            <span style="display: inline-block; width: 8px; height: 8px; background: var(--primary-color); border-radius: 50%; margin-left: 0.5rem;"></span>
                        <?php endif; ?>
                    </h3>
                    <span class="notification-date"><?= $date ?></span>
                </div>
                
                <p class="notification-message">
                    <?= nl2br(htmlspecialchars($notif['message'])) ?>
                </p>
                
                <div class="notification-actions">
                    <?php if ($notif['lien']): ?>
                        <a href="<?= htmlspecialchars($notif['lien']) ?>" 
                           class="btn-primary"
                           onclick="markAsRead(<?= $notif['id_notification'] ?>, '<?= htmlspecialchars($notif['lien']) ?>'); return false;">
                            <i class="fas fa-eye"></i> Voir
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($isUnread): ?>
                        <button onclick="markAsRead(<?= $notif['id_notification'] ?>)" class="btn-outline">
                            <i class="fas fa-check"></i> Marquer comme lu
                        </button>
                    <?php endif; ?>
                    
                    <button onclick="deleteNotification(<?= $notif['id_notification'] ?>)" class="btn-outline">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get icon for notification type
     */
    private function getNotificationIcon($type)
    {
        $icons = [
            'evenement_rappel' => 'fas fa-clock',
            'evenement_modification' => 'fas fa-edit',
            'evenement_annulation' => 'fas fa-times-circle',
            'inscription_confirmee' => 'fas fa-check-circle',
            'systeme' => 'fas fa-info-circle'
        ];
        
        return $icons[$type] ?? 'fas fa-bell';
    }

    /**
     * Format date for display
     */
    private function formatDate($dateString)
    {
        $date = new DateTime($dateString);
        $now = new DateTime();
        $diff = $now->diff($date);

        if ($diff->d == 0) {
            if ($diff->h == 0) {
                if ($diff->i == 0) {
                    return 'À l\'instant';
                }
                return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
            }
            return $diff->h . ' heure' . ($diff->h > 1 ? 's' : '');
        } elseif ($diff->d == 1) {
            return 'Hier';
        } elseif ($diff->d < 7) {
            return $diff->d . ' jours';
        } else {
            return $date->format('d/m/Y à H:i');
        }
    }
}
?>