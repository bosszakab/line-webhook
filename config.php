<?php
// config.php
// ไฟล์สำหรับเก็บการตั้งค่าทั้งหมดของระบบ

// เปิดใช้งาน error reporting เพื่อช่วยในการดีบัก
// ในเวอร์ชันใช้งานจริง (Production) ควรตั้งค่า display_errors เป็น 0
error_reporting(E_ALL);
ini_set('display_errors', 1);


// ===================================================================
// 1. การตั้งค่าฐานข้อมูล (Database Configuration)
// ===================================================================
$db_config = [
    'host' => 'localhost',  // โดยทั่วไปคือ 'localhost'
    'user' => 'root',       // ชื่อผู้ใช้ฐานข้อมูล (สำหรับ XAMPP มักจะเป็น 'root')
    'pass' => '',          // รหัสผ่าน (สำหรับ XAMPP มักจะเป็นค่าว่าง)
    'name' => 'hospital_line' // ชื่อฐานข้อมูลของคุณ
];


// ===================================================================
// 2. การตั้งค่า LINE Messaging API
// ===================================================================

// (สำคัญมาก!) นำ Channel Access Token (Long-lived) ของคุณ
// จาก LINE Developers Console มาใส่ที่นี่
define('LINE_CHANNEL_ACCESS_TOKEN','vOFSE7a62MaT6Rg3bMawrPHf5q0uvBSCT6uDCCMiUzxXLmF96yCCeVtT3BHLjOwajR03IM91aWZzlicGq0eqSmQWCv9TgtFFcn2c1ZZw/yQ2he4TYacHc5CwpZGg2MgcjbuNOuBHDc7p/jkmycTBMgdB04t89/1O/w1cDnyilFU=');

?>