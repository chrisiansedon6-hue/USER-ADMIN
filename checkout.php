<?php
require_once 'config.php';

// ✅ Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Redirect if user not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// ✅ Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Initialize error variable
$error = "";

// ✅ Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id = $_SESSION['user_id'];
    $cart_items = $_SESSION['cart'];

    // Calculate total price
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $delivery_fee = 50; // fixed delivery fee
    $total_price = $subtotal + $delivery_fee;

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Insert into orders table
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, status) VALUES (?, ?, 'pending')");
        if (!$stmt) { throw new Exception("Prepare failed for orders: " . $conn->error); }
        $stmt->bind_param("id", $user_id, $total_price);
        $stmt->execute();
        $order_id = $conn->insert_id;

        // Insert order items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        if (!$stmt) { throw new Exception("Prepare failed for order_items: " . $conn->error); }
        foreach ($cart_items as $item) {
            $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
            $stmt->execute();
        }

        // Commit transaction
        $conn->commit();

        // Clear cart
        $_SESSION['cart'] = [];

        // Redirect to orders page with success message
        $_SESSION['success_message'] = "Order placed successfully!";
        header('Location: orders.php');
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $error = "Failed to place order: " . $e->getMessage();
    }
}

// ✅ Calculate totals for display
$cart_total = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['quantity'];
}
$delivery_fee = 50;
$total = $cart_total + $delivery_fee;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Artisan Pastries</title>
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
                <a href="orders.php" class="nav-link">My Orders</a>
                <a href="logout.php" class="btn-primary">Logout</a>
            </div>
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
        </nav>
    </header>

    <div class="page-content">
        <div class="container">
            <h1 class="page-title">Checkout</h1>

            <?php if (!empty($error)): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="checkout-layout">
                <!-- Delivery Form -->
                <div class="checkout-form-section">
                    <h2>Delivery Information</h2>
                    <form method="POST" action="checkout.php" id="checkoutForm">
                        <div class="form-group">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" id="name" name="name" class="form-input" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-input" placeholder="+63 912 345 6789" required>
                        </div>
                        <div class="form-group">
                            <label for="address" class="form-label">Delivery Address</label>
                            <textarea id="address" name="address" class="form-input" rows="3" placeholder="Street, Barangay, City, Province" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="notes" class="form-label">Order Notes (Optional)</label>
                            <textarea id="notes" name="notes" class="form-input" rows="2" placeholder="Any special instructions?"></textarea>
                        </div>

                        <h2 style="margin-top: 2rem;">Payment Method</h2>
                        <div class="payment-methods">
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="cod" checked>
                                <div class="payment-card">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <span>Cash on Delivery</span>
                                </div>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="gcash">
                                <div class="payment-card">
                                    <i class="fas fa-mobile-alt"></i>
                                    <span>GCash</span>
                                </div>
                            </label>
                        </div>

                        <button type="submit" class="btn-submit">Place Order</button>
                    </form>
                </div>

                <!-- Order Summary -->
                <div class="checkout-summary">
                    <h2>Order Summary</h2>
                    <div class="order-items">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="order-item">
                                <div class="order-item-info">
                                    <span><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></span>
                                </div>
                                <span>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="summary-divider"></div>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>₱<?php echo number_format($cart_total, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Delivery Fee</span>
                        <span>₱<?php echo number_format($delivery_fee, 2); ?></span>
                    </div>
                    <div class="summary-divider"></div>
                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <span>₱<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>