<?php

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'hospital_line';

// --- เชื่อมต่อฐานข้อมูล ---
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// --- รับค่าจากฟอร์ม ---
$patient_hn = $_POST['patient_hn'];
$patient_name = $_POST['patient_name'];
$line_user_id = $_POST['line_user_id'];

// --- เตรียมคำสั่ง SQL เพื่อป้องกัน SQL Injection ---
$stmt = $conn->prepare("INSERT INTO patients (patient_hn, patient_name, line_user_id) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $patient_hn, $patient_name, $line_user_id);

// --- รันคำสั่งและจัดการผลลัพธ์ ---
if ($stmt->execute()) {
    // หากสำเร็จ ให้ redirect กลับไปหน้าแรกพร้อมสถานะ success
    header("Location: index.php?status=success");
    exit();
} else {
    // หากผิดพลาด (อาจจะเพราะ User ID ซ้ำ)
    echo "<h1>เกิดข้อผิดพลาด!</h1>";
    echo "<p>ไม่สามารถลงทะเบียนผู้ใช้งานได้ อาจเป็นเพราะ LINE User ID นี้มีอยู่ในระบบแล้ว</p>";
    echo "<p>Error: " . htmlspecialchars($stmt->error) . "</p>";
    echo '<a href="index.php">กลับไปหน้าลงทะเบียน</a>';
}

$stmt->close();
$conn->close();
?>