<?php
require_once 'config.php';

// Redirect if user not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header('Location: orders.php');
    exit;
}

$order_id = intval($_GET['order_id']);

// Fetch order info
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();

if (!$order) {
    die("Order not found or you do not have permission to view it.");
}

// Fetch order items
$stmt = $conn->prepare("SELECT oi.quantity, oi.price, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Details - Artisan Pastries</title>
<link rel="stylesheet" href="styles.css">
<style>
body { font-family:'Quicksand', sans-serif; background:#f9f9f9; margin:0; padding:0; color:#333;}
.container { max-width:800px; margin:4rem auto; background:#fff; padding:2rem; border-radius:0.5rem; box-shadow:0 4px 16px rgba(0,0,0,0.1);}
h1 { text-align:center; color:#e17055; margin-bottom:1rem;}
table { width:100%; border-collapse:collapse; margin-top:1rem;}
th, td { padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb; text-align:left;}
th { background:#f3f4f6; color:#111827;}
.total { font-weight:bold; text-align:right; padding-top:1rem;}
.back-link { margin-top:1.5rem; text-align:center;}
.back-link a { color:#e17055; font-weight:500; }
</style>
</head>
<body>

<div class="container">
    <h1>Order #<?php echo $order['id']; ?> Details</h1>
    <p><strong>Order Date:</strong> <?php echo date("F j, Y, g:i a", strtotime($order['created_at'])); ?></p>
    <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total = 0;
            while ($item = $items_result->fetch_assoc()):
                $subtotal = $item['quantity'] * $item['price'];
                $total += $subtotal;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>₱<?php echo number_format($item['price'],2); ?></td>
                <td>₱<?php echo number_format($subtotal,2); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <p class="total">Total: ₱<?php echo number_format($total,2); ?></p>

    <div class="back-link">
        <a href="orders.php">← Back to My Orders</a>
    </div>
</div>

</body>
</html>