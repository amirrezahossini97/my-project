<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cafe";

// ایجاد اتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// بررسی اتصال
if ($conn->connect_error) {
    die("اتصال ناموفق: " . $conn->connect_error);
}

// گرفتن داده‌ها از پایگاه داده
$sql = "SELECT id, name, phone, entry, notes FROM orders";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["name"] . "</td>";
        echo "<td>" . $row["phone"] . "</td>";
        echo "<td>" . $row["entry"] . "</td>";
        echo "<td>" . $row["notes"] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5'>هیچ سفارشی وجود ندارد</td></tr>";
}

// بستن اتصال
$conn->close();
?>
