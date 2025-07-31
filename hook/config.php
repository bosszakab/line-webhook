<?php
// config.php - Database Configuration
class DatabaseConfig {
    private $host = 'localhost';        // เปลี่ยนเป็น host ของคุณ
    private $dbname = 'webhook_db';     // เปลี่ยนเป็นชื่อ database ของคุณ
    private $username = 'root';         // สำหรับ XAMPP ใช้ 'root'
    private $password = '';             // สำหรับ XAMPP ปกติจะเป็นค่าว่าง
    private $pdo;

    public function connect() {
        try {
            $this->pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            return $this->pdo;
        } catch (PDOException $e) {
            // Log error แทนที่จะ die()
            error_log("Database connection failed: " . $e->getMessage());
            return null;
        }
    }
}
?>