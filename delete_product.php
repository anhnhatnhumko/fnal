<?php
session_start();
include 'includes/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Kiểm tra ID sản phẩm
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php?error=Sản phẩm không hợp lệ!");
    exit();
}

$id = $_GET['id'];

// Lấy thông tin sản phẩm để xóa ảnh (nếu có)
$stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

// Nếu sản phẩm không tồn tại
if (!$product) {
    header("Location: dashboard.php?error=Sản phẩm không tồn tại!");
    exit();
}

// Xóa ảnh trên server (nếu có)
if (!empty($product['image']) && file_exists("assets/images/" . $product['image'])) {
    unlink("assets/images/" . $product['image']);
}

// Xóa sản phẩm khỏi database
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: dashboard.php?success=Sản phẩm đã được xóa!");
} else {
    header("Location: dashboard.php?error=Lỗi khi xóa sản phẩm!");
}

$stmt->close();
$conn->close();
exit();
