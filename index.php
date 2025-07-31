<?php
// ไฟล์: index.php (เวอร์ชันสมบูรณ์)
session_start();

// --- (สำคัญ) ส่วนตั้งค่า ---
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'hospital_line';
define('LINE_CHANNEL_ACCESS_TOKEN', 'vOFSE7a62MaT6Rg3bMawrPHf5q0uvBSCT6uDCCMiUzxXLmF96yCCeVtT3BHLjOwajR03IM91aWZzlicGq0eqSmQWCv9TgtFFcn2c1ZZw/yQ2he4TYacHc5CwpZGg2MgcjbuNOuBHDc7p/jkmycTBMgdB04t89/1O/w1cDnyilFU='); // <-- ใส่ Token ของคุณที่นี่

// --- เชื่อมต่อฐานข้อมูล ---
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// --- ฟังก์ชันสำหรับส่งข้อความ LINE ---
function send_line_multicast($userIds, $messageText) {
    if (empty(LINE_CHANNEL_ACCESS_TOKEN) || LINE_CHANNEL_ACCESS_TOKEN === 'ใส่_CHANNEL_ACCESS_TOKEN_ของคุณที่นี่') {
        return ['success' => false, 'message' => 'ยังไม่ได้ตั้งค่า Channel Access Token'];
    }
    $messages = [['type' => 'text', 'text' => $messageText]];
    $payload = ['to' => $userIds, 'messages' => $messages];
    $headers = ['Content-Type: application/json', 'Authorization: Bearer ' . LINE_CHANNEL_ACCESS_TOKEN];
    $ch = curl_init('https://api.line.me/v2/bot/message/multicast');
    curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_POSTFIELDS => json_encode($payload), CURLOPT_HTTPHEADER => $headers]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code == 200) return ['success' => true];
    $error_details = json_decode($response, true);
    return ['success' => false, 'message' => $error_details['message'] ?? 'Unknown API Error'];
}

// --- ส่วนจัดการ Form ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'register_user':
            $patient_hn = $_POST['patient_hn'];
            $patient_name = $_POST['patient_name'];
            $line_user_id = $_POST['line_user_id'];
            
            if (strlen($line_user_id) !== 33 || !preg_match('/^U[a-fA-F0-9]{32}$/', $line_user_id)) {
                $_SESSION['feedback'] = ['type' => 'error', 'message' => 'รูปแบบ LINE User ID ไม่ถูกต้อง!'];
            } else {
                // (แก้ไข) เพิ่ม try-catch เพื่อดักจับข้อผิดพลาดจากฐานข้อมูล
                try {
                    $stmt = $conn->prepare("INSERT INTO patients (patient_hn, patient_name, line_user_id) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $patient_hn, $patient_name, $line_user_id);
                    $stmt->execute();
                    $_SESSION['feedback'] = ['type' => 'success', 'message' => 'ลงทะเบียนผู้ใช้งานใหม่สำเร็จ!'];
                } catch (mysqli_sql_exception $e) {
                    // ตรวจสอบรหัสข้อผิดพลาดสำหรับ 'Duplicate entry' (1062)
                    if ($e->getCode() == 1062) {
                        $_SESSION['feedback'] = ['type' => 'error', 'message' => 'ลงทะเบียนไม่สำเร็จ: LINE User ID นี้มีอยู่ในระบบแล้ว'];
                    } else {
                        // สำหรับข้อผิดพลาดอื่นๆ ของฐานข้อมูล
                        $_SESSION['feedback'] = ['type' => 'error', 'message' => 'เกิดข้อผิดพลาดกับฐานข้อมูล: ' . $e->getMessage()];
                    }
                } finally {
                    if (isset($stmt)) {
                        $stmt->close();
                    }
                }
            }
            break;
        case 'schedule_notification':
            $stmt = $conn->prepare("INSERT INTO scheduled_notifications (patient_user_id, message, send_at) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $_POST['user_id'], $_POST['message'], $_POST['send_at']);
            $_SESSION['feedback'] = $stmt->execute()
                ? ['type' => 'success', 'message' => 'บันทึกการแจ้งเตือนเรียบร้อยแล้ว!']
                : ['type' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $stmt->error];
            $stmt->close();
            break;
        case 'send_broadcast':
            $broadcast_message = $_POST['broadcast_message'];
            $result = $conn->query("SELECT line_user_id FROM patients");
            $all_user_ids = array_column($result->fetch_all(MYSQLI_ASSOC), 'line_user_id');
            if (empty($all_user_ids)) {
                $_SESSION['feedback'] = ['type' => 'error', 'message' => 'ไม่พบผู้ใช้งานให้ส่งข้อความ'];
            } else {
                $send_result = send_line_multicast($all_user_ids, $broadcast_message);
                $_SESSION['feedback'] = $send_result['success']
                    ? ['type' => 'success', 'message' => 'ส่งข้อความประกาศถึงผู้ใช้ ' . count($all_user_ids) . ' คน สำเร็จ!']
                    : ['type' => 'error', 'message' => 'ส่งข้อความประกาศไม่สำเร็จ: ' . $send_result['message']];
            }
            break;
    }
    header('Location: index.php');
    exit();
}

// --- ดึงข้อมูลเพื่อแสดงผล ---
$patients_result = $conn->query("SELECT patient_hn, patient_name, line_user_id, registered_at FROM patients ORDER BY registered_at DESC");
$feedback = $_SESSION['feedback'] ?? null;
unset($_SESSION['feedback']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ระบบจัดการแจ้งเตือน LINE</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; color: #343a40; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: auto; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px; }
        .card { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 30px; }
        h2, h3 { color: #0056b3; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        label { display: block; margin-top: 15px; font-weight: 600; color: #495057; }
        input[type="text"], input[type="datetime-local"], textarea, select { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ced4da; border-radius: 4px; font-size: 16px; box-sizing: border-box; }
        textarea { resize: vertical; min-height: 80px;}
        button { display: block; width: 100%; padding: 12px; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 20px; transition: background-color 0.2s; }
        .btn-schedule { background-color: #007bff; }
        .btn-schedule:hover { background-color: #0056b3; }
        .btn-register { background-color: #28a745; }
        .btn-register:hover { background-color: #218838; }
        .btn-broadcast { background-color: #fd7e14; }
        .btn-broadcast:hover { background-color: #e86a02; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #dee2e6; text-align: left; }
        th { background-color: #343a40; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .feedback { padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid transparent; }
        .feedback.success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .feedback.error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🏥 ระบบจัดการแจ้งเตือนนัดหมายผ่าน LINE</h2>
        
        <?php if ($feedback): ?>
            <div class="feedback <?php echo htmlspecialchars($feedback['type']); ?>">
                <?php echo htmlspecialchars($feedback['message']); ?>
            </div>
        <?php endif; ?>

        <div class="grid">
            <div class="card">
                <h3>📅 สร้างนัดหมายใหม่</h3>
                <form action="index.php" method="post">
                    <input type="hidden" name="action" value="schedule_notification">
                    <label for="user_id">เลือกคนไข้:</label>
                    <select id="user_id" name="user_id" required>
                        <option value="">-- กรุณาเลือกคนไข้ --</option>
                        <?php
                        if ($patients_result && $patients_result->num_rows > 0) {
                            $patients_result->data_seek(0);
                            while($row = $patients_result->fetch_assoc()) {
                                echo "<option value='" . htmlspecialchars($row['line_user_id']) . "'>" . htmlspecialchars($row['patient_name']) . " (" . htmlspecialchars($row['patient_hn']) . ")</option>";
                            }
                        }
                        ?>
                    </select>
                    <label for="message">ข้อความแจ้งเตือน:</label>
                    <textarea id="message" name="message" rows="4" placeholder="เช่น แจ้งเตือนนัดหมายพบแพทย์วันที่..." required></textarea>
                    <label for="send_at">เวลาที่ต้องการให้ส่ง:</label>
                    <input type="datetime-local" id="send_at" name="send_at" required>
                    <button type="submit" class="btn-schedule">บันทึกการแจ้งเตือน</button>
                </form>
            </div>

            <!-- ========== (เพิ่มกลับมา) ส่วนของฟอร์มประกาศ ========== -->
            <div class="card">
                <h3>📢 ส่งข้อความประกาศ (ถึงทุกคน)</h3>
                <form action="index.php" method="post" onsubmit="return confirm('ยืนยันการส่งข้อความนี้ถึงผู้ใช้ทุกคน?');">
                    <input type="hidden" name="action" value="send_broadcast">
                    <label for="broadcast_message">ข้อความที่จะประกาศ:</label>
                    <textarea id="broadcast_message" name="broadcast_message" rows="4" placeholder="พิมพ์ข้อความที่ต้องการประกาศที่นี่..." required></textarea>
                    <button type="submit" class="btn-broadcast">ส่งข้อความประกาศทันที</button>
                </form>
                <form action="hook/index.php">
                    <button class="btn-schedule">ข้อมูล User ID ทั้งหมด</button>
                    </form>
            </div>
            <!-- ====================================================== -->
        </div>

        <div class="card">
            <h3>👤 ลงทะเบียนผู้ใช้งานใหม่</h3>
            <form action="index.php" method="post">
                <input type="hidden" name="action" value="register_user">
                <label for="patient_hn">HN ของคนไข้:</label>
                <input type="text" id="patient_hn" name="patient_hn" placeholder="เช่น 01-23-000562" required>
                <label for="patient_name">ชื่อ-นามสกุล คนไข้:</label>
                <input type="text" id="patient_name" name="patient_name" placeholder="เช่น นายทดสอบ ระบบดี" required>
                <label for="line_user_id">LINE User ID:</label>
                <input type="text" id="line_user_id" name="line_user_id" placeholder="Uxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" required>
                <button type="submit" class="btn-register">ลงทะเบียนผู้ใช้งาน</button>
            </form>
        </div>

        <div class="card">
            <h3>👥 รายชื่อผู้ใช้งานที่ลงทะเบียนแล้ว</h3>
            <table>
                <thead>
                    <tr>
                        <th>HN</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>LINE User ID</th>
                        <th>วันที่ลงทะเบียน</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($patients_result && $patients_result->num_rows > 0) {
                        $patients_result->data_seek(0);
                        while($row = $patients_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['patient_hn']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['patient_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['line_user_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['registered_at']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align: center;'>ยังไม่มีผู้ใช้งานลงทะเบียน</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>