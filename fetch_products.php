<?php
require_once 'config.php';

header('Content-Type: application/json');

$products = [];

$sql = "SELECT p.id, p.name, p.details, p.price, p.stock, p.status,
        (SELECT image FROM product_images WHERE product_id = p.id LIMIT 1) AS image
        FROM products p
        WHERE p.is_archived = 0
        AND p.status = 'Active'
        ORDER BY p.id DESC";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {

    $imagePath = "admin/uploaded_img/" . $row['image'];

    if (!empty($row['image']) && file_exists("admin/uploaded_img/" . $row['image'])) {
        $image = $imagePath;
    } else {
        $image = "https://via.placeholder.com/400x250?text=No+Image";
    }

    $products[] = [
        "id" => $row['id'],
        "name" => $row['name'],
        "details" => $row['details'],
        "price" => number_format($row['price'], 2),
        "stock" => $row['stock'],
        "image" => $image
    ];
}

echo json_encode($products);