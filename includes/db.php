<?php
$host = "localhost";    
$user = "root";
$password = "101204";
$dbname = "products_management";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>
