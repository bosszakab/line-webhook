<?php
// line_api_service.php
// คลาสนี้ทำหน้าที่เป็นตัวกลางในการสื่อสารกับ LINE Messaging API ทั้งหมด

class LineApiService {
    /**
     * @var string Channel Access Token สำหรับการยืนยันตัวตนกับ LINE API
     */
    private string $channelAccessToken;

    /**
     * @var string URL พื้นฐานของ Messaging API
     */
    private const API_BASE_URL = 'https://api.line.me/v2/bot/message/';

    /**
     * Constructor ของคลาส
     * @param string $channelAccessToken Channel Access Token (Long-lived) ของคุณ
     */
    public function __construct(string $channelAccessToken) {
        $this->channelAccessToken = $channelAccessToken;
    }

    /**
     * ส่งข้อความแบบ Multicast (ส่งหาผู้ใช้หลายคนพร้อมกัน)
     * * @param array $userIds อาร์เรย์ของ User ID ที่จะส่ง (สูงสุด 500 ID ต่อครั้ง)
     * @param string $messageText ข้อความที่ต้องการส่ง
     * @return array ผลลัพธ์การทำงาน ['success' => bool, 'message' => string]
     */
    public function sendMulticast(array $userIds, string $messageText): array {
        // ตรวจสอบข้อมูลเบื้องต้น
        if (empty($this->channelAccessToken) || $this->channelAccessToken === 'ใส่_CHANNEL_ACCESS_TOKEN_ของคุณที่นี่') {
            return ['success' => false, 'message' => 'ยังไม่ได้ตั้งค่า Channel Access Token'];
        }
        if (empty($userIds)) {
            return ['success' => false, 'message' => 'ไม่มี User ID ให้ส่งข้อความ'];
        }

        // สร้างรูปแบบข้อความตามที่ LINE API กำหนด
        $messages = [
            [
                'type' => 'text',
                'text' => $messageText
            ]
        ];

        // เตรียมข้อมูลที่จะส่ง (Payload)
        $payload = [
            'to' => $userIds,
            'messages' => $messages,
        ];

        // เรียกใช้ฟังก์ชันสำหรับส่ง Request จริง
        return $this->sendPostRequest('multicast', $payload);
    }

    /**
     * ฟังก์ชันภายในสำหรับส่ง POST request ไปยัง LINE API โดยใช้ cURL
     * * @param string $endpoint Endpoint ที่ต้องการ (เช่น 'multicast', 'push')
     * @param array $payload ข้อมูลที่จะส่งไปใน Body
     * @return array ผลลัพธ์การทำงาน
     */
    private function sendPostRequest(string $endpoint, array $payload): array {
        // เตรียมส่วน Header ของ Request
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->channelAccessToken
        ];

        // เริ่มต้นการทำงานของ cURL
        $ch = curl_init(self::API_BASE_URL . $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // ตั้งเวลาหมดอายุของ request ไว้ที่ 10 วินาที

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        // ตรวจสอบผลลัพธ์
        if ($curl_error) {
            return ['success' => false, 'message' => 'cURL Error: ' . $curl_error];
        }

        if ($http_code == 200 || $http_code == 202) { // 202 Accepted ก็ถือว่าสำเร็จ
            return ['success' => true, 'message' => 'Request sent successfully.'];
        } else {
            // พยายามถอดรหัสข้อผิดพลาดจาก LINE เพื่อให้เข้าใจง่ายขึ้น
            $error_details = json_decode($response, true);
            $error_message = $error_details['message'] ?? 'Unknown API Error';
            if (isset($error_details['details'])) {
                foreach($error_details['details'] as $detail) {
                    $error_message .= ' | ' . ($detail['property'] ?? '') . ': ' . ($detail['message'] ?? '');
                }
            }
            return [
                'success' => false, 
                'message' => "LINE API Error (HTTP {$http_code}): " . $error_message
            ];
        }
    }
}
