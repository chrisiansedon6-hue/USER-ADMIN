<?php
require_once '../config.php';
if(session_status()===PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['admin_id'])) header("Location: admin_login.php");

$id = intval($_POST['id'] ?? 0);
$stmt = $conn->prepare("UPDATE products SET is_archived=0 WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();

header("Location: archive.php");
exit;
?>