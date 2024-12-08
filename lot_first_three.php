<?php 
//    file : lot_first_three.php
// รวมไฟล์ database.php เพื่อให้สามารถใช้ฟังก์ชันจากไฟล์นี้ได้
require_once('database.php');

// เชื่อมต่อฐานข้อมูล
$db = new SQLite3('data.db');

// ตรวจสอบว่าใช้การค้นหาทั้งหมดหรือไม่
$showAll = isset($_GET['show_all']) ? true : false;

// รับค่าปี, เดือน, วัน และ เลขหกหลักจาก URL
$year = isset($_GET['year']) ? $_GET['year'] : '';
$month = isset($_GET['month']) ? $_GET['month'] : '';
$day = isset($_GET['day']) ? $_GET['day'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : ''; // วันที่ในรูปแบบ ปี-เดือน-วัน
$number = isset($_GET['number']) ? $_GET['number'] : ''; // เลขหกหลักที่ต้องการค้นหา

// หากเลือกดูทั้งหมด จะไม่กรองข้อมูล
if ($showAll) {
    // คำสั่ง SQL เมื่อแสดงทั้งหมด
    $query = "SELECT number, date, award_type FROM first_three_digit_numbers ORDER BY date DESC";
    $stmt = $db->prepare($query); // เตรียมคำสั่ง SQL
} else {
    // สร้างคำสั่ง SQL เพื่อตามหาเลขหกหลักตามเงื่อนไขที่เลือก
    $query = "SELECT number, date, award_type FROM first_three_digit_numbers WHERE 1=1"; // เพิ่มเงื่อนไขที่ 1=1 ให้คำสั่ง SQL เป็นที่สมบูรณ์

    if ($year) {
        $query .= " AND strftime('%Y', date) = :year"; // ค้นหาตามปี
    }
    if ($month) {
        $query .= " AND strftime('%m', date) = :month"; // ค้นหาตามเดือน
    }
    if ($day) {
        $query .= " AND strftime('%d', date) = :day"; // ค้นหาตามวัน
    }
    if ($date) {
        $query .= " AND strftime('%Y-%m-%d', date) = :date"; // ค้นหาตามวัน/เดือน/ปี
    }
    if ($number) {
        $query .= " AND number = :number"; // ค้นหาตามเลขหกหลัก
    }

    // เรียงตามวันจากล่าสุดไปเก่า
    $query .= " ORDER BY date DESC";

    $stmt = $db->prepare($query); // เตรียมคำสั่ง SQL
    if ($year) {
        $stmt->bindValue(':year', $year, SQLITE3_TEXT);
    }
    if ($month) {
        $stmt->bindValue(':month', $month, SQLITE3_TEXT);
    }
    if ($day) {
        $stmt->bindValue(':day', $day, SQLITE3_TEXT);
    }
    if ($date) {
        $stmt->bindValue(':date', $date, SQLITE3_TEXT);
    }
    if ($number) {
        $stmt->bindValue(':number', $number, SQLITE3_TEXT);
    }
}

// ดำเนินการกับคำสั่ง SQL
$result = $stmt->execute();

// สร้างอาร์เรย์เพื่อเก็บความถี่ของตัวเลขในแต่ละหลัก
$digitFrequencies = array_fill(0, 3, array_fill(0, 10, 0));
$latestNumber = '';
$formattedNumber = sprintf("%03d", $latestNumber); // จะได้ "012345"

// คำนวณความถี่ของตัวเลขในแต่ละหลัก
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $number = str_split($row['number']);
    $numbers[] = ['number' => $row['number'], 'date' => $row['date'], 'award_type' => $row['award_type']];
    
    // คำนวณความถี่ของตัวเลขในแต่ละหลัก
    if (count($number) === 3) {
        for ($i = 0; $i < 3; $i++) {
            $digitFrequencies[$i][(int)$number[$i]]++;
        }
    }
}

// ดึงข้อมูลเลข 3 หลักล่าสุด
$queryLatest = "SELECT number FROM first_three_digit_numbers ORDER BY id DESC LIMIT 1";
$latestResult = $db->query($queryLatest);
if ($latestRow = $latestResult->fetchArray(SQLITE3_ASSOC)) {
    $latestNumber = $latestRow['number'];
}

// ดึงปีจากฐานข้อมูล (เฉพาะปีที่มีข้อมูล)
$queryYears = "SELECT DISTINCT strftime('%Y', date) AS year FROM first_three_digit_numbers ORDER BY year DESC";
$resultYears = $db->query($queryYears);
$years = [];
while ($row = $resultYears->fetchArray(SQLITE3_ASSOC)) {
    $years[] = $row['year'];
}

// ดึงเดือนจากฐานข้อมูล (เฉพาะเดือนที่มีข้อมูล)
$queryMonths = "SELECT DISTINCT strftime('%m', date) AS month FROM first_three_digit_numbers ORDER BY month ASC";
$resultMonths = $db->query($queryMonths);
$months = [];
while ($row = $resultMonths->fetchArray(SQLITE3_ASSOC)) {
    $months[] = $row['month'];
}

// ดึงวันที่จากฐานข้อมูล (เฉพาะวันที่มีข้อมูล)
$queryDays = "SELECT DISTINCT strftime('%d', date) AS day FROM first_three_digit_numbers ORDER BY day ASC";
$resultDays = $db->query($queryDays);
$days = [];
while ($row = $resultDays->fetchArray(SQLITE3_ASSOC)) {
    $days[] = $row['day'];
}

// คำสั่ง SQL สำหรับการค้นหาตัวเลขที่ออกซ้ำ
$queryDuplicates = "
    SELECT number, COUNT(*) AS occurrences 
    FROM first_three_digit_numbers 
    WHERE 1=1";

if ($year) {
    $queryDuplicates .= " AND strftime('%Y', date) = :year";
}
if ($month) {
    $queryDuplicates .= " AND strftime('%m', date) = :month";
}
if ($day) {
    $queryDuplicates .= " AND strftime('%d', date) = :day";
}
if ($date) {
    $queryDuplicates .= " AND strftime('%Y-%m-%d', date) = :date";
}

$queryDuplicates .= " GROUP BY number HAVING occurrences > 1 ORDER BY occurrences DESC, number ASC";
$stmtDuplicates = $db->prepare($queryDuplicates);

// ผูกค่าต่างๆ ที่กรอกในฟอร์ม
if ($year) {
    $stmtDuplicates->bindValue(':year', $year, SQLITE3_TEXT);
}
if ($month) {
    $stmtDuplicates->bindValue(':month', $month, SQLITE3_TEXT);
}
if ($day) {
    $stmtDuplicates->bindValue(':day', $day, SQLITE3_TEXT);
}
if ($date) {
    $stmtDuplicates->bindValue(':date', $date, SQLITE3_TEXT);
}

$resultDuplicates = $stmtDuplicates->execute();

$duplicates = [];
while ($row = $resultDuplicates->fetchArray(SQLITE3_ASSOC)) {
    $duplicates[] = [
        'number' => sprintf("%03d", $row['number']), // รูปแบบตัวเลข 6 หลัก
        'occurrences' => $row['occurrences'] // จำนวนครั้งที่ออก
    ];
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

// ตัวอย่างการเรียกใช้ฟังก์ชันเมื่อเข้าชมหน้า lot_first_three.php
incrementViewCount($db, 'lot_first_three');

// ดึงจำนวนคนดูในวันนี้
$queryViews = "SELECT view_count FROM lot_first_three_views WHERE view_date = :view_date";
$stmtViews = $db->prepare($queryViews);
$stmtViews->bindValue(':view_date', date('Y-m-d'), SQLITE3_TEXT);
$resultViews = $stmtViews->execute();

$viewsToday = 0;
if ($row = $resultViews->fetchArray(SQLITE3_ASSOC)) {
    $viewsToday = $row['view_count'];
}

// ดึงข้อมูลโฆษณาจากฐานข้อมูล
// กำหนด id ที่ต้องการดึง (สามารถแก้ไขตามที่ต้องการ)
$ids_to_fetch = [5, 6]; // กำหนดให้ดึงข้อมูลจาก id 1 และ 3

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

$db->close(); //ปิดการเชื่อมต่อ database

// ฟังก์ชันคาดการณ์ตัวเลข
function predictNumbers($frequencies, $latestDigits) {
    $predictedNumbers = [];
    foreach ($frequencies as $index => $freq) {
        $maxFrequency = max($freq);
        $predictedDigit = array_search($maxFrequency, $freq);
        if (!in_array($predictedDigit, $predictedNumbers)) {
            $predictedNumbers[] = $predictedDigit;
        }
    }
    
    // เพิ่มเลขจากหลักล่าสุดเข้าไป
    foreach ($latestDigits as $digit) {
        if (!in_array($digit, $predictedNumbers)) {
            $predictedNumbers[] = $digit;
        }
    }

    // จัดรูปแบบเป็นเลข 3 หลัก
    while (count($predictedNumbers) < 3) {
        $predictedNumbers[] = rand(0, 9); // เติมเลขสุ่มถ้าครบ 3 หลักแล้ว
    }

    return $predictedNumbers;
}

$latestDigits = str_split($latestNumber);
$predictedNumbers = predictNumbers($digitFrequencies, $latestDigits);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถิติเลขหน้า 3 ตัว</title>
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

        <h1>สถิติเลข 3 หลัก</h1>

        <p>จำนวนการเข้าหน้าเว็บวันนี้ : <?php echo $viewsToday; ?> ครั้ง</p>

        <div id="lotChart">
            <canvas id="lotChartCanvas" width="1000" height="500"></canvas>
        </div>

        <!-- ค้นหาตามวัน/เดือน/ปี -->
        <form method="get" action="lot_first_three.php">
            <label for="date">ค้นหาตามวัน/เดือน/ปี:</label>
            <select id="day-date" name="day">
                <option value="">-- ดูทั้งหมด --</option>
                <?php foreach ($days as $d): ?>
                    <option value="<?php echo sprintf('%03d', $d); ?>" <?php echo (sprintf('%03d', $d) == $day) ? 'selected' : ''; ?>>
                        <?php echo sprintf('%03d', $d); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="month-date" name="month">
                <option value="">-- ดูทั้งหมด --</option>
                <?php foreach ($months as $m): ?>
                    <option value="<?php echo sprintf('%03d', $m); ?>" <?php echo (sprintf('%03d', $m) == $month) ? 'selected' : ''; ?>>
                        <?php echo sprintf('%03d', $m); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="year-date" name="year">
                <option value="">-- ดูทั้งหมด --</option>
                <?php foreach ($years as $y): ?>
                    <option value="<?php echo $y; ?>" <?php echo ($y == $year) ? 'selected' : ''; ?>>
                        <?php echo $y; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">ค้นหา</button>
            <!-- <button type="submit" name="show_all" value="true">ดูทั้งหมด</button> -->
        </form>

        <!-- ปุ่มดูทั้งหมด -->
        <!-- <form method="get" action="lot_six.php">
            <button type="submit" name="show_all" value="true">ดูทั้งหมด</button>
        </form> -->

        <!-- ตัวเลขที่ออกซ้ำ -->
        <h2>ตัวเลขที่ออกซ้ำ</h2>
        <?php if ($duplicates): ?>
            <table>
                <thead>
                    <tr>
                        <th>เลขหกหลัก</th>
                        <th>จำนวนครั้งที่ออก</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($duplicates as $duplicate): ?>
                        <tr>
                            <td><?php echo $duplicate['number']; ?></td>
                            <td><?php echo $duplicate['occurrences']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>ไม่มีตัวเลขที่ออกซ้ำ</p>
        <?php endif; ?>

        <!-- ตารางแสดงความถี่ตัวเลขแต่ละหลัก -->
        <h2>ความถี่ตัวเลขแต่ละหลัก (0-9)</h2>
        <table>
            <thead>
                <tr>
                    <th>หลัก</th>
                    <?php for ($i = 0; $i <= 9; $i++): ?>
                        <th><?php echo $i; ?></th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($digitFrequencies as $index => $frequencies): ?>
                    <tr>
                        <td>หลักที่ <?php echo $index + 1; ?></td>
                        <?php foreach ($frequencies as $frequency): ?>
                            <td><?php echo $frequency; ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- ตารางแสดงเลขหกหลัก เรียงตามวันล่าสุด -->
        <h2>ตารางแสดงเลขหกหลัก</h2>
        <?php if ($numbers): ?>
            <table>
                <thead>
                    <tr>
                        <th>เลขหกหลัก</th>
                        <th>วันที่</th>
                        <th>ประเภทรางวัล</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($numbers as $row): ?>
                    <tr>
                        <td><?php echo sprintf("%03d", $row['number']); ?></td> <!-- แสดงเลข 3 หลัก โดยมีเลข 0 ข้างหน้า -->
                        <td>
                            <?php
                            $formattedDate = DateTime::createFromFormat('Y-m-d', $row['date'])->format('d/m/Y');
                            echo htmlspecialchars($formattedDate);
                            ?>
                        </td>
                        <td>รางวัลที่ : <?php echo htmlspecialchars($row['award_type']); ?></td> <!-- ประเภทรางวัล -->
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>ไม่พบข้อมูลเลขหกหลักตามเงื่อนไขที่ค้นหา</p>
        <?php endif; ?>
    </div>

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
const digitFrequencies = <?php echo json_encode($digitFrequencies); ?>;

const ctx = document.getElementById('lotChartCanvas').getContext('2d');

const lotChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [...Array(10).keys()], // เลข 0-9
        datasets: digitFrequencies.map((frequencies, index) => ({
            label: `หลักที่ ${index + 1}`,
            data: frequencies,
            backgroundColor: [
                'rgba(255, 99, 132, 0.6)',
                'rgba(54, 162, 235, 0.6)',
                'rgba(255, 206, 86, 0.6)'
            ][index],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)'
            ][index],
            borderWidth: 1
        }))
    },
    options: {
        responsive: true, // ทำให้กราฟยืดหยุ่นตามหน้าจอ
        maintainAspectRatio: false, // อนุญาตให้กราฟเปลี่ยนสัดส่วน
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'จำนวนครั้ง'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'เลข'
                }
            }
        },
        plugins: {
            legend: {
                position: 'top' // เปลี่ยนตำแหน่งคำอธิบายกราฟ
            }
        }
    }
});

function GoBackWithRefresh(event) {
    if ('referrer' in document) {
        window.location = document.referrer;
        /* OR */
        //location.replace(document.referrer);
    } else {
        window.history.back();
    }
}

document.getElementById('refreshButton').addEventListener('click', function() {
        // เรียกใช้งานข้อมูลจาก lot_first_three.php ด้วย AJAX
        fetch('lot_first_three.php?show_all=true')
            .then(response => response.text())
            .then(data => {
                // อัปเดตเนื้อหาใน div content
                document.getElementById('content').innerHTML = data;
            })
            .catch(error => console.error('Error:', error));
    });

</script>

</body>
</html>


