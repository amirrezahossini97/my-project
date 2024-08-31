<?php
session_start();
require 'config.php'; // فایل پیکربندی پایگاه داده

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ایجاد اتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// بررسی اتصال
if ($conn->connect_error) {
    die("اتصال ناموفق: " . $conn->connect_error);
}

// بررسی اینکه آیا فرم ارسال شده است
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // اعتبارسنجی داده‌ها
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $phone = filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING);
    $entry = filter_var(trim($_POST['entry']), FILTER_SANITIZE_STRING);
    $notes = filter_var(trim($_POST['notes']), FILTER_SANITIZE_STRING);
    $quantity = filter_var(trim($_POST['quantity']), FILTER_SANITIZE_NUMBER_INT);

    // بررسی اینکه مقادیر خالی نباشند
    if (!empty($name) && !empty($phone) && !empty($entry) && !empty($quantity)) {
        // افزودن داده‌ها به جدول
        $sql = "INSERT INTO orders (user_id, name, phone, entry, notes, quantity) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssi", $user_id, $name, $phone, $entry, $notes, $quantity);

        if ($stmt->execute()) {
            // موفقیت‌آمیز بودن ذخیره داده‌ها
            header("Location: index.php?status=success"); // بازگشت به صفحه اصلی
            exit();
        } else {
            echo "خطا: " . $stmt->error;
        }

        // بستن اتصال
        $stmt->close();
    } else {
        echo "لطفاً تمامی فیلدها را پر کنید.";
    }
}

// بستن اتصال
$conn->close();
?>
