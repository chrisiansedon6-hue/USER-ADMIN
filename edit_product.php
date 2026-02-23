<?php
require_once '../config.php';
if(session_status()===PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['admin_id'])) header("Location: admin_login.php");

$id = intval($_GET['id'] ?? 0);
$product = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();

if(!$product) {
    die("Product not found!");
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $name = trim($_POST['name']);
    $details = trim($_POST['details']);
    $price = floatval($_POST['price']);
    $category = $_POST['category'];
    $stock = intval($_POST['stock']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE products SET name=?, details=?, price=?, category=?, stock=?, status=? WHERE id=?");
    $stmt->bind_param("ssdissi", $name, $details, $price, $category, $stock, $status, $id);
    $stmt->execute();

    header("Location: add_product.php");
    exit;
}
?>