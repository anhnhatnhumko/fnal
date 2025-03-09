<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$limit = 4;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;


$whereClauses = [];
$params = [];
$types = "";

// Lọc theo giá
if (!empty($_GET['min_price']) && is_numeric($_GET['min_price'])) {
    $whereClauses[] = "price >= ?";
    $params[] = $_GET['min_price'];
    $types .= "d";
}
if (!empty($_GET['max_price']) && is_numeric($_GET['max_price'])) {
    $whereClauses[] = "price <= ?";
    $params[] = $_GET['max_price'];
    $types .= "d";
}

// Lọc theo loại sản phẩm
if (!empty($_GET['category'])) {
    $whereClauses[] = "category = ?";
    $params[] = $_GET['category'];
    $types .= "s";
}

$whereSql = !empty($whereClauses) ? " WHERE " . implode(" AND ", $whereClauses) : "";

$query = "SELECT * FROM products $whereSql LIMIT ?, ?";
$params[] = $start;
$params[] = $limit;
$types .= "ii";

$stmt = $conn->prepare($query);

// Kiểm tra nếu có tham số mới bind_param()
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Đếm tổng số sản phẩm
$totalQuery = "SELECT COUNT(id) AS total FROM products $whereSql";
$totalStmt = $conn->prepare($totalQuery);

// Bind tham số nhưng loại bỏ `LIMIT ?, ?`
if (!empty($whereClauses)) {
    $filteredParams = array_slice($params, 0, -2);
    $filteredTypes = substr($types, 0, -2);
    if (!empty($filteredParams)) {
        $totalStmt->bind_param($filteredTypes, ...$filteredParams);
    }
}

$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$totalRow = $totalResult->fetch_assoc();
$totalProducts = $totalRow['total'];
$totalPages = ceil($totalProducts / $limit);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="page-dashboard">
            <h2>Danh sách sản phẩm</h2>

            <!-- Bộ lọc sản phẩm
            <form method="GET" action="" class="filter-form">
                <label for="min_price">Giá từ:</label>
                <input type="number" name="min_price" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>" step="0.01">

                <label for="max_price">đến:</label>
                <input type="number" name="max_price" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>" step="0.01">

                <label for="category">Loại sản phẩm:</label>
                <input type="text" name="category" value="<?= htmlspecialchars($_GET['category'] ?? '') ?>">

                <button type="submit" class="btn filter-btn">Lọc</button>
                <a href="dashboard.php" class="btn reset-btn">Reset</a>
            </form> -->

            <!-- Danh sách sản phẩm -->
            <div class="product-container">
                <?php
                $productCount = 0; // Đếm số sản phẩm trên trang hiện tại
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $productCount++; // Tăng biến đếm
                ?>
                        <div class="product-card">
                            <img src="assets/images/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <p class="price">$<?php echo number_format($row['price'], 2, '.', ','); ?></p>
                            <p class="category"><?php echo htmlspecialchars($row['category']); ?></p>
                            <div class="actions">
                                <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="edit-btn">Sửa</a>
                            </div>
                        </div>
                <?php
                    }
                }

                // Thêm các thẻ trống nếu số sản phẩm < 4 để giữ bố cục cố định
                while ($productCount < 4) {
                    echo '<div class="product-card empty-card"></div>';
                    $productCount++;
                }
                ?>
            </div>


            <!-- Phân trang -->
            <div class="pagination">
                <?php
                // Loại bỏ tham số 'page' trước khi xây dựng query string
                $queryParams = $_GET;
                unset($queryParams['page']);

                for ($i = 1; $i <= $totalPages; $i++) {
                    $queryString = http_build_query(array_merge($queryParams, ['page' => $i]));
                ?>
                    <a href="?<?= $queryString; ?>" class="<?= ($i == $page) ? 'active' : ''; ?>">
                        <?= $i; ?>
                    </a>
                <?php } ?>
            </div>
            <a href="add_product.php" class="floating-btn">
                ➕
            </a>
        </div>
    </div>

    <script>
        function confirmDelete(id) {
            if (confirm("Bạn có chắc chắn muốn xóa sản phẩm này?")) {
                window.location.href = "delete_product.php?id=" + id;
            }
        }
    </script>
</body>
<?php include 'includes/footer.php'; ?>

</html>