<?php
require_once '../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

function safeQuery($conn, $query){
    $result = $conn->query($query);
    return $result ? $result->fetch_assoc()['total'] ?? 0 : 0;
}

/* ===== Stats ===== */
$products_count   = safeQuery($conn,"SELECT COUNT(*) as total FROM products");
$orders_count     = safeQuery($conn,"SELECT COUNT(*) as total FROM orders");
$pending_orders   = safeQuery($conn,"SELECT COUNT(*) as total FROM orders WHERE status='Pending'");
$completed_orders = safeQuery($conn,"SELECT COUNT(*) as total FROM orders WHERE status='Completed'");
$cancelled_orders = safeQuery($conn,"SELECT COUNT(*) as total FROM orders WHERE status='Cancelled'");
$total_revenue    = safeQuery($conn,"SELECT SUM(total_price) as total FROM orders WHERE status='Completed'");

/* ===== Daily Sales ===== */
$sales_data = [];
$days = [];
for($i=6;$i>=0;$i--){
    $date = date('Y-m-d', strtotime("-$i days"));
    $days[] = date('M d', strtotime($date));
    $result = $conn->query("SELECT SUM(total) as total FROM orders WHERE DATE(created_at)='$date'");
    $row = $result ? $result->fetch_assoc() : null;
    $sales_data[] = $row && $row['total'] ? $row['total'] : 0;
}

/* ===== Monthly Sales (Last 6 Months) ===== */
$monthly_labels = [];
$monthly_sales = [];
for($i=5;$i>=0;$i--){
    $month = date('Y-m', strtotime("-$i months"));
    $monthly_labels[] = date('M Y', strtotime($month));
    $result = $conn->query("SELECT SUM(total) as total FROM orders WHERE DATE_FORMAT(created_at,'%Y-%m')='$month'");
    $row = $result ? $result->fetch_assoc() : null;
    $monthly_sales[] = $row && $row['total'] ? $row['total'] : 0;
}

/* ===== Latest Products ===== */
$latest_products = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root{
--primary:#e17055;
--bg:#f4f6f9;
--card:#ffffff;
--text:#333;
}

body.dark{
--bg:#1e272e;
--card:#2f3640;
--text:#f5f6fa;
}

body{
margin:0;
font-family:Arial;
background:var(--bg);
color:var(--text);
display:flex;
transition:0.3s;
}

/* Sidebar */
.sidebar{
width:230px;
background:#2d3436;
color:#fff;
min-height:100vh;
padding:1.5rem;
}
.sidebar h2{color:#fdcb6e;}
.sidebar a{
display:block;
color:#fff;
padding:0.6rem 0;
text-decoration:none;
transition:0.2s;
}
.sidebar a:hover{color:#fdcb6e;}

/* Main */
.main{flex:1;padding:2rem;}
.header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:2rem;
}

/* Profile dropdown */
.profile{
position:relative;
cursor:pointer;
}
.profile-menu{
display:none;
position:absolute;
right:0;
top:40px;
background:var(--card);
box-shadow:0 4px 10px rgba(0,0,0,0.2);
border-radius:8px;
overflow:hidden;
}
.profile-menu a{
display:block;
padding:0.5rem 1rem;
color:var(--text);
text-decoration:none;
}
.profile-menu a:hover{background:#eee;}
.profile.show .profile-menu{display:block;}

/* Cards */
.cards{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
gap:1rem;
margin-bottom:2rem;
}
.card{
background:var(--card);
padding:1.5rem;
border-radius:10px;
box-shadow:0 4px 10px rgba(0,0,0,0.05);
transition:0.3s;
}
.card h3{margin:0;font-size:1rem;color:gray;}
.card p{
font-size:1.6rem;
font-weight:bold;
color:var(--primary);
}

/* Toggle */
.toggle-btn{
background:var(--primary);
border:none;
color:#fff;
padding:0.5rem 1rem;
border-radius:6px;
cursor:pointer;
}

/* Charts */
.chart-grid{
display:grid;
grid-template-columns:1fr 1fr;
gap:1rem;
margin-bottom:2rem;
}

/* Latest Products Table */
.table-wrapper { overflow-x:auto; margin-top:2rem; }
table { width:100%; border-collapse:collapse; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 5px 20px rgba(0,0,0,0.05); }
th, td { padding:12px 15px; text-align:left; border-bottom:1px solid #e5e7eb; }
th { background:#e17055; color:#fff; }
tr:hover { background:#fff5f0; }

@media(max-width:900px){
.chart-grid{grid-template-columns:1fr;}
}
</style>
</head>
<body>

<div class="sidebar">
<h2>Artisan Admin</h2>
<a href="#">Dashboard</a>
<a href="add_product.php">Add Product</a>
<a href="orders.php">Orders</a>
<a href="logout.php">Logout</a>
</div>

<div class="main">
<div class="header">
<h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></h1>

<div style="display:flex;gap:1rem;align-items:center;">
<button class="toggle-btn" onclick="toggleDark()">🌙 Dark</button>
<div class="profile" id="profileBox">
<strong>Admin ▾</strong>
<div class="profile-menu">
<a href="#">Profile</a>

</div>
</div>
</div>
</div>

<!-- Stats -->
<div class="cards">
<div class="card"><h3>Total Products</h3><p class="counter"><?php echo $products_count; ?></p></div>
<div class="card"><h3>Total Orders</h3><p class="counter"><?php echo $orders_count; ?></p></div>
<div class="card"><h3>Completed Orders</h3><p class="counter"><?php echo $completed_orders; ?></p></div>
<div class="card"><h3>Total Revenue</h3><p>₱<span class="counter"><?php echo $total_revenue; ?></span></p></div>
</div>

<!-- Charts -->
<div class="chart-grid">
<div class="card">
<h3>Daily Sales</h3>
<canvas id="dailyChart"></canvas>
</div>

<div class="card">
<h3>Monthly Sales</h3>
<canvas id="monthlyChart"></canvas>
</div>
</div>

<!-- Latest Products -->
<?php if($latest_products && $latest_products->num_rows > 0): ?>
<div class="table-wrapper">
<h3>Latest Products</h3>
<table>
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Price (₱)</th>
<th>Category</th>
<th>Status</th>
<th>Stock</th>
</tr>
</thead>
<tbody>
<?php while($row = $latest_products->fetch_assoc()): ?>
<tr>
<td><?php echo $row['id'];?></td>
<td><?php echo htmlspecialchars($row['name']);?></td>
<td><?php echo number_format($row['price'],2);?></td>
<td><?php echo htmlspecialchars($row['category']);?></td>
<td><?php echo $row['status'];?></td>
<td><?php echo $row['stock'];?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
<?php endif; ?>

</div>

<script>
/* Dark Mode */
function toggleDark(){document.body.classList.toggle('dark');}

/* Profile dropdown */
document.getElementById("profileBox").addEventListener("click",function(){
this.classList.toggle("show");
});

/* Animated Counter */
document.querySelectorAll(".counter").forEach(counter=>{
let update=()=>{
let target=+counter.innerText;
let count=0;
let increment=target/50;
let interval=setInterval(()=>{
count+=increment;
if(count>=target){
counter.innerText=target;
clearInterval(interval);
}else{
counter.innerText=Math.floor(count);
}
},20);
};
update();
});

/* Charts */
new Chart(document.getElementById('dailyChart'),{
type:'line',
data:{
labels: <?php echo json_encode($days); ?>,
datasets:[{
data: <?php echo json_encode($sales_data); ?>,
borderColor:'#e17055',
backgroundColor:'rgba(225,112,85,0.2)',
fill:true,
tension:0.3
}]
}
});

new Chart(document.getElementById('monthlyChart'),{
type:'bar',
data:{
labels: <?php echo json_encode($monthly_labels); ?>,
datasets:[{
data: <?php echo json_encode($monthly_sales); ?>,
backgroundColor:'#e17055'
}]
}
});

/* Auto-refresh counters every 30s */
setInterval(() => {
    fetch("fetch_stats.php")
    .then(res => res.json())
    .then(data => {
        document.querySelectorAll(".counter")[0].innerText = data.products;
        document.querySelectorAll(".counter")[1].innerText = data.orders;
        document.querySelectorAll(".counter")[2].innerText = data.completed;
        document.querySelectorAll(".counter")[3].innerText = data.revenue;
    });
}, 30000);
</script>

</body>
</html>