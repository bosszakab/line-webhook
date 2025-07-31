<?php
// admin_dashboard.php - คลาสสำหรับสร้างหน้า Dashboard (เวอร์ชันแก้ไข)

// เรียกใช้ไฟล์ที่จำเป็นก่อนเสมอ
require_once 'user_manager.php';

class AdminDashboard {
    private UserManager $userManager;

    public function __construct() {
        // สร้าง instance ของ UserManager เพื่อใช้ดึงข้อมูล
        $this->userManager = new UserManager();
    }

    /**
     * เมธอดหลักสำหรับสร้างและแสดงผลหน้า Dashboard ทั้งหมด
     */
    public function display(): void {
        // ดึงข้อมูลที่จำเป็นทั้งหมดก่อนเริ่มแสดงผล
        $platformFilter = htmlspecialchars($_GET['platform'] ?? '');
        $users = $this->userManager->getAllUsers($platformFilter ?: null);
        $stats = $this->userManager->getUserStats();
        
        // เรียกใช้ Template ส่วนหัว
        include 'templates/header.php';
        
        // แสดงส่วนสถิติ
        $this->displayStats($stats);
        
        // แสดงส่วนตารางผู้ใช้งาน
        $this->displayUserTable($users, $platformFilter);

        // เรียกใช้ Template ส่วนท้าย
        include 'templates/footer.php';
    }

    /**
     * แสดงผลส่วนสถิติ (Stat Cards)
     */
    private function displayStats(array $stats): void {
        echo "<h2><i class='fa-solid fa-chart-pie'></i> ภาพรวมระบบ</h2>";
        echo "<a href='../index.php' class='btn btn-primary'><i class='fa-solid fa-users'></i>กลับไปยังหน้าลงทะเบียน</a>&nbsp;&nbsp;";
        echo "<a href='index.php?test=1' class='btn btn-primary'><i class='fa-solid fa-plug'></i>ทดสอบการเชื่อมต่อ</a><hr>";
        echo "<div class='stats-grid'>";
        
        if (empty($stats)) {
            echo "<p>ยังไม่มีข้อมูลสถิติ</p>";
        } else {
            foreach ($stats as $stat) {
                $platformName = htmlspecialchars(ucfirst($stat['platform']));
                $icon = 'fa-brands fa-' . strtolower($platformName);
                if ($platformName === 'Custom') {
                    $icon = 'fa-solid fa-cube'; // ไอคอนสำหรับ Custom platform
                }

                echo "<div class='stat-card'>";
                echo "<h3><i class='{$icon}'></i> {$platformName}</h3>";
                echo "<p>" . number_format($stat['user_count']) . " <small>Users</small></p>";
                echo "<p>" . number_format($stat['total_messages']) . " <small>Messages</small></p>";
                echo "</div>";
            }
        }
        echo "</div>";
    }

    /**
     * แสดงผลตารางรายชื่อผู้ใช้งาน
     */
    private function displayUserTable(array $users, string $platformFilter): void {
        echo "<h2><i class='fa-solid fa-users'></i> รายชื่อผู้ใช้งาน</h2>";

        // Toolbar สำหรับกรองข้อมูล
        echo "<div class='toolbar'>";
        echo "<form method='get' action='index.php' class='filter-form'>";
        echo "<select name='platform' onchange='this.form.submit()'>";
        echo "<option value=''>ทุกแพลตฟอร์ม</option>";
        $platforms = ['line', 'facebook', 'custom'];
        foreach ($platforms as $p) {
            $selected = ($platformFilter === $p) ? 'selected' : '';
            echo "<option value='{$p}' {$selected}>" . ucfirst($p) . "</option>";
        }
        echo "</select>";
        echo "</form></div>";

        // ตารางข้อมูล
        echo "<div class='table-wrapper'><table>";
        echo "<thead><tr><th>Platform</th><th>User ID</th><th>First Seen</th><th>Last Seen</th><th>Messages</th><th>Action</th></tr></thead>";
        echo "<tbody>";
        
        if (empty($users)) {
            echo "<tr><td colspan='6' style='text-align:center; padding: 2rem;'>ไม่พบข้อมูลผู้ใช้</td></tr>";
        } else {
            foreach ($users as $user) {
                // สร้างลิงก์ไปยังหน้าดูข้อความ
                $viewUrl = 'view_messages.php?user_id=' . urlencode($user['user_id']) . '&platform=' . urlencode($user['platform']);
                
                echo "<tr>";
                echo "<td>" . htmlspecialchars($user['platform']) . "</td>";
                echo "<td class='user-id-cell'>" . htmlspecialchars($user['user_id']) . "</td>";
                echo "<td>" . htmlspecialchars($user['first_seen']) . "</td>";
                echo "<td>" . htmlspecialchars($user['last_seen']) . "</td>";
                echo "<td>" . number_format($user['message_count']) . "</td>";
                echo "<td><a href='{$viewUrl}' class='btn btn-primary'><i class='fa-solid fa-comments'></i> ดูข้อความ</a></td>";
                echo "</tr>";
            }
        }
        
        echo "</tbody></table></div>";
    }
}
?>
