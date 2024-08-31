<?php
require 'config.php'; // فایل پیکربندی پایگاه داده

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashedPassword);

        if ($stmt->execute()) {
            echo "ثبت نام با موفقیت انجام شد.";
        } else {
            echo "خطا: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "لطفاً تمام فیلدها را پر کنید.";
    }
}


$conn->close();
?>
