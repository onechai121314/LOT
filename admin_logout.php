<?php
session_start();
session_destroy();
header('Location: admin_login.php'); // กลับไปยังหน้า login
exit;
?>
