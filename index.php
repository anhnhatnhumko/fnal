<?php
session_start();
if (isset($_SESSION['user_id'])) {
    // Nếu đã đăng nhập, chuyển hướng đến dashboard
    header("Location: dashboard.php");
    exit();
} else {
    // Nếu chưa đăng nhập, chuyển hướng đến trang đăng nhập
    header("Location: login.php");
    exit();
}
?>
