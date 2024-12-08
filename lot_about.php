<?php

// file : lot_page.php
// รวมไฟล์ database.php เพื่อให้สามารถใช้ฟังก์ชันจากไฟล์นี้ได้
require_once('database.php');

// เชื่อมต่อฐานข้อมูล
$db = new SQLite3('data.db');

// ดึงข้อมูลโฆษณาจากฐานข้อมูล
// กำหนด id ที่ต้องการดึง (สามารถแก้ไขตามที่ต้องการ)
$ids_to_fetch = [15, 16]; // กำหนดให้ดึงข้อมูลจาก id 1 และ 3

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

    // ฟังก์ชันสำหรับดึงข้อมูลล่าสุดจากแต่ละตาราง
    function getLatestAwards($db, $table_name) {
        $query = "SELECT number, date, award_type FROM $table_name ORDER BY date DESC, award_type ASC";
        $result = $db->query($query);
        $latest_date = null;
        $awards = [];

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if ($latest_date === null) {
                $latest_date = $row['date'];
            }

            // เก็บเฉพาะรางวัลของวันที่ล่าสุด
            if ($row['date'] === $latest_date) {
                $awards[] = $row;
            } else {
                break;
            }
        }

        return ['date' => $latest_date, 'awards' => $awards];
    }

    // ดึงข้อมูลจากแต่ละตาราง
    $six_digit_awards = getLatestAwards($db, 'six_digit_numbers');
    $two_digit_awards = getLatestAwards($db, 'two_digit_numbers');
    $first_three_digit_awards = getLatestAwards($db, 'first_three_digit_numbers');
    $last_three_digit_awards = getLatestAwards($db, 'last_three_digit_numbers');

    // แปลงวันที่ล่าสุดเป็นรูปแบบ วัน/เดือน/ปี
    function formatDate($date) {
        $date_parts = explode('-', $date);
        if (count($date_parts) === 3) {
            return $date_parts[2] . '/' . $date_parts[1] . '/' . ($date_parts[0] - 543); // แปลง พ.ศ. เป็น ค.ศ.
        }
        return $date;
    }

    $latest_date_formatted = formatDate($six_digit_awards['date']);
    // $separated_numbers = str_split($number); // แยกตัวเลขแต่ละตัว

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
incrementViewCount($db, 'lot_page');

// ดึงจำนวนคนดูในวันนี้
$queryViews = "SELECT view_count FROM lot_page_views WHERE view_date = :view_date";
$stmtViews = $db->prepare($queryViews);
$stmtViews->bindValue(':view_date', date('Y-m-d'), SQLITE3_TEXT);
$resultViews = $stmtViews->execute();

$viewsToday = 0;
if ($row = $resultViews->fetchArray(SQLITE3_ASSOC)) {
    $viewsToday = $row['view_count'];
}

// ปิดการเชื่อมต่อ
$db->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เกี่ยวกับเรา</title>
    <link rel="stylesheet" href="styles_lot.css">
    <link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Mitr:wght@300&display=swap" rel="stylesheet">
    <style>
        body {
    margin: 0;
    padding: 0;
    line-height: 1.6;
    color: #333;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.about-us {
    background-color: #f9f9f9;
    padding: 40px 20px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.about-us h2 {
    font-size: 2.5em;
    text-align: center;
    color: #333;
    margin-bottom: 20px;
}

.about-us p {
    font-size: 1.1em;
    margin-bottom: 20px;
    text-align: center;
}

.about-details {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: space-between;
}

.about-item {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    flex: 1 1 calc(33.333% - 20px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.about-item h3 {
    font-size: 1.5em;
    color: #007BFF;
    margin-bottom: 10px;
}

.about-item p {
    font-size: 1em;
    color: #666;
}

@media (max-width: 768px) {
    .about-details {
        flex-direction: column;
    }

    .about-item {
        flex: 1 1 100%;
    }
}

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
    <section class="about-us">
        <div class="container">
            <h2>เกี่ยวกับเรา</h2>
            <p>
                เว็บไซต์ของเราเป็นเว็บไซต์เกี่ยวกับการประกาศผล และ สถิติ ของสลากกินแบ่งรัฐบาล โดยได้นำข้อมูล 20 ปี ย้อนหลังมาทำให้ดูง่ายมากขึ้น และ เพิ่มฟังกชั่นเช่นกราฟ และ ตาราง ในการดูสถิตตัวเลข
                ซึ่งเป็นตัวเลือกในการตัดสินใจซื้อตัวเลขที่ผู้ใช้ต้องการ และ เราจะมีการอัพเดตตลอดทุกงวด
            </p>
            <div class="about-details">
                <div class="about-item">
                    <h3>ติดต่อเรา</h3>
                    <p>
                        สามารถติดต่องานลงโฆษณาได้ทาง Email : keroro232425@gmail.com 
                    </p>
                </div>
                <!-- <div class="about-item">
                    <h3>เป้าหมายของเรา</h3>
                    <p>
                        เรามุ่งมั่นที่จะเป็นผู้นำในด้านการให้บริการที่ทันสมัย 
                        และตอบสนองความต้องการของผู้ใช้งานในยุคดิจิทัล
                    </p>
                </div>
                <div class="about-item">
                    <h3>ทีมงานของเรา</h3>
                    <p>
                        ทีมงานของเราประกอบด้วยผู้เชี่ยวชาญในหลากหลายด้าน 
                        พร้อมให้บริการและแก้ไขปัญหาอย่างรวดเร็วและมีประสิทธิภาพ
                    </p> -->
                </div>
            </div>
        </div>
    </section>
</main>


    <script>
        function toggleMenu() {
            const menu = document.getElementById('menu');
            menu.classList.toggle('show');
        }
    </script>

</body>
</html>


