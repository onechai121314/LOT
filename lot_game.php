<?php

// file : lot_game.php
// รวมไฟล์ database.php เพื่อให้สามารถใช้ฟังก์ชันจากไฟล์นี้ได้
require_once('database.php');

// เชื่อมต่อฐานข้อมูล
$db = new SQLite3('data.db');

// ดึงข้อมูลโฆษณาจากฐานข้อมูล
// กำหนด id ที่ต้องการดึง (สามารถแก้ไขตามที่ต้องการ)
$ids_to_fetch = [13, 14]; // กำหนดให้ดึงข้อมูลจาก id 1 และ 3

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

// Function to generate 6 random lottery numbers between 0 and 9
function generateLotteryNumbers() {
    $numbers = [];
    while (count($numbers) < 6) {
        $num = rand(0, 9);
        if (!in_array($num, $numbers)) {
            $numbers[] = $num;
        }
    }
    sort($numbers); // Sort numbers in ascending order
    return $numbers;
}

// Function to generate 3 random lottery numbers between 0 and 9
function generateLotteryNumbersThree() {
    $numbers = [];
    while (count($numbers) < 3) {
        $num = rand(0, 9);
        if (!in_array($num, $numbers)) {
            $numbers[] = $num;
        }
    }
    sort($numbers); // Sort numbers in ascending order
    return $numbers;
}

// Function to generate 3 random lottery numbers between 0 and 9
function generateLotteryNumbersTwo() {
    $numbers = [];
    while (count($numbers) < 2) {
        $num = rand(0, 9);
        if (!in_array($num, $numbers)) {
            $numbers[] = $num;
        }
    }
    sort($numbers); // Sort numbers in ascending order
    return $numbers;
}

// If the form is submitted, generate the appropriate lottery numbers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generate6'])) {
        $lotteryNumbers = generateLotteryNumbers();
    }
    if (isset($_POST['generate3'])) {
        $lotteryNumbersThree = generateLotteryNumbersThree();
    }
    if (isset($_POST['generate2'])) {
        $lotteryNumbersThree = generateLotteryNumbersThree();
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

// ตัวอย่างการเรียกใช้ฟังก์ชันเมื่อเข้าชมหน้า lot_two.php
incrementViewCount($db, 'lot_game');

// ดึงจำนวนคนดูในวันนี้
$queryViews = "SELECT view_count FROM lot_game_views WHERE view_date = :view_date";
$stmtViews = $db->prepare($queryViews);
$stmtViews->bindValue(':view_date', date('Y-m-d'), SQLITE3_TEXT);
$resultViews = $stmtViews->execute();

$viewsToday = 0;
if ($row = $resultViews->fetchArray(SQLITE3_ASSOC)) {
    $viewsToday = $row['view_count'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กล่องสุ่มเลข</title>
    <link rel="stylesheet" href="styles_lot.css">
    <link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Mitr:wght@300&display=swap" rel="stylesheet">
    <style>
        .lottery-machine {
            display: inline-flex;
            justify-content: space-between;
            width: 400px;
            height: 100px;
            overflow: hidden;
            border: 5px solid #333;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .number {
            width: 60px;
            height: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 2rem;
            background-color: #fff;
            border: 1px solid #ccc;
            margin: 2px;
            transition: transform 0.5s ease;
        }
        .result {
            margin-top: 20px;
            font-size: 1.5rem;
            font-weight: bold;
        }
        button {
            padding: 15px 30px;
            font-size: 1.5rem;
            cursor: pointer;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
        }
        button:hover {
            background-color: #218838;
        }
        form {
            margin: 20px;
        }

        /* กล่องคำเตือน  */
        /* ออกแบบกล่องคำเตือน */
        .alert-box {
        max-width: 400px;
        margin: 20px auto;
        padding: 15px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: bold;
        font-family: 'Mitr', sans-serif;
        display: flex;
        align-items: center;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        position: relative;
        }

        /* สไตล์ข้อความ */
        .alert-box .text {
            flex-grow: 1;
            color: #fff;
        }

        /* สไตล์ปุ่มปิด */
        .alert-box .close-btn {
            background: #800000;
            border: none;
            font-size: 20px;
            font-weight: bold;
            color: #fff;
            cursor: pointer;
        }
        /* กล่องประเภทข้อผิดพลาด */
        .alert-warning {
            background-color: #dc3545;
            color: #ffffff;
        }
         /* กล่องคำเตือน  */
    </style>
</head>
<body>
<header>
<div class="logo">
    <a href="lot_page.php">
        <img src="logo.png" alt="โลโก้เว็บไซต์">
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
            <div class="alert-box alert-warning">
                <span class="text">คำเตือน !! : กล่องสุ่มตัวเลขนั้นมีไว้เล่นเพื่อความบันเทิงเท่านั้น นอกเหนือจากนั้นทางแอดมินจะไม่รับผิดชอบใดๆทั้งสิ้น</span>
                <button class="close-btn" onclick="this.parentElement.style.display='none';">&times;</button>
            </div>   
            <h1>กล่องสุ่มตัวเลข</h1>
            <p>จำนวนการเข้าหน้าเว็บวันนี้ : <?php echo $viewsToday; ?> ครั้ง</p>

            <form method="POST" id="lotteryForm6">
                <button type="submit" name="generate6" id="spinBtn6">สุ่ม</button>
            </form>

            <div class="lottery-machine">
                <?php 
                // Display the 6 lottery numbers
                if (isset($lotteryNumbers)) {
                    foreach ($lotteryNumbers as $index => $number) {
                        echo "<div class='number' id='number6{$index}'>$number</div>";
                    }
                } else {
                    // Default display if not generated yet
                    for ($i = 0; $i < 6; $i++) {
                        echo "<div class='number' id='number6{$i}'>0</div>";
                    }
                }
                ?>
            </div>

            <div class="result">
                <?php echo isset($lotteryNumbers) ? "Generated Numbers: " . implode(", ", $lotteryNumbers) : ""; ?>
            </div>

            <form method="POST" id="lotteryForm3">
                <button type="submit" name="generate3" id="spinBtn3">สุ่ม</button>
            </form>

            <div class="lottery-machine">
                <?php 
                // Display the 3 lottery numbers
                if (isset($lotteryNumbersThree)) {
                    foreach ($lotteryNumbersThree as $index => $number) {
                        echo "<div class='number' id='number3{$index}'>$number</div>";
                    }
                } else {
                    // Default display if not generated yet
                    for ($i = 0; $i < 3; $i++) {
                        echo "<div class='number' id='number3{$i}'>0</div>";
                    }
                }
                ?>
            </div>

            <div class="result">
                <?php echo isset($lotteryNumbersTwo) ? "Generated Numbers: " . implode(", ", $lotteryNumbersTwo) : ""; ?>
            </div>

            <form method="POST" id="lotteryForm2">
                <button type="submit" name="generate2" id="spinBtn2">สุ่ม</button>
            </form>

            <div class="lottery-machine">
                <?php 
                // Display the 3 lottery numbers
                if (isset($lotteryNumbersTwo)) {
                    foreach ($lotteryNumbersTwo as $index => $number) {
                        echo "<div class='number' id='number2{$index}'>$number</div>";
                    }
                } else {
                    // Default display if not generated yet
                    for ($i = 0; $i < 2; $i++) {
                        echo "<div class='number' id='number2{$i}'>0</div>";
                    }
                }
                ?>
            </div>

            <div class="result">
                <?php echo isset($lotteryNumbersTwo) ? "Generated Numbers: " . implode(", ", $lotteryNumbersTwo) : ""; ?>
            </div>
            </div>

        </section>
    </main>

<!-- 
<footer>
    <p>&copy; 2024 สถิติสลากกินแบ่ง. All rights reserved.</p>
</footer> -->

<script>
    document.getElementById("lotteryForm6").addEventListener("submit", function (event) {
    event.preventDefault();

    const button = document.getElementById("spinBtn6");
    button.disabled = true;
    button.textContent = "Generating...";

    const numbers = Array.from({ length: 6 }, (_, index) => document.getElementById(`number6${index}`));
    numbers.forEach((number, index) => {
        const randomNumber = Math.floor(Math.random() * 10);
        number.textContent = "...";
        setTimeout(() => {
            number.textContent = randomNumber;
            number.style.transform = "scale(1.2)";
            setTimeout(() => number.style.transform = "scale(1)", 200);
        }, 1000 + index * 100);
    });

    setTimeout(() => {
        button.disabled = false;
        button.textContent = "Generate 6 Numbers";
    }, 2000);
});

document.getElementById("lotteryForm3").addEventListener("submit", function (event) {
    event.preventDefault();

    const button = document.getElementById("spinBtn3");
    button.disabled = true;
    button.textContent = "Generating...";

    const numbers = Array.from({ length: 3 }, (_, index) => document.getElementById(`number3${index}`));
    numbers.forEach((number, index) => {
        const randomNumber = Math.floor(Math.random() * 10);
        number.textContent = "...";
        setTimeout(() => {
            number.textContent = randomNumber;
            number.style.transform = "scale(1.2)";
            setTimeout(() => number.style.transform = "scale(1)", 200);
        }, 1000 + index * 100);
    });

    setTimeout(() => {
        button.disabled = false;
        button.textContent = "Generate 3 Numbers";
    }, 2000);
});

document.getElementById("lotteryForm2").addEventListener("submit", function (event) {
    event.preventDefault();

    const button = document.getElementById("spinBtn2");
    button.disabled = true;
    button.textContent = "Generating...";

    const numbers = Array.from({ length: 2 }, (_, index) => document.getElementById(`number2${index}`));
    numbers.forEach((number, index) => {
        const randomNumber = Math.floor(Math.random() * 10);
        number.textContent = "...";
        setTimeout(() => {
            number.textContent = randomNumber;
            number.style.transform = "scale(1.2)";
            setTimeout(() => number.style.transform = "scale(1)", 200);
        }, 1000 + index * 100);
    });

    setTimeout(() => {
        button.disabled = false;
        button.textContent = "Generate 2 Numbers";
    }, 2000);
});
</script>

</body>
</html>
