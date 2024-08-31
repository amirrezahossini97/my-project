<?php
session_start();
require 'config.php'; // فایل پیکربندی پایگاه داده

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = filter_var(trim($_POST['username']), FILTER_SANITIZE_STRING);
    $password = filter_var(trim($_POST['password']), FILTER_SANITIZE_STRING);

    if (!empty($username) && !empty($password)) {
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            echo "کاربر با موفقیت ایجاد شد.";
        } else {
            echo "خطا: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "لطفاً تمامی فیلدها را پر کنید.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>ایجاد کاربر جدید</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="title-box">
            ایجاد کاربر جدید
        </div>
        <form action="create_user.php" method="post">
            <div class="form-group">
                <label for="username">نام کاربری:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">کلمه عبور:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="submit-btn">ایجاد کاربر</button>
        </form>
    </div>
</body>
</html>
<style>
         body {
            font-family: Arial, sans-serif;
            direction: rtl;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            
        }
        
        .container {
            height: auto;
            max-width: 400px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
                
        .title-box {
            color: #black;
            padding: 20px;
            text-align: center;
            font-size: 1.5em;
            font-weight: bold;
            margin-top:-80px;
            margin-left: -168px;
        }
        
        .form-content {
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .submit-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #red;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            font-size: 1em;
            margin-top: 15px;
        }
        
        .submit-btn:hover {
            background-color: #0056b3;
        }
        
        .form-footer {
            text-align: center;
            margin-top: 15px;
        }
        
        .form-footer a {
            color: #007bff;
            text-decoration: none;
        }
        
        .form-footer a:hover {
            text-decoration: underline;
        }
    </style>