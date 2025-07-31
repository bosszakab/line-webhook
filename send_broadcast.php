<?php
// send_broadcast.php - จัดการการส่งข้อความประกาศ

// !!! --- ตั้งค่าสำคัญ --- !!!
// นำ Channel Access Token (Long-lived) ของคุณจาก LINE Developers Console มาใส่ที่นี่
define('vOFSE7a62MaT6Rg3bMawrPHf5q0uvBSCT6uDCCMiUzxXLmF96yCCeVtT3BHLjOwajR03IM91aWZzlicGq0eqSmQWCv9TgtFFcn2c1ZZw/yQ2he4TYacHc5CwpZGg2MgcjbuNOuBHDc7p/jkmycTBMgdB04t89/1O/w1cDnyilFU=');
// !!! -------------------- !!!


require_once 'user_manager.php';
require_once 'line_api_client.php';

// ตรวจสอบว่าเป็นการส่งแบบ POST และมีข้อความหรือไม่
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['broadcast_message'])) {
    header('Location: index.php?broadcast_status=error&message=InvalidRequest');
    exit();
}

$messageText = $_POST['broadcast_message'];

// 1. ดึง User ID ทั้งหมดจากฐานข้อมูล
$userManager = new UserManager();
$allUserIds = $userManager->getAllUserIds('line'); // ดึงเฉพาะผู้ใช้จาก LINE

if (empty($allUserIds)) {
    header('Location: index.php?broadcast_status=error&message=NoUsers');
    exit();
}

// 2. เตรียมส่งข้อความผ่าน LINE API
$lineClient = new LineApiClient(YOUR_CHANNEL_ACCESS_TOKEN);

// LINE API จำกัดการส่งแบบ Multicast ครั้งละไม่เกิน 500 คน
// เราจึงต้องแบ่ง User ID ออกเป็นกลุ่มๆ กลุ่มละ 500
$userIdChunks = array_chunk($allUserIds, 500);
$totalSuccess = 0;
$totalFailed = 0;
$lastErrorMessage = '';

foreach ($userIdChunks as $chunk) {
    $result = $lineClient->sendMulticast($chunk, $messageText);
    if ($result['success']) {
        $totalSuccess += count($chunk);
    } else {
        $totalFailed += count($chunk);
        $lastErrorMessage = $result['message'];
        // บันทึกข้อผิดพลาดลง log เพื่อตรวจสอบภายหลัง
        error_log("Broadcast Error: " . $result['message']);
    }
}

// 3. ส่งผลลัพธ์กลับไปยังหน้า Dashboard
if ($totalFailed > 0) {
    $status = 'partial_success';
    $message = "Sent to {$totalSuccess} users, Failed for {$totalFailed} users. Last Error: " . urlencode($lastErrorMessage);
    header("Location: index.php?broadcast_status={$status}&message={$message}");
} else {
    $status = 'success';
    $message = "Message sent to all {$totalSuccess} users successfully.";
    header("Location: index.php?broadcast_status={$status}&message=" . urlencode($message));
}
exit();
?>
