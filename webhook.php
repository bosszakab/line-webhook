<?php
// เปิดแสดง Error ทั้งหมด (สำหรับทดสอบ)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ตั้งค่า Path แบบ Absolute
$logDir = __DIR__ . '/';
$logFile = $logDir . 'webhook_log_raw.txt'; // ไฟล์สำหรับเก็บข้อมูลดิบทั้งหมด
$userIdFile = $logDir . 'user_activity_log.txt'; // ไฟล์สำหรับเก็บ User ID และประเภทกิจกรรม

// รับข้อมูลจาก LINE
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// ถ้าไม่มีข้อมูลเข้ามา ให้หยุดทำงาน
if (!$data) {
    http_response_code(200);
    exit();
}

// บันทึก Log ข้อมูลดิบที่ได้รับทั้งหมด
file_put_contents($logFile, $json . "\n", FILE_APPEND);

// ตรวจสอบว่ามี events ส่งมาหรือไม่
if (!empty($data['events'])) {
    foreach ($data['events'] as $event) {
        // ดึง userId จากทุก event ที่มี source
        if (!empty($event['source']['userId'])) {
            $userId = $event['source']['userId'];
            $eventType = $event['type'];
            $messageContent = '';

            // เจาะจงเฉพาะ event ที่เป็น message เพื่อดูเนื้อหา
            if ($eventType === 'message' && !empty($event['message']['type'])) {
                $messageType = $event['message']['type'];
                $messageContent = " | Message Type: $messageType";
            }
            
            $logMessage = date('[Y-m-d H:i:s]') . " Event: $eventType | User ID: $userId" . $messageContent . "\n";
            file_put_contents($userIdFile, $logMessage, FILE_APPEND);
        }
    }
}

// ตอบกลับ LINE Server
http_response_code(200);
?>
