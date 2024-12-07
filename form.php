<?php
session_start();
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
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กรอกข้อมูลตัวเลข</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            flatpickr(".date_input_date", {
                dateFormat: "d/m/Y", // กำหนดรูปแบบวันที่เป็น DD/MM/YYYY
                allowInput: true, // อนุญาตให้กรอกวันที่ได้
            });
        });

        function validateDate(input) {
            const datePattern = /^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/(19|20)\d\d$/;
            if (input.value && !datePattern.test(input.value)) {
                alert("กรุณากรอกวันที่ในรูปแบบ DD/MM/YYYY");
                input.value = "";
            }
        }
    </script>
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
    <h1>กรอกข้อมูลตัวเลข</h1>
    <form method="post" action="database.php">
        <label for="number_type">เลือกประเภทตัวเลข:</label>
        <select id="number_type" name="number_type" required>
            <option value="">-- กรุณาเลือก --</option>
            <option value="six_digit">ตัวเลข 6 หลัก</option>
            <option value="two_digit">ตัวเลข 2 หลัก</option>
            <option value="first_three_digit">ตัวเลข 3 หลักหน้า</option>
            <option value="last_three_digit">ตัวเลข 3 หลักท้าย</option>
        </select>
        <br><br>

        <!-- กรอกข้อมูล 5 ตัว -->
        <div id="numbersContainer">
            <h2>กรอกข้อมูลตัวเลขและวันที่:</h2>
            <?php for ($i = 1; $i <= 24; $i++): ?>
                <label for="number_input_<?php echo $i; ?>">หมายเลข <?php echo $i; ?>:</label>
                <input type="number" id="number_input_<?php echo $i; ?>" name="number_input[]" >
                <label for="date_input_<?php echo $i; ?>">วันที่ <?php echo $i; ?> (DD/MM/YYYY):</label>
                <input type="text" class="date_input_date" id="date_input_<?php echo $i; ?>" name="date_input_date[]" placeholder="DD/MM/YYYY" onblur="validateDate(this)">
                <br><br>
            <?php endfor; ?>
        </div>

        <input type="submit" value="บันทึกข้อมูล">
    </form>
</div>

<!-- Sidebar Right -->
<!-- <div class="sidebar-right"></div> -->
</div>

<!-- footer -->
<footer>
    <p>&copy; 2024 สถิติสลากกินแบ่ง. All rights reserved.</p>
</footer>
 

</body>
</html>
