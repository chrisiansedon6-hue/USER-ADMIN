<?php
require_once '../config.php';

function safeQuery($conn, $query){
    $result = $conn->query($query);
    return $result ? $result->fetch_assoc()['total'] ?? 0 : 0;
}

echo json_encode([
    "products" => safeQuery($conn,"SELECT COUNT(*) as total FROM products"),
    "orders" => safeQuery($conn,"SELECT COUNT(*) as total FROM orders"),
    "completed" => safeQuery($conn,"SELECT COUNT(*) as total FROM orders WHERE status='Completed'"),
    "revenue" => safeQuery($conn,"SELECT SUM(total) as total FROM orders WHERE status='Completed'")
]);