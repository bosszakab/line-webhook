<?php
// ค่าตั้งค่าฐานข้อมูล
$db_host = 'localhost';
$db_user = 'root'; // User เริ่มต้นของ XAMPP
$db_pass = '';     // Password เริ่มต้นของ XAMPP คือค่าว่าง
$db_name = 'hospital_line';

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// รับค่าจากฟอร์ม
$user_id = $_POST['user_id'];
$message = $_POST['message'];
$send_at = $_POST['send_at'];

// เตรียมคำสั่ง SQL
$stmt = $conn->prepare("INSERT INTO scheduled_notifications (patient_user_id, message, send_at) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $user_id, $message, $send_at);

// รันคำสั่งและแสดงผล
if ($stmt->execute()) {
    echo "<h1>บันทึกการแจ้งเตือนเรียบร้อยแล้ว!</h1>";
    echo "<p>ระบบจะส่งข้อความไปยัง " . htmlspecialchars($user_id) . " ในเวลา " . htmlspecialchars($send_at) . "</p>";
    echo '<a href="index.html">กลับไปหน้าแรก</a>';
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>