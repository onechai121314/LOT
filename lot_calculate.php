<?php

// file : lot_page.php

// เชื่อมต่อฐานข้อมูล
$db = new SQLite3('data.db');

// ดึงข้อมูลโฆษณาจากฐานข้อมูล
// กำหนด id ที่ต้องการดึง (สามารถแก้ไขตามที่ต้องการ)
$ids_to_fetch = [11, 12]; // กำหนดให้ดึงข้อมูลจาก id 1 และ 3

// สร้าง query ที่สามารถเลือก id ที่ต้องการ
$query_ads = "SELECT * FROM ads WHERE id IN (" . implode(',', $ids_to_fetch) . ") ORDER BY id DESC"; // ดึงโฆษณาจาก id ที่เลือก
$result = $db->query($query_ads);

// ถ้ามีโฆษณา
$ads = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    // เปลี่ยนข้อมูลรูปภาพจาก BLOB เป็น base64 เพื่อแสดงใน HTML
    $image_data = base64_encode($row['image_data']);
    $ad_link = $row['link'];
    $ads[] = ['image_data' => $image_data, 'ad_link' => $ad_link]; // เก็บข้อมูลใน array
}

// ถ้าไม่พบโฆษณาใดๆ
if (empty($ads)) {
    $image_data = ''; 
    $ad_link = '#'; // กำหนดเป็น # หากไม่มีข้อมูลโฆษณา
    $default_image = 'default_ad.jpg'; // รูปภาพที่กำหนดเมื่อไม่มีโฆษณา
}

// ฟังก์ชันสำหรับดึงข้อมูลจากฐานข้อมูล
function getNumberData($db, $number_type) {
    $query = "SELECT number, COUNT(*) as count FROM $number_type GROUP BY number ORDER BY count DESC";
    $result = $db->query($query);

    $numbers = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $numbers[] = [
            'number' => $row['number'],
            'count' => $row['count']
        ];
    }
    return $numbers;
}

// คำนวณค่าเฉลี่ยความน่าจะเป็นของแต่ละหมายเลข
function calculateProbability($numbers) {
    $total_count = array_sum(array_column($numbers, 'count'));
    $probabilities = [];

    foreach ($numbers as $number_data) {
        // คำนวณความน่าจะเป็นของตัวเลขแต่ละหมายเลข
        $probabilities[] = [
            'number' => $number_data['number'],
            'probability' => ($number_data['count'] / $total_count) * 100 // คำนวณเปอร์เซ็นต์
        ];
    }

    return $probabilities;
}

// เลือกประเภทของตัวเลข (6 หลัก, 2 หลัก, ฯลฯ)
$number_type = $_POST['number_type'] ?? 'six_digit_numbers';

// ดึงข้อมูลจากฐานข้อมูล
$numbers = getNumberData($db, $number_type);
$probabilities = calculateProbability($numbers);

// คำนวณกราฟ
$graph_data = json_encode($probabilities);

// ฟังก์ชันสำหรับคำนวณผลลัพธ์สำหรับเลขที่ผู้ใช้กรอก
$result = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_number'])) {
    $user_number = $_POST['user_number'];
    $found = false;

    foreach ($numbers as $number_data) {
        if ($number_data['number'] == $user_number) {
            $result = "หมายเลข $user_number เคยออกมาแล้ว $number_data[count] ครั้ง";
            $found = true;
            break;
        }
    }

    if (!$found) {
        $result = "หมายเลข $user_number ไม่มีในฐานข้อมูล.";
    }
}

// ฟังก์ชันในการเพิ่มการเข้าชม
function incrementViewCount($db, $page_name) {
    $today = date('Y-m-d');
    
    // ตรวจสอบว่ามีการบันทึกการเข้าชมในวันที่นี้แล้วหรือยัง
    $stmt = $db->prepare("SELECT * FROM {$page_name}_views WHERE view_date = :view_date");
    $stmt->bindValue(':view_date', $today, SQLITE3_TEXT);
    $result = $stmt->execute();
    
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        // หากมีแล้ว เพิ่มจำนวนการเข้าชม
        $new_count = $row['view_count'] + 1;
        $update_stmt = $db->prepare("UPDATE {$page_name}_views SET view_count = :view_count WHERE view_date = :view_date");
        $update_stmt->bindValue(':view_count', $new_count, SQLITE3_INTEGER);
        $update_stmt->bindValue(':view_date', $today, SQLITE3_TEXT);
        $update_stmt->execute();
    } else {
        // หากยังไม่มีการบันทึกในวันที่นี้ ให้เพิ่มข้อมูลใหม่
        $insert_stmt = $db->prepare("INSERT INTO {$page_name}_views (view_date, view_count) VALUES (:view_date, 1)");
        $insert_stmt->bindValue(':view_date', $today, SQLITE3_TEXT);
        $insert_stmt->execute();
    }
}

// ตัวอย่างการเรียกใช้ฟังก์ชันเมื่อเข้าชมหน้า lot_six.php
incrementViewCount($db, 'lot_calculate');

// ดึงจำนวนคนดูในวันนี้
$queryViews = "SELECT view_count FROM lot_calculate_views WHERE view_date = :view_date";
$stmtViews = $db->prepare($queryViews);
$stmtViews->bindValue(':view_date', date('Y-m-d'), SQLITE3_TEXT);
$resultViews = $stmtViews->execute();

$viewsToday = 0;
if ($row = $resultViews->fetchArray(SQLITE3_ASSOC)) {
    $viewsToday = $row['view_count'];
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลเลขซ้ำ</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Mitr:wght@300&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="styles_lot.css">
    <style>
      
    </style>
</head>
<body>
<!-- Header -->
<header>
<div class="logo">
    <a href="lot_page.php">
        <img src="logo_lot.png" alt="โลโก้เว็บไซต์">
    </a>
</div>
    <div class="menu-phone menu-toggle" onclick="toggleMenu()">☰ เมนู</div>
        <nav class="menu">
            <ul id="menu" class="menu-list">
                <li><a href="lot_page.php" class="menu-btn">หน้าแรก</a></li>
                <li><a href="lot_six.php">รางวัลเลข 6 ตัว</a></li>
                <li><a href="lot_first_three.php">รางวัลเลขหน้า 3 ตัว</a></li>
                <li><a href="lot_last_three.php">รางวัลเลขท้าย 3 ตัว</a></li>
                <li><a href="lot_two.php">รางวัลเลขท้าย 2 ตัว</a></li>
                <li><a href="lot_calculate.php">ข้อมูลเลขออกซ้ำ</a></li>
                <li><a href="lot_game.php">กล่องสุ่มเลข</a></li>
                <li><a href="lot_about.php">เกี่ยวกับเรา/ติดต่อ</a></li>
            </ul>
        </nav>
    </header>

    <!-- กรอบสำหรับโฆษณา -->
 <div class="advertisement">
        <?php foreach ($ads as $index => $ad): ?>
        <div class="ad-box">
            <?php if ($ad['image_data']): ?>
                <a href="<?php echo htmlspecialchars($ad['ad_link']); ?>" target="_blank">
                    <img src="data:image/jpeg;base64,<?php echo $ad['image_data']; ?>" alt="โฆษณา <?php echo $index + 1; ?>" class="ad-image">
                </a>
            <?php else: ?>
                <a href="<?php echo htmlspecialchars($ad['ad_link']); ?>" target="_blank">
                    <img src="<?php echo $default_image; ?>" alt="โฆษณา <?php echo $index + 1; ?>" class="ad-image">
                </a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

   <!-- เนื้อหาหลัก -->
   <main>
    <section>

    <h1>การคำนวณความน่าจะเป็นของตัวเลข</h1>
        <p>จำนวนการเข้าหน้าเว็บวันนี้ : <?php echo $viewsToday; ?> ครั้ง</p>

        <!-- แสดงกราฟความน่าจะเป็น -->
        <h2>กราฟความน่าจะเป็นของตัวเลข</h2>
        <canvas id="probabilityChart"></canvas>

        
        <!-- แสดงผลลัพธ์ -->
        <?php if ($result): ?>
            <p><strong>ผลลัพธ์:</strong> <?php echo $result; ?></p>
        <?php endif; ?>

        <!-- ฟอร์มกรอกเลข -->
        <form method="POST">
            <label for="number_type">เลือกประเภทของตัวเลข: </label>
            <select name="number_type" id="number_type">
                <option value="six_digit_numbers" <?php echo $number_type == 'six_digit_numbers' ? 'selected' : ''; ?>>6 หลัก</option>
                <option value="two_digit_numbers" <?php echo $number_type == 'two_digit_numbers' ? 'selected' : ''; ?>>2 หลัก</option>
                <option value="first_three_digit_numbers" <?php echo $number_type == 'first_three_digit_numbers' ? 'selected' : ''; ?>>3 หลัก หน้า</option>
                <option value="last_three_digit_numbers" <?php echo $number_type == 'last_three_digit_numbers' ? 'selected' : ''; ?>>3 หลัก ท้าย</option>
            </select>
            <br><br>

            <label for="user_number">กรอกหมายเลขที่ต้องการตรวจสอบ: </label>
            <input type="text" name="user_number" id="user_number">
            <button type="submit">ตรวจสอบ</button>
        </form>
            
    </section>
</main>


<!-- <footer>
    <p>&copy; 2024 สถิติสลากกินแบ่ง. All rights reserved.</p>
</footer> -->

<script>
        function toggleMenu() {
            const menu = document.getElementById('menu');
            menu.classList.toggle('show');
        }
</script>


<script>
    const data = <?php echo $graph_data; ?>;
    const labels = data.map(item => item.number);
    const probabilities = data.map(item => item.probability);

    // ดึงหมายเลขจากฟอร์ม
    const userNumber = '<?php echo isset($_POST['user_number']) ? $_POST['user_number'] : ''; ?>';
    
    // สร้างกราฟ
    const ctx = document.getElementById('probabilityChart').getContext('2d');
    
    // ฟังก์ชันเปลี่ยนสีกราฟ
    function getBackgroundColor(number) {
        return number === userNumber ? 'rgba(255, 99, 132, 0.2)' : 'rgba(60, 179, 113, 0.2)'; // ถ้าหมายเลขตรงจะเป็นสีแดง
    }

    // สร้างกราฟใหม่
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'ความน่าจะเป็น (%)',
                data: probabilities,
                backgroundColor: labels.map(label => getBackgroundColor(label)), // เปลี่ยนสีตามหมายเลข
                borderColor: labels.map(label => label === userNumber ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'), // เปลี่ยนสีขอบตามหมายเลข
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
</script>
</body>
</html>

<?php
// ปิดการเชื่อมต่อ
$db->close();
?>


