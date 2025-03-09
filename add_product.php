<?php
include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $stock = $_POST['stock'];
    $image = "";

    // Kiểm tra & xử lý upload ảnh
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $targetDir = "assets/images/";
        $originalFileName = basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));

        // Tạo tên file duy nhất
        $newFileName = uniqid() . "." . $imageFileType;
        $targetFile = $targetDir . $newFileName;

        // Kiểm tra định dạng ảnh hợp lệ
        $allowedTypes = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($imageFileType, $allowedTypes)) {
            echo "<script>alert('Chỉ chấp nhận file JPG, JPEG, PNG, GIF.');</script>";
            exit();
        }

        // Upload file vào thư mục
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $image = $newFileName; // Lưu tên file vào database
        } else {
            echo "<script>alert('Lỗi upload ảnh.');</script>";
            exit();
        }
    }

    // Thêm sản phẩm vào database
    $stmt = $conn->prepare("INSERT INTO products (name, price, category, description, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdssis", $name, $price, $category, $description, $stock, $image);

    if ($stmt->execute()) {
        echo "<script>alert('Thêm sản phẩm thành công!'); window.location.href = 'dashboard.php';</script>";
    } else {
        echo "<script>alert('Lỗi: " . $stmt->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm sản phẩm</title>
    <link rel="stylesheet" href="assets/css/styles.css"> <!-- Link đến file CSS đã biên dịch từ SCSS -->
</head>
<body class="page-add-product">

    <h2>Thêm sản phẩm</h2>

    <form method="POST" enctype="multipart/form-data">
        <label for="name">Tên sản phẩm:</label>
        <input type="text" id="name" name="name" required>

        <label for="price">Giá:</label>
        <input type="number" id="price" name="price" required>

        <label for="category">Danh mục:</label>
        <input type="text" id="category" name="category" required>

        <label for="description">Mô tả:</label>
        <textarea id="description" name="description"></textarea>

        <label for="stock">Số lượng:</label>
        <input type="number" id="stock" name="stock" required>

        <label for="image">Hình ảnh:</label>
        <input type="file" id="image" name="image" required>

        <button type="submit">Thêm sản phẩm</button>
    </form>

</body>
</html>