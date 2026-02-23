<?php
require_once '../config.php';

// Start session safely
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect if admin not logged in
if(!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle status update
if(isset($_POST['update_status'])){
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    if($stmt){
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
        $stmt->close();
    }

    // Redirect to avoid form resubmission
    header('Location: orders.php');
    exit;
}

// Fetch orders with user names
$orders = $conn->query("
    SELECT o.*, u.name AS user_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Orders Management</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
/* Base */
body { margin:0; font-family:'Inter', sans-serif; background:#f4f6f9; color:#333; }
h2 { margin-bottom:25px; font-size:26px; }

/* Sidebar */
.sidebar {
    width:230px; background:#1e272e; color:#fff;
    position:fixed; height:100%; padding-top:30px; transition:0.3s;
}
.sidebar h2 { text-align:center; font-size:22px; margin-bottom:30px; }
.sidebar a {
    display:block; padding:14px 24px; color:#d2dae2; text-decoration:none;
    margin-bottom:4px; border-radius:8px; transition:0.3s;
}
.sidebar a:hover { background:#485460; color:#fff; }

/* Main */
.main { margin-left:230px; padding:40px 30px; }

/* Orders Grid */
.orders-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
    gap:20px;
}

/* Order Card */
.order-card {
    background:#fff;
    border-radius:15px;
    padding:20px;
    box-shadow:0 10px 25px rgba(0,0,0,0.05);
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    transition:0.3s;
}
.order-card:hover { transform:translateY(-3px); box-shadow:0 15px 30px rgba(0,0,0,0.08); }

.order-header { display:flex; justify-content:space-between; margin-bottom:12px; }
.order-id { font-weight:700; color:#e17055; }
.order-date { font-size:13px; color:#666; }

.order-info { margin-bottom:15px; }
.order-info p { margin-bottom:6px; font-size:14px; }

/* Status Badge */
.badge {
    padding:6px 14px;
    border-radius:20px;
    font-size:13px;
    font-weight:600;
    display:inline-block;
}
.Pending { background:#ffeaa7; color:#b7791f; }
.Completed { background:#d4edda; color:#2f855a; }
.Cancelled { background:#f8d7da; color:#c0392b; }

/* Form */
select {
    padding:6px 10px; border-radius:8px; border:1px solid #ccc; font-size:14px;
    background:#fff;
}
button {
    padding:6px 12px; border:none; border-radius:8px;
    background:#e17055; color:white; cursor:pointer;
    font-weight:600; transition:0.3s; margin-left:5px;
}
button:hover { background:#d35400; transform:translateY(-2px); }

/* Responsive */
@media(max-width:900px){
    .sidebar { position:relative; width:100%; height:auto; }
    .main { margin-left:0; padding:20px; }
}
</style>
</head>
<body>

<div class="sidebar">
    <h2>🍰 Artisan Admin</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="add_product.php">Add Product</a>
    <a href="orders.php">Orders</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">
    <h2>📦 Orders Management</h2>

    <div class="orders-grid">
        <?php while($o=$orders->fetch_assoc()): ?>
        <div class="order-card">
            <div class="order-header">
                <span class="order-id">#<?php echo $o['id']; ?></span>
                <span class="order-date"><?php echo date("M d, Y", strtotime($o['created_at'])); ?></span>
            </div>

            <div class="order-info">
                <p><strong>User:</strong> <?php echo htmlspecialchars($o['user_name']); ?></p>
                <p><strong>Total:</strong> ₱<?php echo number_format($o['total_price'],2); ?></p>
                <p><strong>Status:</strong> <span class="badge <?php echo $o['status']; ?>"><?php echo $o['status']; ?></span></p>
            </div>

            <form method="POST" style="display:flex; align-items:center; margin-top:auto;">
                <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                <select name="status">
                    <option value="Pending" <?php if($o['status']=='Pending') echo 'selected'; ?>>Pending</option>
                    <option value="Completed" <?php if($o['status']=='Completed') echo 'selected'; ?>>Completed</option>
                    <option value="Cancelled" <?php if($o['status']=='Cancelled') echo 'selected'; ?>>Cancelled</option>
                </select>
                <button type="submit" name="update_status">✔ Update</button>
            </form>
        </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>