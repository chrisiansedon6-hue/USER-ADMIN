<?php
require_once '../config.php';
$id = intval($_GET['id']);
$img = $conn->query("SELECT * FROM product_images WHERE id=$id")->fetch_assoc();
unlink("uploaded_img/".$img['image']);
$conn->query("DELETE FROM product_images WHERE id=$id");
header("Location: ".$_SERVER['HTTP_REFERER']);