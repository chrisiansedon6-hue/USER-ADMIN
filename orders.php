<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user orders
$orders_query = "SELECT o.*, 
                 GROUP_CONCAT(CONCAT(oi.quantity, 'x ', p.name) SEPARATOR ', ') as items
                 FROM orders o
                 LEFT JOIN order_items oi ON o.id = oi.order_id
                 LEFT JOIN products p ON oi.product_id = p.id
                 WHERE o.user_id = ?
                 GROUP BY o.id
                 ORDER BY o.created_at DESC";
$stmt = $conn->prepare($orders_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Artisan Pastries</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav-container">
            <a href="index.php" class="logo">
                <i class="fas fa-shopping-bag"></i>
                <span>Artisan Pastries</span>
            </a>
            <div class="nav-menu" id="navMenu">
                <a href="index.php#menu" class="nav-link">Menu</a>
                <a href="cart.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i> Cart
                </a>
                <a href="orders.php" class="nav-link active">My Orders</a>
                <a href="logout.php" class="btn-primary">Logout</a>
            </div>
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
        </nav>
    </header>

    <div class="page-content">
        <div class="container">
            <h1 class="page-title">My Orders</h1>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            
            <?php if ($orders_result && $orders_result->num_rows > 0): ?>
                <div class="orders-list">
                    <?php while($order = $orders_result->fetch_assoc()): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <h3>Order #<?php echo $order['id']; ?></h3>
                                    <p class="order-date">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="order-status">
                                    <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="order-items-list">
                                <p><strong>Items:</strong> <?php echo htmlspecialchars($order['items'] ?? 'No items'); ?></p>
                            </div>
                           <div class="order-footer">
    <div class="order-total">
        <span>Total:</span>
        <strong>₱<?php echo number_format($order['total_price'], 2); ?></strong>
    </div>
    <a href="order_details.php?order_id=<?php echo $order['id']; ?>" class="btn-view-order">
        View Details
    </a>
</div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-receipt"></i>
                    <h2>No orders yet</h2>
                    <p>Start ordering delicious pastries!</p>
                    <a href="index.php#menu" class="btn-primary-large">Browse Menu</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
