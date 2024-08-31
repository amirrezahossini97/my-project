<?php
require 'config.php'; // فایل پیکربندی پایگاه داده

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table_numbers = $_POST['table_number'] ?? [];

    // ایجاد اتصال
    $conn = new mysqli($servername, $username, $password, $dbname);

    // بررسی اتصال
    if ($conn->connect_error) {
        die("اتصال ناموفق: " . $conn->connect_error);
    }

    foreach ($table_numbers as $order_id => $table_number) {
        // بروزرسانی شماره میز و وضعیت تکمیل
        $sql = "UPDATE orders SET table_number = ?, completed = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $table_number, $order_id);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();
    header("Location: index.php"); // برگشت به صفحه اصلی بعد از بروزرسانی
    exit();
}
?>
