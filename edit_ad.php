<?php
session_start();
// เชื่อมต่อฐานข้อมูล
$db = new SQLite3('data.db');

// สำหรับ login เฉพาะ admin เท่านั้น 
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php'); // เปลี่ยนเส้นทางไปที่หน้า login
    exit;
}
// ฟังก์ชัน Logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}

// ตรวจสอบว่าได้ id หรือไม่
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // ดึงข้อมูลโฆษณาที่ต้องการแก้ไข
    $stmt = $db->prepare("SELECT * FROM ads WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $ad = $result->fetchArray(SQLITE3_ASSOC);
}

// ตรวจสอบการส่งฟอร์มแก้ไข
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_id = $_POST['id'];
    $link = $_POST['link'];

    // ตรวจสอบว่ามีการอัพโหลดไฟล์หรือไม่
    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_data = file_get_contents($_FILES['image']['tmp_name']);
    } else {
        $image_data = $ad['image_data'];
    }

    // อัพเดตข้อมูลในฐานข้อมูล
    $stmt = $db->prepare("UPDATE ads SET id = :new_id, link = :link, image_data = :image_data WHERE id = :current_id");
    $stmt->bindValue(':new_id', $new_id, SQLITE3_INTEGER);
    $stmt->bindValue(':link', $link, SQLITE3_TEXT);
    $stmt->bindValue(':image_data', $image_data, SQLITE3_BLOB);
    $stmt->bindValue(':current_id', $id, SQLITE3_INTEGER);
    $stmt->execute();

    // รีไดเรกต์กลับไปที่หน้า view_ads.php
    header("Location: view_ads.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลโฆษณา</title>
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
                <!-- ฟอร์ม Logout -->
            <form method="POST" style="text-align: right;">
                <button type="submit" name="logout">ออกจากระบบ</button>
            </form>
        </div>
    </div>

    <!-- Content -->
    <div class="content">

    <h1>แก้ไขข้อมูลโฆษณา</h1>
    <form action="edit_ad.php?id=<?php echo $ad['id']; ?>" method="POST" enctype="multipart/form-data">
    <label for="id">ID:</label>
    <input type="text" id="id" name="id" value="<?php echo htmlspecialchars($ad['id']); ?>" required readonly><br><br>

    <label for="link">ลิงก์:</label>
    <input type="text" id="link" name="link" value="<?php echo htmlspecialchars($ad['link']); ?>" required><br><br>

    <label for="image">เลือกภาพใหม่:</label>
    <input type="file" id="image" name="image" accept="image/*"><br><br>

    <img src="data:image/jpeg;base64,<?php echo base64_encode($ad['image_data']); ?>" alt="Current Image" width="100"><br><br>

    <input type="submit" value="บันทึกการแก้ไข">
    </form>

    </div>

    <!-- Sidebar Right -->
    <!-- <div class="sidebar-right"></div> -->
</div>

<footer>
    <p>&copy; 2024 สถิติสลากกินแบ่ง. All rights reserved.</p>
</footer>
</body>
</html>

<?php
// ปิดการเชื่อมต่อฐานข้อมูล
$db->close();
?>
