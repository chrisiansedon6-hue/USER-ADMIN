<?php
require_once '../config.php';
if(session_status()===PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['admin_id'])) header("Location: admin_login.php");

$archived_products = $conn->query("SELECT * FROM products WHERE is_archived=1 ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Archived Products</title>
<style>
body { font-family:sans-serif; padding:2rem; background:#f4f6f9;}
table { width:100%; border-collapse:collapse; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 5px 20px rgba(0,0,0,0.05);}
th, td { padding:12px 15px; border-bottom:1px solid #e5e7eb; text-align:left;}
th { background:#e17055; color:#fff;}
button { padding:5px 10px; border:none; background:#2ecc71; color:#fff; border-radius:6px; cursor:pointer;}
button:hover { background:#27ae60;}
</style>
</head>
<body>
<h2>Archived Products</h2>
<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>Price</th>
<th>Category</th>
<th>Stock</th>
<th>Status</th>
<th>Action</th>
</tr>
<?php while($row=$archived_products->fetch_assoc()): ?>
<tr>
<td><?php echo $row['id'];?></td>
<td><?php echo htmlspecialchars($row['name']);?></td>
<td>₱<?php echo number_format($row['price'],2);?></td>
<td><?php echo htmlspecialchars($row['category']);?></td>
<td><?php echo $row['stock'];?></td>
<td><?php echo $row['status'];?></td>
<td>
    <form method="POST" action="restore_product.php">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <button type="submit">Restore</button>
    </form>
</td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>