<?php
$servername = "localhost";
$username = "root"; // یا نام کاربری پایگاه داده شما
$password = ""; // یا رمز عبور پایگاه داده شما
$dbname = "cafe";

// ایجاد اتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// بررسی اتصال
if ($conn->connect_error) {
    die("اتصال ناموفق: " . $conn->connect_error);
}
?>
