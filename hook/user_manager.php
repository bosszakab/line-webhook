<?php
// user_manager.php - คลาสสำหรับจัดการและดึงข้อมูลผู้ใช้จากฐานข้อมูล

require_once 'config.php';

class UserManager {
    private $db;

    public function __construct() {
        $config = new DatabaseConfig();
        $this->db = $config->connect();
    }

    public function getAllUsers($platform = null) {
        $sql = "SELECT * FROM users";
        $params = [];

        if ($platform) {
            $sql .= " WHERE platform = :platform";
            $params['platform'] = $platform;
        }

        $sql .= " ORDER BY last_seen DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getUserMessages($userId, $platform, $limit = 50) {
        $stmt = $this->db->prepare("
            SELECT * FROM messages 
            WHERE user_id = :user_id AND platform = :platform 
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':platform', $platform);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getUserStats() {
        $stmt = $this->db->prepare("
            SELECT 
                platform,
                COUNT(*) as user_count,
                SUM(message_count) as total_messages
            FROM users 
            GROUP BY platform
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>