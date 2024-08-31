<?php
require 'vendor/autoload.php'; // بارگذاری Composer

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// تنظیمات پایگاه داده
$servername = "localhost";
$username = "root";
$password = ""; // رمز عبور پایگاه داده خود را وارد کنید
$dbname = "cafe";

// اتصال به پایگاه داده
$conn = new mysqli($servername, $username, $password, $dbname);

// بررسی اتصال
if ($conn->connect_error) {
    die("اتصال ناموفق: " . $conn->connect_error);
}

// گرفتن تاریخ امروز
$today = date("Y-m-d");

// گرفتن داده‌ها از پایگاه داده
$sql = "SELECT name, phone, entry, notes, quantity, completed, table_number FROM orders WHERE DATE(entry) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();

// ایجاد یک شیء Spreadsheet جدید
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('List of Orders');

// افزودن هدرها
$headers = ['نام و نام خانوادگی', 'شماره تماس', 'ورود', 'توضیحات', 'تعداد', 'تیک', 'شماره میز'];
$sheet->fromArray($headers, NULL, 'A1');

// افزودن داده‌ها
$rowIndex = 2;
while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowIndex, $row['name']);
    $sheet->setCellValue('B' . $rowIndex, $row['phone']);
    $sheet->setCellValue('C' . $rowIndex, $row['entry']);
    $sheet->setCellValue('D' . $rowIndex, $row['notes']);
    $sheet->setCellValue('E' . $rowIndex, $row['quantity']);
    $sheet->setCellValue('F' . $rowIndex, $row['completed'] ? 'تیک شده' : 'تیک نشده');
    $sheet->setCellValue('G' . $rowIndex, $row['table_number']);
    $rowIndex++;
}

// نوشتن داده‌ها به فایل Excel
$writer = new Xlsx($spreadsheet);
$fileName = 'list_of_orders_' . $today . '.xlsx';
$writer->save($fileName);

// بستن اتصال
$conn->close();

// خروجی دادن به کاربر
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $fileName . '"');
header('Cache-Control: max-age=0');
readfile($fileName);

// حذف فایل موقت
unlink($fileName);
?>
