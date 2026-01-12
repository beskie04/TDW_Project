<?php
require_once __DIR__ . '/BaseModel.php';

class NotificationModel extends BaseModel
{
    protected $table = 'notifications';
    protected $primaryKey = 'id_notification';

    /**
     * Get unread notifications count for a user
     */
    public function getUnreadCount($membreId)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE id_membre = :membre_id AND est_lu = 0";
        
        $result = $this->query($sql, ['membre_id' => $membreId]);
        return $result[0]['count'] ?? 0;
    }

    /**
     * Get all notifications for a user
     */
    public function getByMembre($membreId, $limit = 50, $unreadOnly = false)
    {
        $sql = "SELECT n.*, e.titre as evenement_titre
                FROM {$this->table} n
                LEFT JOIN evenements e ON n.id_evenement = e.id_evenement
                WHERE n.id_membre = :membre_id";
        
        if ($unreadOnly) {
            $sql .= " AND n.est_lu = 0";
        }
        
        $sql .= " ORDER BY n.date_creation DESC LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':membre_id', $membreId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $membreId)
    {
        $sql = "UPDATE {$this->table} 
                SET est_lu = 1, date_lecture = NOW() 
                WHERE id_notification = :id AND id_membre = :membre_id";
        
        return $this->execute($sql, [
            'id' => $notificationId,
            'membre_id' => $membreId
        ]);
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($membreId)
    {
        $sql = "UPDATE {$this->table} 
                SET est_lu = 1, date_lecture = NOW() 
                WHERE id_membre = :membre_id AND est_lu = 0";
        
        return $this->execute($sql, ['membre_id' => $membreId]);
    }

    /**
     * Create a notification
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (id_membre, type_notification, titre, message, lien, id_evenement) 
                VALUES (:id_membre, :type, :titre, :message, :lien, :id_evenement)";
        
        return $this->execute($sql, [
            'id_membre' => $data['id_membre'],
            'type' => $data['type'] ?? 'systeme',
            'titre' => $data['titre'],
            'message' => $data['message'],
            'lien' => $data['lien'] ?? null,
            'id_evenement' => $data['id_evenement'] ?? null
        ]);
    }

    /**
     * Create notification for all event participants
     */
    public function notifyEventParticipants($evenementId, $titre, $message, $type = 'evenement_modification')
    {
        $sql = "INSERT INTO {$this->table} (id_membre, type_notification, titre, message, lien, id_evenement)
                SELECT DISTINCT ep.id_membre, :type, :titre, :message, 
                       CONCAT('?page=evenements&action=details&id=', :evenement_id), 
                       :evenement_id
                FROM evenement_participants ep
                WHERE ep.id_evenement = :evenement_id_2 
                AND ep.id_membre IS NOT NULL";
        
        return $this->execute($sql, [
            'type' => $type,
            'titre' => $titre,
            'message' => $message,
            'evenement_id' => $evenementId,
            'evenement_id_2' => $evenementId
        ]);
    }

    /**
     * Send event reminders (24 hours before)
     */
    public function sendEventReminders()
    {
        // Call the stored procedure
        $sql = "CALL envoi_rappels_evenements()";
        return $this->execute($sql);
    }

    /**
     * Delete old read notifications (older than 30 days)
     */
    public function cleanOldNotifications()
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE est_lu = 1 
                AND date_lecture < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        return $this->execute($sql);
    }

    /**
     * Delete notification
     */
    public function deleteNotification($notificationId, $membreId)
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE id_notification = :id AND id_membre = :membre_id";
        
        return $this->execute($sql, [
            'id' => $notificationId,
            'membre_id' => $membreId
        ]);
    }

    /**
     * Get recent notifications for dashboard
     */
    public function getRecent($membreId, $limit = 5)
    {
        return $this->getByMembre($membreId, $limit);
    }
}
?>