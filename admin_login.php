<?php
session_start();
$db = new SQLite3('data.db');

// ตรวจสอบการส่งฟอร์ม
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ตรวจสอบข้อมูลในฐานข้อมูล
    $stmt = $db->prepare('SELECT * FROM admins WHERE username = :username');
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($result && password_verify($password, $result['password'])) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin_page.php'); // เปลี่ยนเส้นทางไปยังหน้า admin_page.php
        exit;
    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง!";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <!-- <title>เข้าสู่ระบบผู้ดูแลระบบ</title> -->
    <link rel="stylesheet" href="styles.css">
    <style>
        /* จัดสไตล์พื้นฐาน */
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom right, #4caf50, #2196f3);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #fff;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #fff;
        }

        /* กล่องฟอร์ม */
        .login-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            background: #4caf50;
            color: #fff;
            padding: 10px 15px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background: #45a049;
        }

        /* แสดงข้อความข้อผิดพลาด */
        .error-message {
            color: red;
            margin-bottom: 15px;
        }
        h1{
            color: #333;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>เข้าสู่ระบบผู้ดูแลระบบ</h1>
        <?php if (isset($error)) { echo "<p class='error-message'>$error</p>"; } ?>
        <form method="POST">
            <label for="username">ชื่อผู้ใช้:</label>
            <input type="text" name="username" id="username" required>
            <label for="password">รหัสผ่าน:</label>
            <input type="password" name="password" id="password" required>
            <button type="submit">เข้าสู่ระบบ</button>
        </form>
    </div>
</body>
</html>

