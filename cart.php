<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $product_id = intval($_POST['product_id']);
        switch ($_POST['action']) {
            case 'update':
                $quantity = intval($_POST['quantity']);
                if ($quantity > 0) {
                    foreach ($_SESSION['cart'] as &$item) {
                        if ($item['id'] == $product_id) {
                            $item['quantity'] = $quantity;
                            break;
                        }
                    }
                }
                break;
            case 'remove':
                foreach ($_SESSION['cart'] as $key => $item) {
                    if ($item['id'] == $product_id) {
                        unset($_SESSION['cart'][$key]);
                        $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index
                        break;
                    }
                }
                break;
            case 'clear':
                $_SESSION['cart'] = [];
                break;
        }
    }
    header('Location: cart.php');
    exit;
}

// Calculate totals
$cart_items = $_SESSION['cart'] ?? [];
$cart_total = 0;
foreach ($cart_items as $item) {
    $cart_total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shopping Cart - Artisan Pastries</title>
<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* Cart Page CSS */
.page-content { padding: 2rem; max-width: 1200px; margin: 0 auto; }
.page-title { text-align:center; margin-bottom:2rem; font-size:2rem; }

.cart-layout { display: flex; gap: 2rem; flex-wrap: wrap; }
.cart-items { flex: 2; display: flex; flex-direction: column; gap: 1rem; }
.cart-item { display: flex; gap: 1rem; align-items: center; border:1px solid #e5e7eb; border-radius:0.5rem; padding:1rem; background:#fff; }
.cart-item-image img { width:100px; height:100px; object-fit:cover; border-radius:0.5rem; }
.cart-item-details { flex:1; }
.cart-item-price { font-weight:bold; color:#e17055; }
.cart-item-quantity { display:flex; align-items:center; gap:0.5rem; }
.qty-input { width:50px; text-align:center; border:1px solid #ccc; border-radius:0.25rem; }
.qty-btn { padding:0.25rem 0.5rem; border:none; background:#e17055; color:#fff; font-weight:bold; cursor:pointer; border-radius:0.25rem; }
.qty-btn:disabled { background:#ccc; cursor:not-allowed; }
.cart-item-total { font-weight:bold; width:80px; text-align:right; }
.cart-item-remove button { background:none; border:none; color:#e17055; cursor:pointer; font-size:1.2rem; }

.cart-summary { flex:1; border:1px solid #e5e7eb; border-radius:0.5rem; padding:1rem; height:min-content; background:#fff; }
.cart-summary h2 { margin-bottom:1rem; }
.summary-row { display:flex; justify-content:space-between; margin-bottom:0.5rem; }
.summary-divider { height:1px; background:#e5e7eb; margin:0.5rem 0; }
.summary-total { font-weight:bold; }
.btn-checkout, .btn-clear-cart { display:block; width:100%; text-align:center; padding:0.75rem; margin-top:0.5rem; border:none; border-radius:0.5rem; font-weight:bold; cursor:pointer; }
.btn-checkout { background:#e17055; color:#fff; }
.btn-clear-cart { background:#fdcb6e; color:#333; }

.empty-state { text-align:center; padding:2rem; background:#fff; border-radius:0.5rem; }
.empty-state i { font-size:3rem; color:#e17055; margin-bottom:1rem; }
.empty-state h2 { margin-bottom:0.5rem; }
.empty-state p { margin-bottom:1rem; color:#6b7280; }


</style>
</head>
<body>

<header class="header">
<nav class="nav-container">
    <a href="index.php" class="logo"><i class="fas fa-shopping-bag"></i> Artisan Pastries</a>
    <div class="nav-menu" id="navMenu">
        <a href="index.php#menu" class="nav-link">Menu</a>
        <a href="cart.php" class="nav-link active"><i class="fas fa-shopping-cart"></i> Cart</a>
        <a href="orders.php" class="nav-link">My Orders</a>
        <a href="logout.php" class="btn-primary">Logout</a>
    </div>
    <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
</nav>
</header>

<div class="page-content">
    <h1 class="page-title">Shopping Cart</h1>

    <?php if (empty($cart_items)): ?>
        <div class="empty-state">
            <i class="fas fa-shopping-cart"></i>
            <h2>Your cart is empty</h2>
            <p>Add some delicious pastries to your cart!</p>
            <a href="index.php#menu" class="btn-primary-large">Browse Menu</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div class="cart-items">
                <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <div class="cart-item-image">
                        <?php
                        $item_name = $item['name'] ?? 'Product';
                        $image_path = !empty($item['image']) ? 'uploaded_img' . $item['image'] : '';
                        if ($image_path && file_exists($image_path)) {
                            echo '<img src="'.htmlspecialchars($image_path).'" alt="'.htmlspecialchars($item_name).'">';
                        } else {
                            echo '<img src="https://via.placeholder.com/100x100?text='.urlencode($item_name).'" alt="'.htmlspecialchars($item_name).'">';
                        }
                        ?>
                    </div>
                    <div class="cart-item-details">
                        <h3><?php echo htmlspecialchars($item_name); ?></h3>
                        <p class="cart-item-price">₱<?php echo number_format($item['price'],2); ?></p>
                    </div>
                    <div class="cart-item-quantity">
                        <form method="POST" action="cart.php" class="quantity-form">
                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                            <input type="hidden" name="action" value="update">
                            <button type="submit" name="quantity" value="<?php echo $item['quantity'] - 1; ?>" class="qty-btn" <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>>-</button>
                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="qty-input" onchange="this.form.submit()">
                            <button type="submit" name="quantity" value="<?php echo $item['quantity'] + 1; ?>" class="qty-btn">+</button>
                        </form>
                    </div>
                    <div class="cart-item-total">
                        <p>₱<?php echo number_format($item['price'] * $item['quantity'],2); ?></p>
                    </div>
                    <form method="POST" action="cart.php" class="cart-item-remove">
                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                        <input type="hidden" name="action" value="remove">
                        <button type="submit" class="btn-remove"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <h2>Order Summary</h2>
                <div class="summary-row"><span>Subtotal</span><span>₱<?php echo number_format($cart_total,2); ?></span></div>
                <div class="summary-row"><span>Delivery Fee</span><span>₱50.00</span></div>
                <div class="summary-divider"></div>
                <div class="summary-row summary-total"><span>Total</span><span>₱<?php echo number_format($cart_total + 50,2); ?></span></div>
                <a href="checkout.php" class="btn-checkout">Proceed to Checkout</a>
                <form method="POST" action="cart.php">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="btn-clear-cart">Clear Cart</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const navMenu = document.getElementById('navMenu');
mobileMenuBtn.addEventListener('click', () => navMenu.classList.toggle('open'));
</script>
</body>
</html>

