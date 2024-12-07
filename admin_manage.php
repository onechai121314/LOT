<?php
session_start();
$db = new SQLite3('data.db');

// ตรวจสอบการเข้าสู่ระบบ
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

// เพิ่มผู้ดูแลระบบใหม่
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_admin'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $db->prepare('INSERT INTO admins (username, password) VALUES (:username, :password)');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->bindValue(':password', $hashed_password, SQLITE3_TEXT);

        if ($stmt->execute()) {
            $message = "เพิ่มผู้ดูแลระบบสำเร็จ!";
        } else {
            $message = "ชื่อผู้ใช้นี้ถูกใช้ไปแล้ว!";
        }
    } else {
        $message = "กรุณากรอกข้อมูลให้ครบถ้วน!";
    }
}

// ลบผู้ดูแลระบบ
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    $stmt = $db->prepare('DELETE FROM admins WHERE id = :id');
    $stmt->bindValue(':id', $delete_id, SQLITE3_INTEGER);

    if ($stmt->execute()) {
        $message = "ลบผู้ดูแลระบบสำเร็จ!";
    } else {
        $message = "เกิดข้อผิดพลาดในการลบผู้ดูแลระบบ!";
    }
}

// ดึงข้อมูลผู้ดูแลระบบทั้งหมด
$result = $db->query('SELECT * FROM admins');
$admins = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $admins[] = $row;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการผู้ดูแลระบบ</title>
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
    <h1>จัดการผู้ดูแลระบบ</h1>
    <?php if (isset($message)) { echo "<p style='color: green;'>$message</p>"; } ?>

    <!-- ฟอร์มเพิ่มผู้ดูแลระบบ -->
    <form method="POST">
        <h2>เพิ่มผู้ดูแลระบบใหม่</h2>
        <label for="username">ชื่อผู้ใช้:</label>
        <input type="text" name="username" id="username" required>
        <br>
        <label for="password">รหัสผ่าน:</label>
        <input type="password" name="password" id="password" required>
        <br>
        <button type="submit" name="add_admin">เพิ่ม</button>
    </form>

    <!-- แสดงรายการผู้ดูแลระบบ -->
    <h2>รายการผู้ดูแลระบบ</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>ชื่อผู้ใช้</th>
            <th>การกระทำ</th>
        </tr>
        <?php foreach ($admins as $admin): ?>
        <tr>
            <td><?php echo $admin['id']; ?></td>
            <td><?php echo htmlspecialchars($admin['username']); ?></td>
            <td><?php echo htmlspecialchars($admin['password']); ?></td>
            <td>
                <a href="?delete_id=<?php echo $admin['id']; ?>" onclick="return confirm('คุณต้องการลบผู้ใช้นี้หรือไม่?');">ลบ</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <br>
    <a href="admin_page.php">กลับไปยังหน้าผู้ดูแลระบบ</a>
</div> <!-- Content -->

<!-- Sidebar Right -->
<!-- <div class="sidebar-right"></div> -->
</div> <!-- container -->


<footer>
    <p>&copy; 2024 สถิติสลากกินแบ่ง. All rights reserved.</p>
</footer>
</body>
</html>
