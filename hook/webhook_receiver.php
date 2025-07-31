<?php
require_once 'config.php';

// webhook_receiver.php - รับข้อมูลจาก Webhook
class WebhookReceiver {
    private $db;
    private $debugMode = true; // เปิดใช้งานโหมด debug

    public function __construct() {
        $config = new DatabaseConfig();
        $this->db = $config->connect();
        
        // สร้างโฟลเดอร์ logs ถ้ายังไม่มี
        if (!is_dir('webhook_logs')) {
            mkdir('webhook_logs', 0755, true);
        }
    }

    public function handleWebhook() {
        try {
            // ตั้งค่า headers ให้ถูกต้อง
            header('Content-Type: application/json; charset=UTF-8');
            
            // รับข้อมูล JSON จาก Webhook
            $input = file_get_contents('php://input');
            
            // Log ข้อมูลที่รับมาทันที
            $this->logWebhookData($input, $_SERVER);
            
            // ตรวจสอบว่ามีข้อมูลส่งมาหรือไม่
            if (empty($input)) {
                $this->logError("No input data received");
                if ($this->debugMode) {
                    http_response_code(200);
                    echo json_encode(['status' => 'ok', 'message' => 'No data received but response 200']);
                    return;
                }
            }

            $webhookData = json_decode($input, true);
            
            // ตรวจสอบว่า JSON decode สำเร็จหรือไม่
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logError("JSON decode error: " . json_last_error_msg());
                if ($this->debugMode) {
                    http_response_code(200);
                    echo json_encode(['status' => 'ok', 'message' => 'JSON error but response 200']);
                    return;
                }
            }

            // ประมวลผลข้อมูลหากมีการเชื่อมต่อฐานข้อมูล
            if ($webhookData && $this->db) {
                $this->processWebhookData($webhookData);
            } else {
                // แม้ฐานข้อมูลเชื่อมต่อไม่ได้ ก็ยัง response 200
                $this->logError("Database connection failed or no webhook data");
            }

            // ตอบกลับ 200 เสมอให้ LINE Platform
            http_response_code(200);
            
            if ($this->debugMode) {
                echo json_encode([
                    'status' => 'success',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'data_received' => !empty($webhookData)
                ]);
            } else {
                echo "OK";
            }

        } catch (Exception $e) {
            // Log error แต่ยัง response 200
            $this->logError("Exception in handleWebhook: " . $e->getMessage());
            
            http_response_code(200);
            if ($this->debugMode) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Exception occurred but response 200',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                echo "OK";
            }
        }
    }

    private function processWebhookData($data) {
        // สำหรับ LINE Bot
        if (isset($data['events'])) {
            foreach ($data['events'] as $event) {
                if ($event['type'] === 'message' && isset($event['source']['userId'])) {
                    $this->saveUserData([
                        'platform' => 'line',
                        'user_id' => $event['source']['userId'],
                        'message_type' => $event['message']['type'],
                        'message_text' => $event['message']['text'] ?? '',
                        'timestamp' => $event['timestamp'],
                        'reply_token' => $event['replyToken'] ?? '',
                        'webhook_event_id' => $event['webhookEventId'] ?? ''
                    ]);
                }
            }
        }
        // สำหรับ Facebook Messenger
        elseif (isset($data['entry'])) {
            foreach ($data['entry'] as $entry) {
                if (isset($entry['messaging'])) {
                    foreach ($entry['messaging'] as $messaging) {
                        if (isset($messaging['sender']['id'])) {
                            $this->saveUserData([
                                'platform' => 'facebook',
                                'user_id' => $messaging['sender']['id'],
                                'message_type' => 'text',
                                'message_text' => $messaging['message']['text'] ?? '',
                                'timestamp' => $messaging['timestamp'],
                                'reply_token' => '',
                                'webhook_event_id' => ''
                            ]);
                        }
                    }
                }
            }
        }
        // สำหรับ Generic Webhook (ตามโครงสร้างในภาพ)
        elseif (isset($data['source']['userId'])) {
            $this->saveUserData([
                'platform' => 'custom',
                'user_id' => $data['source']['userId'],
                'message_type' => $data['message']['type'] ?? 'text',
                'message_text' => $data['message']['text'] ?? '',
                'timestamp' => $data['timestamp'] ?? time() * 1000,
                'reply_token' => $data['replyToken'] ?? '',
                'webhook_event_id' => $data['webhookEventId'] ?? ''
            ]);
        }
    }

    private function saveUserData($userData) {
        // ถ้าไม่มีการเชื่อมต่อฐานข้อมูล ข้ามการบันทึก
        if (!$this->db) {
            $this->logError("Cannot save user data: No database connection");
            return;
        }

        try {
            // บันทึกข้อมูลผู้ใช้
            $stmt = $this->db->prepare("
                INSERT INTO users (platform, user_id, first_seen, last_seen, message_count) 
                VALUES (:platform, :user_id, NOW(), NOW(), 1)
                ON DUPLICATE KEY UPDATE 
                last_seen = NOW(), 
                message_count = message_count + 1
            ");
            
            $stmt->execute([
                'platform' => $userData['platform'],
                'user_id' => $userData['user_id']
            ]);

            // บันทึกข้อความ
            $stmt = $this->db->prepare("
                INSERT INTO messages (
                    platform, user_id, message_type, message_text, 
                    timestamp, reply_token, webhook_event_id, created_at
                ) VALUES (
                    :platform, :user_id, :message_type, :message_text,
                    :timestamp, :reply_token, :webhook_event_id, NOW()
                )
            ");

            $stmt->execute($userData);
            
            $this->logInfo("User data saved successfully for user: " . $userData['user_id']);

        } catch (PDOException $e) {
            $this->logError("Database error: " . $e->getMessage());
        }
    }

    private function logWebhookData($data, $serverData = []) {
        $logFile = 'webhook_logs/' . date('Y-m-d') . '.log';
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? '',
            'data' => $data,
            'headers' => function_exists('getallheaders') ? getallheaders() : []
        ];
        
        file_put_contents($logFile, json_encode($logEntry, JSON_PRETTY_PRINT) . "\n---\n", FILE_APPEND | LOCK_EX);
    }

    private function logError($message) {
        $logFile = 'webhook_logs/error_' . date('Y-m-d') . '.log';
        $logEntry = date('Y-m-d H:i:s') . " ERROR: " . $message . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    private function logInfo($message) {
        if ($this->debugMode) {
            $logFile = 'webhook_logs/info_' . date('Y-m-d') . '.log';
            $logEntry = date('Y-m-d H:i:s') . " INFO: " . $message . "\n";
            file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        }
    }
}
?>