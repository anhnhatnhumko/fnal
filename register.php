<?php
session_start();
include "includes/db.php";

if (isset($_SESSION["user"])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = trim($_POST['email']);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp!";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Tên đăng nhập đã tồn tại!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            if ($stmt->execute()) {
                $_SESSION["user"] = $stmt->insert_id;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Đăng ký thất bại!";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng ký</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <div class="page-register-body">
        <div class="register-container">
            <h2>Đăng ký</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Tên đăng nhập" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mật khẩu" required>
                <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>
                <button type="submit">Đăng ký</button>
            </form>
            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
        </div>
    </div>
</body>


</html>