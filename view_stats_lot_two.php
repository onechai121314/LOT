<?php
session_start();
// เริ่มต้นการเชื่อมต่อฐานข้อมูล
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

// ดึงข้อมูลจำนวนครั้งในแต่ละวัน ของ lot_six.php
$query = "SELECT view_date, view_count FROM lot_two_views ORDER BY view_date DESC";
$result = $db->query($query);

// เก็บข้อมูลใน array เพื่อแสดงผล
$LotTwoViews = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $LotTwoViews[] = $row;
}

// เก็บข้อมูลใน array เพื่อแสดงผล
$LotTwoViews = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $LotTwoViews[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $deleteDate = $_POST['delete_date'];

    // ตรวจสอบและลบข้อมูลในฐานข้อมูล
    $stmt = $db->prepare("DELETE FROM lot_two_views WHERE view_date = :view_date");
    $stmt->bindValue(':view_date', $deleteDate, SQLITE3_TEXT);

    if ($stmt->execute()) {
        echo "<script>alert('ลบข้อมูลสำเร็จ!'); window.location.href = window.location.href;</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการลบข้อมูล!');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถิติการดูหน้าเว็บ</title>
    <link rel="stylesheet" href="styles.css"> <!-- เพิ่มไฟล์ CSS ถ้าจำเป็น -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- สำหรับกราฟ -->
</head>
<style>
        #viewsChart {
        max-width: 1000px; /* เพิ่มความกว้างของพื้นที่กราฟ */
        height: 1000px; /* ปรับความสูงให้สัมพันธ์กับเนื้อหา */
        margin: 0 auto;
        }
        canvas {
        display: block;
        max-width: 100%; /* ทำให้กราฟยืดขยายได้ตามพื้นที่ */
        height: 1000px; /* กำหนดความสูง */
        }
</style>
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
        
    <h1>สถิติการเข้าชมหน้าเว็บของ สถิติเลข 2 ตัว</h1>
    
    <!-- แสดงผลในรูปแบบกราฟ -->
    <h2>ข้อมูลการเข้าชม (กราฟ)</h2>
    <canvas id="viewsChart" width="400" height="200"></canvas>

    <!-- แสดงผลในรูปแบบตาราง -->
    <h2>ข้อมูลการเข้าชม (ตาราง)</h2>
    <table border="1">
        <thead>
            <tr>
                <th>วันที่</th>
                <th>จำนวนครั้ง</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($LotTwoViews as $view): ?>
                <tr>
                    <td><?php echo $view['view_date']; ?></td>
                    <td><?php echo $view['view_count']; ?></td>
                    <td>
                        <form method="POST" action="">
                            <input type="hidden" name="delete_date" value="<?php echo $view['view_date']; ?>">
                            <button type="submit" name="delete" onclick="return confirm('ยืนยันการลบข้อมูล?');">ลบ</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    </div>

    <!-- Sidebar Right -->
    <!-- <div class="sidebar-right"></div> -->
</div>

<footer>
    <p>&copy; 2024. All rights reserved.</p>
</footer>

    <script>
        const ctx = document.getElementById('viewsChart').getContext('2d');
        const viewsChart = new Chart(ctx, {
            type: 'bar', // เปลี่ยนเป็นกราฟแท่ง
            data: {
                labels: <?php echo json_encode(array_column($LotTwoViews, 'view_date')); ?>, // วันที่
                datasets: [{
                    label: 'จำนวนครั้ง',
                    data: <?php echo json_encode(array_column($LotTwoViews, 'view_count')); ?>, // จำนวนครั้ง
                    backgroundColor: 'rgba(75, 192, 192, 0.5)', // สีของแท่งกราฟ
                    borderColor: 'rgba(75, 192, 192, 1)', // สีขอบของแท่งกราฟ
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true, // ทำให้กราฟยืดหยุ่นตามหน้าจอ
                scales: {
                    x: { // การตั้งค่าแกน X
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'วันที่'
                        }
                    },
                    y: { // การตั้งค่าแกน Y
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'จำนวนครั้ง'
                        }
                    }
                },
                plugins: {
                    legend: { display: true },
                    title: { display: true, text: 'จำนวนครั้งในแต่ละวัน (กราฟแท่ง)' }
                }
            }
        });
    </script>
</body>
</html>
