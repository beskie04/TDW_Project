<?php
class NotificationBell
{
    /**
     * Render notification bell with dropdown
     */
    public static function render()
    {
        if (!isset($_SESSION['user'])) {
            return;
        }

        require_once __DIR__ . '/../../models/NotificationModel.php';
        $model = new NotificationModel();
        $membreId = $_SESSION['user']['id_membre'];
        $unreadCount = $model->getUnreadCount($membreId);
        $recentNotifications = $model->getRecent($membreId, 5);
        ?>

        <div class="notification-bell-container">
            <button class="notification-bell-button" id="notification-bell" aria-label="Notifications">
                <i class="fas fa-bell"></i>
                <?php if ($unreadCount > 0): ?>
                    <span class="notification-badge"><?= $unreadCount > 99 ? '99+' : $unreadCount ?></span>
                <?php endif; ?>
            </button>

            <div class="notification-dropdown" id="notification-dropdown">
                <div class="notification-dropdown-header">
                    <h3>Notifications</h3>
                    <?php if ($unreadCount > 0): ?>
                        <button class="mark-all-read" onclick="markAllNotificationsRead()">
                            <i class="fas fa-check-double"></i> Tout marquer
                        </button>
                    <?php endif; ?>
                </div>

                <div class="notification-dropdown-body">
                    <?php if (empty($recentNotifications)): ?>
                        <div class="no-notifications">
                            <i class="fas fa-bell-slash"></i>
                            <p>Aucune notification</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentNotifications as $notif): ?>
                            <?php self::renderDropdownItem($notif); ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="notification-dropdown-footer">
                    <a href="?page=notifications" class="view-all-link">
                        Voir toutes les notifications
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <style>
        .notification-bell-container {
            position: relative;
        }

        .notification-bell-button {
            position: relative;
            background: transparent;
            border: none;
            color: var(--text-color);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .notification-bell-button:hover {
            background: rgba(0, 0, 0, 0.05);
            transform: scale(1.1);
        }

        .notification-bell-button i {
            font-size: 1.25rem;
        }

        .notification-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background: #dc3545;
            color: white;
            border-radius: 10px;
            padding: 0 5px;
            font-size: 0.7rem;
            font-weight: bold;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .notification-dropdown {
            position: absolute;
            top: calc(100% + 0.5rem);
            right: 0;
            width: 400px;
            max-width: 90vw;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            display: none;
            flex-direction: column;
            max-height: 600px;
            z-index: 1000;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .notification-dropdown.show {
            display: flex;
        }

        .notification-dropdown-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-dropdown-header h3 {
            margin: 0;
            font-size: 1.1rem;
            color: var(--dark-color);
        }

        .mark-all-read {
            background: transparent;
            border: none;
            color: var(--primary-color);
            cursor: pointer;
            font-size: 0.85rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .mark-all-read:hover {
            background: rgba(0, 123, 255, 0.1);
        }

        .notification-dropdown-body {
            flex: 1;
            overflow-y: auto;
            max-height: 400px;
        }

        .notification-dropdown-item {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--gray-200);
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
        }

        .notification-dropdown-item:hover {
            background: var(--gray-100);
        }

        .notification-dropdown-item:last-child {
            border-bottom: none;
        }

        .notification-dropdown-item.unread {
            background: #f0f7ff;
        }

        .notification-dropdown-item.unread:hover {
            background: #e6f2ff;
        }

        .notif-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .notif-icon.evenement_rappel {
            background: #fff3cd;
            color: #856404;
        }

        .notif-icon.evenement_modification {
            background: #d1ecf1;
            color: #0c5460;
        }

        .notif-icon.evenement_annulation {
            background: #f8d7da;
            color: #721c24;
        }

        .notif-icon.inscription_confirmee {
            background: #d4edda;
            color: #155724;
        }

        .notif-icon.systeme {
            background: #e2e3e5;
            color: #383d41;
        }

        .notif-content {
            flex: 1;
            min-width: 0;
        }

        .notif-title {
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .notif-unread-dot {
            width: 6px;
            height: 6px;
            background: var(--primary-color);
            border-radius: 50%;
        }

        .notif-message {
            font-size: 0.85rem;
            color: var(--gray-600);
            line-height: 1.4;
            margin-bottom: 0.25rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .notif-time {
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .no-notifications {
            padding: 3rem 2rem;
            text-align: center;
            color: var(--gray-500);
        }

        .no-notifications i {
            font-size: 3rem;
            opacity: 0.3;
            margin-bottom: 1rem;
        }

        .no-notifications p {
            margin: 0;
        }

        .notification-dropdown-footer {
            padding: 0.75rem 1.25rem;
            border-top: 1px solid var(--gray-200);
        }

        .view-all-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: gap 0.2s;
        }

        .view-all-link:hover {
            gap: 0.75rem;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .notification-dropdown {
                position: fixed;
                top: auto;
                bottom: 0;
                left: 0;
                right: 0;
                width: 100%;
                max-width: 100%;
                border-radius: 12px 12px 0 0;
                max-height: 80vh;
            }
        }
        </style>

        <script>
        // Toggle notification dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const bell = document.getElementById('notification-bell');
            const dropdown = document.getElementById('notification-dropdown');

            if (bell && dropdown) {
                bell.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdown.classList.toggle('show');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!dropdown.contains(e.target) && !bell.contains(e.target)) {
                        dropdown.classList.remove('show');
                    }
                });

                // Prevent dropdown close when clicking inside
                dropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }

            // Update notification count periodically
            setInterval(updateNotificationCount, 60000); // Every minute
        });

        function updateNotificationCount() {
            fetch('?page=notifications&action=getUnreadCount')
                .then(response => response.json())
                .then(data => {
                    updateNotificationBadge(data.count);
                });
        }

        function updateNotificationBadge(count) {
            const badge = document.querySelector('.notification-badge');
            const bellButton = document.getElementById('notification-bell');
            
            if (count > 0) {
                if (badge) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.style.display = 'flex';
                } else if (bellButton) {
                    const newBadge = document.createElement('span');
                    newBadge.className = 'notification-badge';
                    newBadge.textContent = count > 99 ? '99+' : count;
                    bellButton.appendChild(newBadge);
                }
            } else if (badge) {
                badge.style.display = 'none';
            }
        }

        function markNotificationRead(id, link) {
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

        function markAllNotificationsRead() {
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
        </script>

        <?php
    }

    /**
     * Render single dropdown notification item
     */
    private static function renderDropdownItem($notif)
    {
        $isUnread = !$notif['est_lu'];
        $icon = self::getNotificationIcon($notif['type_notification']);
        $time = self::getTimeAgo($notif['date_creation']);
        $onclick = $notif['lien'] ? "markNotificationRead({$notif['id_notification']}, '{$notif['lien']}')" : '';
        ?>
        
        <div class="notification-dropdown-item <?= $isUnread ? 'unread' : '' ?>" 
             onclick="<?= $onclick ?>">
            <div class="notif-icon <?= htmlspecialchars($notif['type_notification']) ?>">
                <i class="<?= $icon ?>"></i>
            </div>
            <div class="notif-content">
                <div class="notif-title">
                    <?= htmlspecialchars($notif['titre']) ?>
                    <?php if ($isUnread): ?>
                        <span class="notif-unread-dot"></span>
                    <?php endif; ?>
                </div>
                <div class="notif-message">
                    <?= htmlspecialchars($notif['message']) ?>
                </div>
                <div class="notif-time"><?= $time ?></div>
            </div>
        </div>
        <?php
    }

    /**
     * Get icon for notification type
     */
    private static function getNotificationIcon($type)
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
     * Get time ago string
     */
    private static function getTimeAgo($dateString)
    {
        $date = new DateTime($dateString);
        $now = new DateTime();
        $diff = $now->diff($date);

        if ($diff->d == 0) {
            if ($diff->h == 0) {
                if ($diff->i == 0) return 'À l\'instant';
                return $diff->i . 'm';
            }
            return $diff->h . 'h';
        } elseif ($diff->d == 1) {
            return 'Hier';
        } elseif ($diff->d < 7) {
            return $diff->d . 'j';
        } else {
            return $date->format('d/m');
        }
    }
}
?>