<?php
session_start();
require 'config.php'; // فایل پیکربندی پایگاه داده
require 'includes/jdf.php'; // مسیر به فایل jdf.php

if (!isset($_SESSION['user_id'])) {
    echo "لطفاً ابتدا وارد شوید.";
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
?>

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
                    <th>شماره میز</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $row_number = 1;
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
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

<script>
    // بستن پاپ آپ
    var modal = document.getElementById('table-number-modal');
    var span = document.getElementsByClassName("close")[0];
    span.onclick = function() {
        modal.style.display = "none";
    }
</script>

<?php
$conn->close();
?>
