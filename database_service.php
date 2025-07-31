<?php
// database_service.php
// คลาสนี้ทำหน้าที่จัดการการเชื่อมต่อและคำสั่งทั้งหมดที่เกี่ยวกับฐานข้อมูล

class DatabaseService {
    /**
     * @var mysqli|null เก็บ instance ของการเชื่อมต่อฐานข้อมูล
     */
    private ?mysqli $conn;

    /**
     * Constructor ของคลาส จะถูกเรียกเมื่อมีการสร้างอ็อบเจกต์
     * และจะทำการเชื่อมต่อฐานข้อมูลทันที
     * @param array $db_config ข้อมูลการเชื่อมต่อที่ส่งมาจาก config.php
     */
    public function __construct(array $db_config) {
        // สร้างการเชื่อมต่อ
        $this->conn = new mysqli(
            $db_config['host'],
            $db_config['user'],
            $db_config['pass'],
            $db_config['name']
        );

        // ตรวจสอบข้อผิดพลาดในการเชื่อมต่อ
        if ($this->conn->connect_error) {
            // ในระบบจริง ควรจะ log error แทนการ die()
            die("Database connection failed: " . $this->conn->connect_error);
        }

        // ตั้งค่า character set เป็น utf8
        $this->conn->set_charset("utf8");
    }

    /**
     * ลงทะเบียนผู้ป่วยใหม่
     * @param string $hn
     * @param string $name
     * @param string $userId
     * @return bool คืนค่า true หากสำเร็จ, false หากล้มเหลว
     */
    public function registerPatient(string $hn, string $name, string $userId): bool {
        $stmt = $this->conn->prepare("INSERT INTO patients (patient_hn, patient_name, line_user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $hn, $name, $userId);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    /**
     * ดึงข้อมูลผู้ป่วยทั้งหมด
     * @return array รายชื่อผู้ป่วยทั้งหมด
     */
    public function getAllPatients(): array {
        $result = $this->conn->query("SELECT patient_hn, patient_name, line_user_id, registered_at FROM patients ORDER BY registered_at DESC");
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }
    
    /**
     * ดึงเฉพาะ User ID ของผู้ป่วยทั้งหมด
     * @return array อาร์เรย์ของ LINE User ID
     */
    public function getAllPatientUserIds(): array {
        $result = $this->conn->query("SELECT line_user_id FROM patients");
        if ($result) {
            // ดึงข้อมูลเฉพาะคอลัมน์ 'line_user_id' มาเป็น array
            return array_column($result->fetch_all(MYSQLI_ASSOC), 'line_user_id');
        }
        return [];
    }

    /**
     * Destructor ของคลาส จะถูกเรียกเมื่ออ็อบเจกต์ถูกทำลาย
     * ใช้สำหรับปิดการเชื่อมต่อฐานข้อมูลโดยอัตโนมัติ
     */
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
