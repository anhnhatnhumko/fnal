<?php
session_start();
session_destroy(); // Xóa tất cả session
header("Location: login.php"); // Chuyển hướng về trang đăng nhập
exit();
?>
