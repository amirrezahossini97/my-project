<?php
header('Content-Type: application/json');

// تنظیم ناحیه زمانی به تهران
date_default_timezone_set('Asia/Tehran');

function getCurrentTime() {
    $now = new DateTime();
    return $now->format(' H:i'); // فرمت YYYY-MM-DD HH:MM
}

echo json_encode(['currentTime' => getCurrentTime()]);
?>
