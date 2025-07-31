<?php
// index.php - Entry point
// ตั้งค่า error reporting สำหรับ production
error_reporting(E_ALL);
ini_set('display_errors', 0); // ไม่แสดง error บนหน้าเว็บ
ini_set('log_errors', 1);     // แต่ให้บันทึก error ลง log file

// Include ไฟล์ที่จำเป็น
require_once 'config.php';
require_once 'webhook_receiver.php';
require_once 'admin_dashboard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับ Webhook
    try {
        $receiver = new WebhookReceiver();
        $receiver->handleWebhook();
    } catch (Exception $e) {
        // แม้จะเกิด error ก็ยังต้อง response 200 OK กลับไป
        // เพื่อป้องกันไม่ให้ LINE/Facebook ส่งข้อมูลซ้ำมาอีก
        error_log("Critical error in webhook handler: " . $e->getMessage());
        http_response_code(200);
        echo "OK";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // แสดง Dashboard หรือหน้าทดสอบระบบ
    if (isset($_GET['test'])) {
        // --- หน้าทดสอบระบบ (System Diagnostics Page) ---
        echo "<!DOCTYPE html>";
        echo "<html lang='th'><head><meta charset='UTF-8'><title>Webhook System Test</title>";
        echo "<style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; margin: 40px; background: #f8f9fa; color: #343a40; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
            h1, h3 { color: #212529; border-bottom: 1px solid #eee; padding-bottom: 10px; }
            .status { padding: 15px; margin: 10px 0; border-radius: 5px; border: 1px solid transparent; }
            .success { background: #d4edda; color: #155724; border-color: #c3e6cb; }
            .error { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }
            .info { background: #cce5ff; color: #004085; border-color: #b8daff; }
            code { background: #e9ecef; padding: 3px 6px; border-radius: 4px; font-family: 'SF Mono', Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; }
            ul { padding-left: 20px; }
            li { margin-bottom: 8px; }
        </style>";
        echo "</head><body>";
        
        echo "<div class='container'>";
        echo "<h1><span style='font-size: 2rem;'>🔧</span> Webhook System Diagnostics</h1>";
        
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $uri = rtrim(dirname($_SERVER['REQUEST_URI']), '/\\');
        $baseUrl = $scheme . $host . $uri;
        
        echo "<div class='info'>";
        echo "<h3>📍 ข้อมูลระบบ</h3>";
        echo "<ul>";
        echo "<li><strong>Webhook URL:</strong> <code>" . $baseUrl . "/index.php</code></li>";
        echo "<li><strong>เวลาเซิร์ฟเวอร์:</strong> " . date('Y-m-d H:i:s T') . "</li>";
        echo "<li><strong>PHP Version:</strong> " . PHP_VERSION . "</li>";
        echo "</ul>";
        echo "</div>";
        
        // ทดสอบการเชื่อมต่อฐานข้อมูล
        echo "<h3><span style='font-size: 1.5rem;'>🗄️</span> การเชื่อมต่อฐานข้อมูล</h3>";
        try {
            $config = new DatabaseConfig();
            $db = $config->connect();
            echo "<div class='status success'>✅ เชื่อมต่อฐานข้อมูลสำเร็จ</div>";
            
            // ตรวจสอบตาราง
            $tables = ['users', 'messages', 'webhook_logs'];
            foreach ($tables as $table) {
                $stmt = $db->query("SHOW TABLES LIKE '$table'");
                if ($stmt->rowCount() > 0) {
                    $count = $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                    echo "<div class='status success'>✅ พบตาราง <code>$table</code> (มี $count แถว)</div>";
                } else {
                    echo "<div class='status error'>❌ ไม่พบตาราง <code>$table</code> (กรุณารันไฟล์ <code>database_setup.sql</code>)</div>";
                }
            }
        } catch (Exception $e) {
            echo "<div class='status error'>❌ เชื่อมต่อฐานข้อมูลล้มเหลว: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        
        // ตรวจสอบโฟลเดอร์ logs
        echo "<h3><span style='font-size: 1.5rem;'>📝</span> โฟลเดอร์ Logs</h3>";
        $logDir = 'webhook_logs';
        if (!is_dir($logDir)) {
            if (@mkdir($logDir, 0755, true)) {
                echo "<div class='status success'>✅ สร้างโฟลเดอร์ <code>$logDir</code> สำเร็จ</div>";
            } else {
                echo "<div class='status error'>❌ ไม่สามารถสร้างโฟลเดอร์ <code>$logDir</code> ได้ (กรุณาตรวจสอบ permission)</div>";
            }
        } elseif (is_writable($logDir)) {
            echo "<div class='status success'>✅ โฟลเดอร์ <code>$logDir</code> เขียนได้ปกติ</div>";
        } else {
            echo "<div class='status error'>❌ โฟลเดอร์ <code>$logDir</code> เขียนไม่ได้ (กรุณาตรวจสอบ permission)</div>";
        }
        
        echo "<div style='margin-top: 30px;'>";
        echo "<a href='.' style='background: #007bff; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px;'>← กลับหน้า Dashboard</a>";
        echo "</div>";
        
        echo "</div>";
        echo "</body></html>";
    } else {
        // --- แสดง Dashboard หลัก ---
        try {
            $dashboard = new AdminDashboard();
            $dashboard->display(); // เรียกใช้เมธอด display() ตามไฟล์ที่คุณอัปโหลดมา
        } catch (Exception $e) {
            echo "<!DOCTYPE html>";
            echo "<html lang='th'><head><meta charset='UTF-8'><title>Dashboard Error</title></head><body>";
            echo "<div style='max-width: 600px; margin: 50px auto; padding: 30px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 10px;'>";
            echo "<h1 style='color: #721c24;'>❌ เกิดข้อผิดพลาดในการแสดง Dashboard</h1>";
            echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p>กรุณาตรวจสอบ:</p>";
            echo "<ul>";
            echo "<li>การตั้งค่าเชื่อมต่อฐานข้อมูลในไฟล์ <code>config.php</code></li>";
            echo "<li>ว่าได้สร้างตารางทั้งหมดโดยใช้ <code>database_setup.sql</code> แล้ว</li>";
            echo "<li>สิทธิ์การเขียน (Write Permission) ของโฟลเดอร์ <code>webhook_logs</code></li>";
            echo "</ul>";
            echo "<p>สำหรับรายละเอียดเพิ่มเติม, กรุณาตรวจสอบ PHP error log ของเซิร์ฟเวอร์</p>";
            echo "<p><a href='?test=1'><strong>คลิกที่นี่เพื่อเรียกใช้หน้าตรวจสอบระบบ</strong></a></p>";
            echo "</div>";
            echo "</body></html>";
        }
    }
}
?>
