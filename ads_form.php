<?php
session_start();
// เชื่อมต่อกับฐานข้อมูล SQLite
$db = new SQLite3('data.db');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php'); // ถ้ายังไม่ได้เข้าสู่ระบบ ให้เปลี่ยนเส้นทางไปที่หน้า login
    exit;
}
// ฟังก์ชัน Logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}

// ตรวจสอบการส่งฟอร์มโฆษณา
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES['ad_image']) && $_FILES['ad_image']['error'] === UPLOAD_ERR_OK) {
        // รับค่าลิงก์จากฟอร์มและกรองข้อมูล
        $ad_link = filter_var($_POST['ad_link'], FILTER_SANITIZE_URL);
        $image_temp = $_FILES['ad_image']['tmp_name'];
        $image_data = file_get_contents($image_temp); // อ่านข้อมูลรูปภาพ

        // ตรวจสอบข้อมูลก่อนบันทึก
        if ($image_data && $ad_link) {
            // เตรียมคำสั่ง SQL
            $stmt = $db->prepare("INSERT INTO ads (image_data, link) VALUES (:image_data, :link)");
            $stmt->bindValue(':image_data', $image_data, SQLITE3_BLOB);
            $stmt->bindValue(':link', $ad_link, SQLITE3_TEXT);

            // บันทึกข้อมูลและตรวจสอบผลลัพธ์
            if ($stmt->execute()) {
                echo "<script>alert('บันทึกข้อมูลโฆษณาสำเร็จ'); window.location.href='view_ads.php';</script>";
            } else {
                echo "<script>alert('ไม่สามารถบันทึกโฆษณาได้');</script>";
            }
        } else {
            echo "<script>alert('ข้อมูลไม่สมบูรณ์');</script>";
        }
    } else {
        echo "<script>alert('กรุณาเลือกไฟล์รูปภาพที่ต้องการอัปโหลด');</script>";
    }
}

// ปิดการเชื่อมต่อฐานข้อมูล
$db->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มโฆษณา</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <h1>LOT ADMIN</h1>
</header>

<!-- Main Container -->
<div class="container">
    <!-- Sidebar Left -->
    <div class="sidebar-left">
        <div class="menu">
            <a href="admin_page.php">หน้าแรก(สำหรับผู้ดูแล)</a>
            <a href="form.php">กรอกข้อมูล</a>
            <a href="view.php">ดูข้อมูล</a>
            <a href="ads_form.php">กรอกข้อมูลโฆษณา</a>
            <a href="view_ads.php">จัดการข้อมูลโฆษณา</a>
            <a href="view_stats_lot_page.php">LOT PAGE</a>
            <a href="view_stats_lot_six.php">LOT SIX</a>
            <a href="view_stats_lot_two.php">LOT TWO</a>
            <a href="view_stats_lot_F_three.php">LOT FRIST THREE</a>
            <a href="view_stats_lot_L_three.php">LOT LAST THREE</a>
            <a href="view_stats_lot_calculate.php">LOT CALCULATE</a>
            <a href="view_stats_lot_game.php">LOT GAME</a>
            <a href="admin_manage.php">หน้าจัดการ admin</a>
        </div>
    </div>

    <div class="content">
    <h1>เพิ่มโฆษณา</h1>
    <form action="ads_form.php" method="post" enctype="multipart/form-data">
        <label for="ad_image">เลือกภาพโฆษณา:</label>
        <input type="file" name="ad_image" id="ad_image" accept="image/*" required><br><br>

        <label for="ad_link">ลิงก์โฆษณา:</label>
        <input type="url" name="ad_link" id="ad_link" placeholder="https://example.com" required><br><br>

        <button type="submit">บันทึกโฆษณา</button>
    </form>
    </div>

    <!-- Sidebar Right -->
    <!-- <div class="sidebar-right"></div> -->
    </div>

</div>

<footer>
    <p>&copy; 2024 สถิติสลากกินแบ่ง. All rights reserved.</p>
</footer>
</body>
</html>
