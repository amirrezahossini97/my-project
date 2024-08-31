<?php
session_start();
require 'config.php'; // فایل پیکربندی پایگاه داده
require 'includes/jdf.php'; // مسیر به فایل jdf.php

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

// گرفتن داده‌ها از پایگاه داده برای کاربر جاری
$sql = "SELECT id, name, phone, entry, notes, quantity, completed, table_number FROM orders WHERE user_id = ? ORDER BY id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// محاسبه تعداد افراد تیک خورده و تیک نخورده
$sql_count = "SELECT COUNT(*) AS total, SUM(completed) AS completed_count FROM orders WHERE user_id = ?";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("i", $user_id);
$stmt_count->execute();
$stmt_count->bind_result($total_count, $completed_count);
$stmt_count->fetch();
$stmt_count->close();

$not_completed_count = $total_count - $completed_count;

// گرفتن زمان ورود کاربر
$sql_login = "SELECT login_time FROM user_logins WHERE user_id = ? ORDER BY id DESC LIMIT 1";
$stmt_login = $conn->prepare($sql_login);
$stmt_login->bind_param("i", $user_id);
$stmt_login->execute();
$stmt_login->bind_result($login_time);
$stmt_login->fetch();
$stmt_login->close();

// گرفتن اطلاعات کاربر
$sql_user = "SELECT fullname FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$stmt_user->bind_result($username);
$stmt_user->fetch();

// تبدیل تاریخ ورود میلادی به شمسی
$login_timestamp = strtotime($login_time);
$login_jalali = jdate('Y/m/d', $login_timestamp, '', '', 'en');
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>پنل کاربری</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* استایل برای کم‌رنگ کردن ردیف‌های تکمیل شده */
        .completed {
            background-color: #e0e0e0;
        }
        /* استایل برای پاپ آپ */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            border-radius: 8px;
        }
        .close {
            color: #aaa;
            float: left;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .table-number {
            display: none;
            margin-left: 10px;
            color: #555;
        }
    </style>
    <script>
        var currentOrderId = null;

        function toggleTableNumber(checkbox, orderId) {
            var modal = document.getElementById('table-number-modal');
            var saveButton = document.getElementById('save-table-number');
            var tableNumberInput = document.getElementById('table-number-input');

            if (checkbox.checked) {
                currentOrderId = orderId;
                modal.style.display = 'flex';
                saveButton.onclick = function() {
                    var tableNumber = tableNumberInput.value;
                    var tableNumberDisplay = document.getElementById('table-number-display-' + orderId);
                    tableNumberDisplay.textContent = tableNumber;
                    tableNumberDisplay.style.display = 'inline'; // نمایش شماره میز
                    tableNumberInput.value = ''; // Clear input field
                    checkbox.checked = false; // Uncheck the checkbox after saving
                    modal.style.display = 'none';
                    var form = document.getElementById('order-update-form');
                    var hiddenField = document.createElement('input');
                    hiddenField.type = 'hidden';
                    hiddenField.name = 'table_number[' + orderId + ']';
                    hiddenField.value = tableNumber;
                    form.appendChild(hiddenField);
                    form.submit();
                }
            }
        }

        window.onclick = function(event) {
            var modal = document.getElementById('table-number-modal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        function updateTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('entry').value = `${hours}:${minutes}`;
        }

        // بروز رسانی زمان هر دقیقه
        setInterval(updateTime, 5000);

        // بروز رسانی زمان به صورت فوری
        window.onload = updateTime;
    </script>
</head>
<body>
    <div class="container">
        <div class="user-panel">
            <div class="user-info">
                <h2>خوش آمدید</h2>
                <p>نام میزبان: <?php echo htmlspecialchars($username); ?></p>
                <p>زمان ورود شما: <?php echo htmlspecialchars(date("H:i", strtotime($login_time))); ?></p>
                <p>تاریخ امروز: <?php echo htmlspecialchars($login_jalali); ?></p> <!-- اضافه کردن تاریخ شمسی -->
                <a href="logout.php" class="submit-btn">خروج</a>
            </div>
            
            <div class="main-content">
                <div class="order-form">
                    <h2>ثبت ویتینگ جدید</h2>
                    <form id="add-order-form" action="add_order.php" method="post">
                        <div class="form-group">
                            <label for="name">نام و نام خانوادگی:</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">شماره تماس:</label>
                            <input type="text" id="phone" name="phone" required pattern="\d{11}" title="شماره تماس باید 11 رقم باشد">
                        </div>
                        <div class="form-group">
                            <label for="entry">ورود:</label>
                            <input type="text" id="entry" name="entry" required readonly>
                        </div>
                        <div class="form-group">
                            <label for="notes">حیاط درخواستی:</label>
                            <textarea id="notes" name="notes"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="quantity">تعداد:</label>
                            <input type="number" id="quantity" name="quantity" required min="1">
                        </div>
                        <button type="submit" class="submit-btn">ارسال لیست ویت</button>
                    </form>
                </div>
                <div class="order-list">
                    <h2>لیست ویت ها</h2>
                    <div class="order-summary">
                        <p1>تعداد انجام شده : <?php echo $completed_count; ?></p1>
                        <p2>تعداد ویت ها: <?php echo $not_completed_count; ?></p2>
                    </div>
                    <form id="order-update-form" action="update_orders.php" method="post">
                        <table>
                            <thead>
                                <tr>
                                    <th>ردیف</th>
                                    <th>نام و نام خانوادگی</th>
                                    <th>شماره تماس</th>
                                    <th>ورود</th>
                                    <th>حیاط درخواستی</th>
                                    <th>تعداد</th>
                                    <th>وضعیت</th>
                                    <th>شماره میز</th> <!-- اضافه کردن ستون شماره میز -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $row_number = 1;
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        // فرمت کردن زمان برای حذف ثانیه‌ها
                                        $formatted_entry = date("H:i", strtotime($row["entry"]));
                                        $completed = $row["completed"] ? "completed" : "";

                                        echo "<tr class='$completed'>";
                                        echo "<td>" . $row_number . "</td>";
                                        echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["phone"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($formatted_entry) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["notes"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["quantity"]) . "</td>";
                                        echo "<td>";
                                        echo "<input type='checkbox' onchange='toggleTableNumber(this, " . $row["id"] . ")'";
                                        if ($row["completed"]) {
                                            echo " checked disabled";
                                        }
                                        echo ">";
                                        echo "</td>";
                                        echo "<td id='table-number-display-" . $row["id"] . "'";
                                        if ($row["table_number"]) {
                                            echo ">" . htmlspecialchars($row["table_number"]);
                                        } else {
                                            echo " style='display: none;'";
                                        }
                                        echo "</td>";
                                        echo "</tr>";
                                        $row_number++;
                                    }
                                } else {
                                    echo "<tr><td colspan='8'>هیچ سفارشی وجود ندارد</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="table-number-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>شماره میز</h2>
            <div class="form-group">
                <input type="text" id="table-number-input" required placeholder="شماره میز را وارد کنید">
            </div>
            <button id="save-table-number" class="submit-btn">ذخیره</button>
        </div>
    </div>

    <script>
        // بستن پاپ آپ
        var modal = document.getElementById('table-number-modal');
        var span = document.getElementsByClassName('close')[0];

        span.onclick = function() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>

<?php
// بستن اتصال
$conn->close();
?>
