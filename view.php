<?php
session_start();
// รวมไฟล์ database.php เพื่อให้สามารถใช้ฟังก์ชันจากไฟล์นี้ได้
require_once('database.php');
// เชื่อมต่อกับฐานข้อมูล
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

// ใช้ฟังก์ชัน award_type สำหรับแบ่งรางวัล
$six_digit_awards = award_type($db, 'six_digit_numbers');
$two_digit_awards = award_type($db, 'two_digit_numbers');
$first_three_digit_awards = award_type($db, 'first_three_digit_numbers');
$last_three_digit_awards = award_type($db, 'last_three_digit_numbers');

// ตรวจสอบการลบข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $ids_to_delete = $_POST['ids'] ?? [];
    foreach ($ids_to_delete as $id) {
        $db->exec("DELETE FROM six_digit_numbers WHERE id = $id");
        $db->exec("DELETE FROM two_digit_numbers WHERE id = $id");
        $db->exec("DELETE FROM first_three_digit_numbers WHERE id = $id");
        $db->exec("DELETE FROM last_three_digit_numbers WHERE id = $id");
    }
    header("Location: view.php"); // โหลดใหม่เพื่อไม่ให้มีการส่งฟอร์มซ้ำ
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ดูข้อมูลตัวเลข</title>
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

<div class="content">    
<h1>ข้อมูลตัวเลข 6 หลัก</h1>
<form method="POST">
    <table border="1">
        <tr>
            <th><input type="checkbox" onclick="toggle(this)"></th>
            <th>ID</th>
            <th>หมายเลข</th>
            <th>วันที่</th>
            <th>ลำดับ</th>
            <th>การจัดการ</th>
        </tr>
        <?php foreach ($six_digit_awards as $award): ?>
        <tr>
            <td><input type="checkbox" name="ids[]" value="<?php echo $award['id']; ?>"></td>
            <td><?php echo $award['id']; ?></td>
            <td><?php echo str_pad($award['number'], 6, '0', STR_PAD_LEFT); ?></td>
            <td><?php
                $date_parts = explode('-', $award['date']);
                echo $date_parts[2] . '/' . $date_parts[1] . '/' . $date_parts[0];
            ?></td>
            <td><?php echo "รางวัลที่ " . $award['award_count']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <button type="submit" name="delete">ลบที่เลือก</button>
</form>

<h1>ข้อมูลตัวเลข 2 หลัก</h1>
<form method="POST">
    <table border="1">
        <tr>
            <th><input type="checkbox" onclick="toggle(this)"></th>
            <th>ID</th>
            <th>หมายเลข</th>
            <th>วันที่</th>
            <th>ลำดับ</th>
            <th>การจัดการ</th>
        </tr>
        <?php foreach ($two_digit_awards as $award): ?>
        <tr>
            <td><input type="checkbox" name="ids[]" value="<?php echo $award['id']; ?>"></td>
            <td><?php echo $award['id']; ?></td>
            <td><?php echo str_pad($award['number'], 2, '0', STR_PAD_LEFT); ?></td>
            <td><?php
                $date_parts = explode('-', $award['date']);
                echo $date_parts[2] . '/' . $date_parts[1] . '/' . $date_parts[0];
            ?></td>
            <td><?php echo "รางวัลที่ " . $award['award_count']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <button type="submit" name="delete">ลบที่เลือก</button>
</form>

<h1>ข้อมูลตัวเลข 3 หลักหน้า</h1>
<form method="POST">
    <table border="1">
        <tr>
            <th><input type="checkbox" onclick="toggle(this)"></th>
            <th>ID</th>
            <th>หมายเลข</th>
            <th>วันที่</th>
            <th>ลำดับรางวัล</th>
            <th>การจัดการ</th>
        </tr>
        <?php foreach ($first_three_digit_awards as $award): ?>
        <tr>
            <td><input type="checkbox" name="ids[]" value="<?php echo $award['id']; ?>"></td>
            <td><?php echo $award['id']; ?></td>
            <td><?php echo str_pad($award['number'], 3, '0', STR_PAD_LEFT); ?></td>
            <td><?php
                $date_parts = explode('-', $award['date']);
                echo $date_parts[2] . '/' . $date_parts[1] . '/' . $date_parts[0];
            ?></td>
            <td><?php echo "รางวัลที่ " . $award['award_count']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <button type="submit" name="delete">ลบที่เลือก</button>
</form>

<h1>ข้อมูลตัวเลข 3 หลักท้าย</h1>
<form method="POST">
    <table border="1">
        <tr>
            <th><input type="checkbox" onclick="toggle(this)"></th>
            <th>ID</th>
            <th>หมายเลข</th>
            <th>วันที่</th>
            <th>ลำดับ</th>
            <th>การจัดการ</th>
        </tr>
        <?php foreach ($last_three_digit_awards as $award): ?>
        <tr>
            <td><input type="checkbox" name="ids[]" value="<?php echo $award['id']; ?>"></td>
            <td><?php echo $award['id']; ?></td>
            <td><?php echo str_pad($award['number'], 3, '0', STR_PAD_LEFT); ?></td>
            <td><?php
                $date_parts = explode('-', $award['date']);
                echo $date_parts[2] . '/' . $date_parts[1] . '/' . $date_parts[0];
            ?></td>
            <td><?php echo "รางวัลที่ " . $award['award_count']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <button type="submit" name="delete">ลบที่เลือก</button>
</form>

</div>

    <!-- Sidebar Right -->
    <!-- <div class="sidebar-right"></div> -->
</div>

</div>

<footer>
    <p>&copy; 2024 สถิติสลากกินแบ่ง. All rights reserved.</p>
</footer>
<?php
    // ปิดการเชื่อมต่อ
    $db->close();
?>
</body>
</html>
