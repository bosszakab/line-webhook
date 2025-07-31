<?php
function write_log($message) {
    $log_file = __DIR__ . '/notification_log.txt';
    $log_message = "[" . date("Y-m-d H:i:s") . "] " . $message . "\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// เริ่มการทำงานของสคริปต์
write_log("========== Script execution started by Task Scheduler ==========");

// ค่าตั้งค่า
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'hospital_line';
$channel_access_token = 'vOFSE7a62MaT6Rg3bMawrPHf5q0uvBSCT6uDCCMiUzxXLmF96yCCeVtT3BHLjOwajR03IM91aWZzlicGq0eqSmQWCv9TgtFFcn2c1ZZw/yQ2he4TYacHc5CwpZGg2MgcjbuNOuBHDc7p/jkmycTBMgdB04t89/1O/w1cDnyilFU=';

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    write_log("DB Connection failed: " . $conn->connect_error);
    die("DB Connection failed.");
}
$conn->set_charset("utf8");
write_log("Database connected successfully.");

// ค้นหารายการที่ถึงเวลาส่ง
$result = $conn->query("SELECT id, patient_user_id, message FROM scheduled_notifications WHERE send_at <= NOW() AND status = 'pending'");

if (!$result) {
    write_log("SQL Error: " . $conn->error);
    die("SQL Error.");
}

if ($result->num_rows > 0) {
    write_log($result->num_rows . " pending notifications found.");
    
    while($row = $result->fetch_assoc()) {
        $notification_id = $row['id'];
        $patient_user_id = $row['patient_user_id'];
        $message_text = $row['message'];

        write_log("Processing notification ID: {$notification_id} for User: {$patient_user_id}");

        // ตรวจสอบความถูกต้องของ User ID
        if (strlen($patient_user_id) !== 33 || !preg_match('/^U[a-fA-F0-9]{32}$/', $patient_user_id)) {
            write_log("ERROR: Invalid User ID format: {$patient_user_id}");
            $conn->query("UPDATE scheduled_notifications SET status = 'failed', error_message = 'Invalid User ID format' WHERE id = " . $notification_id);
            continue;
        }

        // เตรียมข้อมูลสำหรับส่ง LINE API
        $post_data = [
            'to' => $patient_user_id,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $message_text
                ]
            ]
        ];
        
        $post_body = json_encode($post_data, JSON_UNESCAPED_UNICODE);
        write_log("Request Body (JSON): " . $post_body);

        // ตรวจสอบว่า User ได้เพิ่ม Bot เป็นเพื่อนหรือไม่
        $profile_url = "https://api.line.me/v2/bot/profile/{$patient_user_id}";
        $ch = curl_init($profile_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $channel_access_token
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 10
        ]);
        
        $profile_response = curl_exec($ch);
        $profile_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $profile_error = curl_error($ch);
        curl_close($ch);

        if ($profile_error) {
            write_log("ERROR: cURL Profile Error: " . $profile_error);
            $conn->query("UPDATE scheduled_notifications SET status = 'failed', error_message = 'Cannot check user profile' WHERE id = " . $notification_id);
            continue;
        }

        if ($profile_http_code !== 200) {
            $error_msg = "Cannot get user profile. HTTP Code: {$profile_http_code}";
            if ($profile_http_code == 404) {
                $error_msg = "User has not added the bot as friend";
            }
            write_log("ERROR: " . $error_msg);
            $conn->query("UPDATE scheduled_notifications SET status = 'failed', error_message = '{$error_msg}' WHERE id = " . $notification_id);
            continue;
        }

        // ส่งข้อความผ่าน LINE API
        $ch = curl_init('https://api.line.me/v2/bot/message/push');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $post_body,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $channel_access_token
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        write_log("cURL execution finished. HTTP Code: {$http_code}");
        
        if ($curl_error) {
            write_log("cURL Error: " . $curl_error);
        }
        
        write_log("Response Body from LINE: " . $response);

        // อัปเดตสถานะในฐานข้อมูล
        $status = ($http_code == 200) ? 'sent' : 'failed';
        $error_message = '';
        
        if ($http_code !== 200) {
            $response_data = json_decode($response, true);
            $error_message = $response_data['message'] ?? 'Unknown error';
            
            switch ($http_code) {
                case 400:
                    $error_message = 'Bad Request: ' . $error_message;
                    break;
                case 401:
                    $error_message = 'Unauthorized: Invalid Channel Access Token';
                    break;
                case 403:
                    $error_message = 'Forbidden: Cannot send message to this user';
                    break;
                case 429:
                    $error_message = 'Rate Limited: Too many requests';
                    break;
                default:
                    $error_message = 'HTTP Error ' . $http_code . ': ' . $error_message;
            }
        }
        
        // ป้องกัน SQL Injection ด้วย real_escape_string
        $error_message_safe = $conn->real_escape_string($error_message);
        $update_sql = "UPDATE scheduled_notifications SET status = '{$status}', error_message = '{$error_message_safe}' WHERE id = {$notification_id}";
        
        if (!$conn->query($update_sql)) {
            write_log("ERROR: Failed to update notification status: " . $conn->error);
        }
        
        write_log("Updated notification ID: {$notification_id} to status '{$status}'" . ($error_message ? " with error: {$error_message}" : ""));
    }
} else {
    write_log("No pending notifications to send.");
}

$conn->close();
write_log("Script finished successfully.");
echo "Script finished. Check log file for details.";
?>