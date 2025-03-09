<?php
session_start();
include 'includes/db.php';

// Xử lý đăng nhập
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Mật khẩu không đúng!";
        }
    } else {
        $error = "Tên đăng nhập không tồn tại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <div class="page-login-body">
    <div class="page-login">
            <div class="login-container">
                <form action="login.php" method="POST" class="login-form">
                    <h2>Đăng Nhập</h2>
                    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
                    <div class="input-group">
                        <input type="text" name="username" placeholder="Tên đăng nhập" required>
                    </div>
                    <div class="input-group">
                        <input type="password" name="password" placeholder="Mật khẩu" required>
                    </div>
                    <button type="submit" class="btn">Đăng Nhập</button>
                    <p class="register-link">Chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
                </form>
            </div>
        </div>
    </div>


</body>

</html>