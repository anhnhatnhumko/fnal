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

// Truy vấn sản phẩm theo ID
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
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

// Xử lý cập nhật sản phẩm
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $name = htmlspecialchars($_POST['name']);
    $price = $_POST['price'];
    $category = htmlspecialchars($_POST['category']);
    $description = htmlspecialchars($_POST['description']);
    $stock = $_POST['stock'];
    $image = $product['image']; // Giữ ảnh cũ nếu không có ảnh mới

    // Xử lý upload ảnh mới
    if (!empty($_FILES['image']['name'])) {
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_name = uniqid() . "_" . basename($_FILES['image']['name']);
        $image_path = "assets/images/" . $image_name;
        $imageFileType = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        // Kiểm tra loại file ảnh
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowedTypes)) {
            die("Chỉ chấp nhận file JPG, JPEG, PNG, GIF.");
        }

        // Xóa ảnh cũ nếu tồn tại
        if (!empty($product['image']) && file_exists("assets/images/" . $product['image'])) {
            unlink("assets/images/" . $product['image']);
        }

        // Di chuyển ảnh vào thư mục
        if (!move_uploaded_file($image_tmp, $image_path)) {
            die("Lỗi khi tải lên ảnh.");
        }

        $image = $image_name; // Cập nhật ảnh mới
    }

    // Cập nhật dữ liệu trong database
    $updateQuery = "UPDATE products SET name=?, price=?, category=?, description=?, stock=?, image=?, updated_at=NOW() WHERE id=?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sdssisi", $name, $price, $category, $description, $stock, $image, $id);

    if ($stmt->execute()) {
        header("Location: dashboard.php?success=Sản phẩm đã được cập nhật!");
        exit();
    } else {
        echo "Lỗi cập nhật sản phẩm.";
    }
}

// Xử lý xóa sản phẩm
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
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
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa sản phẩm</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="page-content-edit">
        <div class="dashboard-nav">Sửa sản phẩm</div>

        <div class="container">
            <h2 class="title">Chỉnh sửa sản phẩm</h2>

            <form action="" method="post" enctype="multipart/form-data" class="edit-form">
                <div class="form-group">
                    <label for="name">Tên sản phẩm:</label>
                    <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($product['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="price">Giá ($):</label>
                    <input type="number" step="0.01" name="price" class="form-input" value="<?= $product['price']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="category">Loại sản phẩm:</label>
                    <input type="text" name="category" class="form-input" value="<?= htmlspecialchars($product['category']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Mô tả:</label>
                    <textarea name="description" class="form-input" required><?= htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="stock">Số lượng:</label>
                    <input type="number" name="stock" class="form-input" value="<?= $product['stock']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="image">Hình ảnh:</label>
                    <input type="file" name="image" class="form-file">
                    <div class="product-image">
                        <p>Ảnh hiện tại:</p>
                        <img src="assets/images/<?= $product['image']; ?>" alt="Ảnh hiện tại" width="150px">
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" name="update" class="btn update">Cập nhật</button>
                </div>
            </form>

            <!-- Nút Xóa Sản Phẩm -->
            <form action="" method="POST" class="delete-form" onsubmit="return confirmDelete();">
                <input type="hidden" name="delete" value="1">
                <button type="submit" class="btn delete">Xóa sản phẩm</button>
            </form>

            <a href="dashboard.php" class="back-link">Quay lại</a>
        </div>
    </div>

    <script>
    function confirmDelete() {
        return confirm("Bạn có chắc chắn muốn xóa sản phẩm này?");
    }
    </script>
</body>
</html>
