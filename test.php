<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Warning Box</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
    }

    .warning-box {
      padding: 15px;
      background-color: #ffcccb;
      color: #a94442;
      border: 1px solid #f5c6cb;
      border-radius: 5px;
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .warning-box .close-btn {
      background: none;
      border: none;
      font-size: 20px;
      color: #a94442;
      cursor: pointer;
    }

    .warning-box .close-btn:hover {
      color: #721c24;
    }
  </style>
</head>
<body>

  <div class="warning-box">
    <span>คำเตือน: กล่องสุ่มตัวเลขนั้นมีไว้เล่นเพื่อความบันเทิงเท่านั้น นอกเหนือจากนั้นทางแอดมินจะไม่รับผิดชอบใดทั้งสิ้น</span>
    <button class="close-btn" onclick="closeWarning(this)"></button>
  </div>

  <script>
    function closeWarning(button) {
      const box = button.parentElement;
      box.style.display = 'none';
    }
  </script>

</body>
</html>
