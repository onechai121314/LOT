<?php
// file : database.php
// สร้างฐานข้อมูลและเชื่อมต่อ
$db = new SQLite3('data.db');



// สร้างตารางสำหรับเก็บตัวเลข 6 หลัก (เปลี่ยนเป็น TEXT และเพิ่ม award_type)
$query1 = "CREATE TABLE IF NOT EXISTS six_digit_numbers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    number TEXT CHECK(length(number) = 6),
    date TEXT,
    award_type INTEGER
)";
$db->exec($query1);

// สร้างตารางสำหรับเก็บตัวเลข 2 หลัก (เปลี่ยนเป็น TEXT และเพิ่ม award_type)
$query2 = "CREATE TABLE IF NOT EXISTS two_digit_numbers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    number TEXT CHECK(length(number) = 2),
    date TEXT,
    award_type INTEGER
)";
$db->exec($query2);

// สร้างตารางสำหรับเก็บตัวเลข 3 หลักหน้า (เปลี่ยนเป็น TEXT และเพิ่ม award_type)
$query3 = "CREATE TABLE IF NOT EXISTS first_three_digit_numbers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    number TEXT CHECK(length(number) = 3),
    date TEXT,
    award_type INTEGER
)";
$db->exec($query3);

// สร้างตารางสำหรับเก็บตัวเลข 3 หลักท้าย (เปลี่ยนเป็น TEXT และเพิ่ม award_type)
$query4 = "CREATE TABLE IF NOT EXISTS last_three_digit_numbers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    number TEXT CHECK(length(number) = 3),
    date TEXT,
    award_type INTEGER
)";
$db->exec($query4);

// สร้างตารางสำหรับเก็บจำนวนการตอบสนองการใช้งานเว็บไซต์ lot_page.php
$query_view_lot_page = "CREATE TABLE IF NOT EXISTS lot_page_views (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    view_date DATE NOT NULL,
    view_count INTEGER DEFAULT 0
)";
$db->exec($query_view_lot_page);

// สร้างตารางสำหรับเก็บจำนวนการเข้าชมของแต่ละหน้า
$query_lot_page_views = "CREATE TABLE IF NOT EXISTS lot_page_views (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    view_date DATE NOT NULL,
    view_count INTEGER DEFAULT 0
)";
$db->exec($query_lot_page_views);

// สร้างตารางสำหรับเก็บจำนวนการเข้าชมของแต่ละหน้า lot_six
$query_lot_six_views = "CREATE TABLE IF NOT EXISTS lot_six_views (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    view_date DATE NOT NULL,
    view_count INTEGER DEFAULT 0
)";
$db->exec($query_lot_six_views);

// สร้างตารางสำหรับเก็บจำนวนการเข้าชมของหน้า lot_two.php
$query_lot_two_views = "CREATE TABLE IF NOT EXISTS lot_two_views (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    view_date DATE NOT NULL,
    view_count INTEGER DEFAULT 0
)";
$db->exec($query_lot_two_views);

// สร้างตารางสำหรับเก็บจำนวนการเข้าชมของหน้า lot_fist_three.php
$query_lot_first_three_views = "CREATE TABLE IF NOT EXISTS lot_first_three_views (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    view_date DATE NOT NULL,
    view_count INTEGER DEFAULT 0
)";
$db->exec($query_lot_first_three_views);

// สร้างตารางสำหรับเก็บจำนวนการเข้าชมของหน้า lot_last_three.php
$query_lot_last_three_views = "CREATE TABLE IF NOT EXISTS lot_last_three_views (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    view_date DATE NOT NULL,
    view_count INTEGER DEFAULT 0
)";
$db->exec($query_lot_last_three_views);

// สร้างตารางสำหรับเก็บจำนวนการเข้าชมของหน้า lot_calculate.php
$query_lot_calculate_views = "CREATE TABLE IF NOT EXISTS lot_calculate_views (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    view_date DATE NOT NULL,
    view_count INTEGER DEFAULT 0
)";
$db->exec($query_lot_calculate_views);

// สร้างตารางสำหรับเก็บจำนวนการเข้าชมของหน้า lot_game.php
$query_lot_game_views = "CREATE TABLE IF NOT EXISTS lot_game_views (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    view_date DATE NOT NULL,
    view_count INTEGER DEFAULT 0
)";
$db->exec($query_lot_game_views);

// สร้างตารางเก็บข้อมูลโฆษณา
$query_ads = "CREATE TABLE IF NOT EXISTS ads (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    image_data BLOB NOT NULL, -- เก็บข้อมูลรูปภาพ
    link TEXT NOT NULL        -- เก็บลิงก์
)";
$db->exec($query_ads);

// ฟังก์ชันสำหรับเพิ่มตัวเลข 6 หลัก (เก็บเป็น TEXT พร้อม award_type)
function insertSixDigitNumber($db, $number, $date, $award_type) {
    $stmt = $db->prepare('INSERT INTO six_digit_numbers (number, date, award_type) VALUES (:number, :date, :award_type)');
    $stmt->bindValue(':number', str_pad($number, 6, '0', STR_PAD_LEFT), SQLITE3_TEXT);  // เติม 0 ถ้าตัวเลขน้อยกว่า 6 หลัก
    $stmt->bindValue(':date', $date, SQLITE3_TEXT);
    $stmt->bindValue(':award_type', $award_type, SQLITE3_INTEGER);
    return $stmt->execute();
}

// ฟังก์ชันสำหรับเพิ่มตัวเลข 2 หลัก (เก็บเป็น TEXT พร้อม award_type)
function insertTwoDigitNumber($db, $number, $date, $award_type) {
    $stmt = $db->prepare('INSERT INTO two_digit_numbers (number, date, award_type) VALUES (:number, :date, :award_type)');
    $stmt->bindValue(':number', str_pad($number, 2, '0', STR_PAD_LEFT), SQLITE3_TEXT);  // เติม 0 ถ้าตัวเลขน้อยกว่า 2 หลัก
    $stmt->bindValue(':date', $date, SQLITE3_TEXT);
    $stmt->bindValue(':award_type', $award_type, SQLITE3_INTEGER);
    return $stmt->execute();
}

// ฟังก์ชันสำหรับเพิ่มตัวเลข 3 หลักหน้า (เก็บเป็น TEXT พร้อม award_type)
function insertFirstThreeDigitNumber($db, $number, $date, $award_type) {
    $stmt = $db->prepare('INSERT INTO first_three_digit_numbers (number, date, award_type) VALUES (:number, :date, :award_type)');
    $stmt->bindValue(':number', str_pad($number, 3, '0', STR_PAD_LEFT), SQLITE3_TEXT);  // เติม 0 ถ้าตัวเลขน้อยกว่า 3 หลัก
    $stmt->bindValue(':date', $date, SQLITE3_TEXT);
    $stmt->bindValue(':award_type', $award_type, SQLITE3_INTEGER);
    return $stmt->execute();
}

// ฟังก์ชันสำหรับเพิ่มตัวเลข 3 หลักท้าย (เก็บเป็น TEXT พร้อม award_type)
function insertLastThreeDigitNumber($db, $number, $date, $award_type) {
    $stmt = $db->prepare('INSERT INTO last_three_digit_numbers (number, date, award_type) VALUES (:number, :date, :award_type)');
    $stmt->bindValue(':number', str_pad($number, 3, '0', STR_PAD_LEFT), SQLITE3_TEXT);  // เติม 0 ถ้าตัวเลขน้อยกว่า 3 หลัก
    $stmt->bindValue(':date', $date, SQLITE3_TEXT);
    $stmt->bindValue(':award_type', $award_type, SQLITE3_INTEGER);
    return $stmt->execute();
}

// ฟังก์ชันในการแบ่งประเภทของรางวัลตามวันที่
function award_type($db, $number_type) {
    $query = "SELECT * FROM $number_type ORDER BY date DESC";
    $result = $db->query($query);

    $awards = [];
    $current_date = null;
    $award_count = 0;

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $date = $row['date'];
        if ($current_date !== $date) {
            $current_date = $date;
            $award_count = 1; // เริ่มลำดับใหม่เมื่อเปลี่ยนวันที่
        } else {
            $award_count++;
        }
        $awards[] = [
            'id' => $row['id'],
            'number' => $row['number'],
            'date' => $row['date'],
            'award_count' => $award_count
        ];
    }
    return $awards;
}

// function การแยกรางวัลที่ 1/2/3....
function calculateAwardType($db, $number_type, $date_input) {
    $query = "SELECT MAX(award_type) as max_award_type FROM $number_type WHERE date = :date_input";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':date_input', $date_input, SQLITE3_TEXT);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    // ถ้าวันนี้ยังไม่มีข้อมูล จะให้รางวัลเป็น 1
    $max_award_type = $result['max_award_type'] ?? 0;
    return $max_award_type + 1;
}

// ตรวจสอบการส่งฟอร์ม
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $number_type = $_POST['number_type'];
    $number_inputs = $_POST['number_input'];
    $date_inputs = $_POST['date_input_date'];

    $success = false;
    $award_type = 1; // กำหนดรางวัลแรกเป็น 1

    // แปลงวันที่และบันทึกข้อมูล
    foreach ($number_inputs as $index => $number_input) {
        $number_input = strval($number_input); // เก็บเป็น string
        $date_input = $date_inputs[$index] ?? null; // รับวันที่ที่ตรงกัน

        if ($date_input) {
            // แปลงวันที่เป็น YYYY-MM-DD
            if (strpos($date_input, '/') !== false) {
                $date_parts = explode('/', $date_input);

                // แปลง ค.ศ. เป็น พ.ศ.
                $year = (int)$date_parts[2] + 543; // เพิ่ม 543 เข้าไปในปี
                $date_input = $year . '-' . $date_parts[1] . '-' . $date_parts[0];
            }

            // บันทึกข้อมูลตามประเภท
            switch ($number_type) {
                case 'six_digit':
                    $award_type = calculateAwardType($db, 'six_digit_numbers', $date_input);
                    if (insertSixDigitNumber($db, $number_input, $date_input, $award_type)) {
                        $success = true;
                    }
                    break;
                case 'two_digit':
                    $award_type = calculateAwardType($db, 'two_digit_numbers', $date_input);
                    if (insertTwoDigitNumber($db, $number_input, $date_input, $award_type)) {
                        $success = true;
                    }
                    break;
                case 'first_three_digit':
                    $award_type = calculateAwardType($db, 'first_three_digit_numbers', $date_input);
                    if (insertFirstThreeDigitNumber($db, $number_input, $date_input, $award_type)) {
                        $success = true;
                    }
                    break;
                case 'last_three_digit':
                    $award_type = calculateAwardType($db, 'last_three_digit_numbers', $date_input);
                    if (insertLastThreeDigitNumber($db, $number_input, $date_input, $award_type)) {
                        $success = true;
                    }
                    break;
            }
        }
    }



// ปิดการเชื่อมต่อ
$db->close();

    // แสดงข้อความสำเร็จหรือไม่สำเร็จ
    if ($success) {
        echo "<script>
                alert('บันทึกข้อมูลเรียบร้อย!');
                window.location.href = 'form.php'; // กลับไปยังหน้า form.php
              </script>";
    } else {
        echo "<script>
                alert('บันทึกไม่สำเร็จ');
                window.location.href = 'form.php'; // กลับไปยังหน้า form.php
              </script>";
    }
}

?>
