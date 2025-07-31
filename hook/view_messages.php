<?php
// view_messages.php - ดูข้อความของผู้ใช้
require_once 'config.php';
require_once 'admin_dashboard.php';

if (!isset($_GET['user_id']) || !isset($_GET['platform'])) {
    die('Missing user_id or platform parameter');
}

$userId = $_GET['user_id'];
$platform = $_GET['platform'];
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;

$userManager = new UserManager();
$messages = $userManager->getUserMessages($userId, $platform, $limit);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ข้อความของผู้ใช้ - <?php echo htmlspecialchars($userId); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { border-bottom: 3px solid #007bff; padding-bottom: 15px; margin-bottom: 20px; }
        .user-info { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .message { background: #f9f9f9; border-left: 4px solid #007bff; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .message-header { font-size: 12px; color: #666; margin-bottom: 8px; }
        .message-text { font-size: 14px; line-height: 1.5; }
        .message-meta { font-size: 11px; color: #999; margin-top: 8px; }
        .no-messages { text-align: center; color: #666; padding: 40px; }
        .back-btn { background: #6c757d; color: white; text-decoration: none; padding: 8px 16px; border-radius: 4px; margin-right: 10px; }
        .back-btn:hover { background: #5a6268; }
        .platform-badge { background: #007bff; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; }
        .stats { display: flex; gap: 20px; margin: 15px 0; }
        .stat { text-align: center; }
        .stat-number { font-size: 24px; font-weight: bold; display: block; }
        .stat-label { font-size: 12px; opacity: 0.9; }
        .message-type { background: #28a745; color: white; padding: 1px 6px; border-radius: 10px; font-size: 10px; }
        .filter-bar { background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .filter-bar select, .filter-bar input { margin: 0 10px; padding: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💬 ข้อความของผู้ใช้</h1>
            <a href="index.php" class="back-btn">← กลับหน้าหลัก</a>
        </div>
        
        <div class="user-info">
            <h2>👤 ข้อมูลผู้ใช้</h2>
            <div class="stats">
                <div class="stat">
                    <span class="stat-number"><?php echo count($messages); ?></span>
                    <span class="stat-label">ข้อความที่แสดง</span>
                </div>
                <div class="stat">
                    <span class="stat-number"><?php echo htmlspecialchars($platform); ?></span>
                    <span class="stat-label">แพลตฟอร์ม</span>
                </div>
            </div>
            <p><strong>User ID:</strong> <code><?php echo htmlspecialchars($userId); ?></code></p>
        </div>

        <div class="filter-bar">
            <strong>🔍 ตัวกรอง:</strong>
            <select onchange="changeLimit(this.value)">
                <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50 ข้อความล่าสุด</option>
                <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100 ข้อความล่าสุด</option>
                <option value="200" <?php echo $limit == 200 ? 'selected' : ''; ?>>200 ข้อความล่าสุด</option>
                <option value="500" <?php echo $limit == 500 ? 'selected' : ''; ?>>500 ข้อความล่าสุด</option>
            </select>
        </div>

        <h2>📝 ประวัติข้อความ</h2>
        
        <?php if (empty($messages)): ?>
            <div class="no-messages">
                <h3>📭 ไม่มีข้อความ</h3>
                <p>ผู้ใช้คนนี้ยังไม่เคยส่งข้อความมา</p>
            </div>
        <?php else: ?>
            <?php foreach ($messages as $message): ?>
                <div class="message">
                    <div class="message-header">
                        <span class="platform-badge"><?php echo htmlspecialchars($message['platform']); ?></span>
                        <span class="message-type"><?php echo htmlspecialchars($message['message_type']); ?></span>
                        <span style="float: right;">
                            📅 <?php echo date('d/m/Y H:i:s', strtotime($message['created_at'])); ?>
                        </span>
                    </div>
                    
                    <div class="message-text">
                        <?php if (!empty($message['message_text'])): ?>
                            <?php echo nl2br(htmlspecialchars($message['message_text'])); ?>
                        <?php else: ?>
                            <em style="color: #999;">ไม่มีข้อความ (อาจเป็นไฟล์หรือสติกเกอร์)</em>
                        <?php endif; ?>
                    </div>
                    
                    <div class="message-meta">
                        <?php if (!empty($message['timestamp'])): ?>
                            🕒 Timestamp: <?php echo date('d/m/Y H:i:s', $message['timestamp'] / 1000); ?>
                        <?php endif; ?>
                        
                        <?php if (!empty($message['reply_token'])): ?>
                            | 🔑 Token: <code><?php echo substr($message['reply_token'], 0, 20); ?>...</code>
                        <?php endif; ?>
                        
                        <?php if (!empty($message['webhook_event_id'])): ?>
                            | 🆔 Event: <code><?php echo htmlspecialchars($message['webhook_event_id']); ?></code>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div style="text-align: center; margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
                <p>📊 แสดง <?php echo count($messages); ?> ข้อความจากทั้งหมด</p>
                <p>หากต้องการดูข้อความเพิ่มเติม ให้เปลี่ยนจำนวนในตัวกรองด้านบน</p>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="index.php" class="back-btn">← กลับหน้าหลัก</a>
        </div>
    </div>

    <script>
        function changeLimit(limit) {
            const url = new URL(window.location);
            url.searchParams.set('limit', limit);
            window.location = url;
        }
    </script>
</body>
</html>