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

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    // คำสั่ง SQL สำหรับลบข้อมูลตาม ID ที่ได้รับ
    $db = new SQLite3('data.db');
    $stmt = $db->prepare('DELETE FROM ads WHERE id = :id');
    $stmt->bindValue(':id', $delete_id, SQLITE3_INTEGER);
    $stmt->execute();
    $db->close();
    echo "ลบโฆษณาเรียบร้อย!";
}

// ดึงข้อมูลโฆษณาทั้งหมดจากฐานข้อมูล
$query = "SELECT * FROM ads";
$result = $db->query($query);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แสดงข้อมูลโฆษณา</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        img {
            max-width: 100px;
            height: auto;
            border-radius: 5px;
        }
    </style>
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

    <div class="content">
        <h1>ข้อมูลโฆษณา</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>รูปภาพ</th>
                    <th>ลิงก์</th>
                    <th>การกระทำ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // แสดงข้อมูลในแต่ละแถว
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $image_data = base64_encode($row['image_data']); // แปลงข้อมูลภาพเป็น Base64
                    $link = htmlspecialchars($row['link']); // ป้องกัน XSS
                    $edit_url = "edit_ad.php?id=" . $row['id']; // ลิงก์ไปยังหน้าแก้ไข
                    $delete_url = "view_ads.php?delete_id=" . $row['id']; // ลิงก์ไปยังหน้าเดียวกันเพื่อทำการลบ
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td><img src='data:image/jpeg;base64,$image_data' alt='Ad Image'></td>";
                    echo "<td><a href='$link' target='_blank'>$link</a></td>";
                    echo "<td>
                            <a href='$edit_url'>แก้ไข</a> | 
                            <a href='$delete_url' onclick='return confirm(\"คุณต้องการลบข้อมูลนี้ใช่ไหม?\");'>ลบ</a>
                          </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
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
