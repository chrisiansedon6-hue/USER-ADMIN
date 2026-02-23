<?php
require_once '../config.php';
if(session_status()===PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['admin_id'])) header("Location: admin_login.php");

// Messages
$message = "";
$error = "";

// Handle Add Product
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_product'])){
    $name = trim($_POST['name']);
    $details = trim($_POST['details']);
    $price = floatval($_POST['price']);
    $category = $_POST['category'];
    $stock = intval($_POST['stock']);
    $status = $_POST['status'];

    if(empty($name)||empty($details)||empty($price)||empty($category)){
        $error = "Please fill all required fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO products(name,details,price,category,stock,status) VALUES(?,?,?,?,?,?)");
        $stmt->bind_param("ssdiss",$name,$details,$price,$category,$stock,$status);
        $stmt->execute();
        $product_id = $stmt->insert_id;

        $allowed = ['jpg','jpeg','png','webp'];
        foreach($_FILES['images']['name'] as $key => $img){
            $tmp = $_FILES['images']['tmp_name'][$key];
            $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
            if(in_array($ext,$allowed)){
                $new_name = uniqid().".".$ext;
                move_uploaded_file($tmp,"uploaded_img/".$new_name);
                $stmt2 = $conn->prepare("INSERT INTO product_images(product_id,image) VALUES(?,?)");
                $stmt2->bind_param("is",$product_id,$new_name);
                $stmt2->execute();
            }
        }
        $message = "Product added successfully!";
    }
}

// Fetch Active Products
$latest_products = $conn->query("SELECT * FROM products WHERE is_archived=0 ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Products Management - Admin</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<style>
/* Base */
body { margin:0; font-family:'Poppins',sans-serif; background:#f0f2f5; color:#333; transition:0.3s; }
body.dark{background:#1e272e; color:#f5f6fa;}
a{cursor:pointer;}

/* Sidebar */
.sidebar {
  position:fixed; top:0; left:0; width:220px; height:100%; background:#2d3436; padding:2rem 1rem;
}
.sidebar h2 { color:#e17055; text-align:center; margin-bottom:2rem; }
.sidebar a { display:block; color:#dfe6e9; margin:1rem 0; font-weight:500; text-decoration:none; padding:8px; border-radius:6px; transition:0.3s; }
.sidebar a:hover { background:#485460; color:#fff; }

/* Main */
.main { margin-left:240px; padding:2rem; }

/* Form Card */
.form-card { background:#fff; padding:2rem; border-radius:20px; box-shadow:0 10px 30px rgba(0,0,0,0.08); max-width:900px; margin:auto; transition:0.3s;}
body.dark .form-card {background:#2f3640; color:#f5f6fa;}
label { font-weight:600; display:block; margin-top:1rem; }
input, select, textarea { width:100%; padding:0.8rem; border-radius:12px; border:1px solid #ccc; margin-top:0.5rem;}
textarea { min-height:120px; }
button { margin-top:1.5rem; padding:1rem; width:100%; background:linear-gradient(135deg,#e17055,#d35400); color:#fff; border:none; border-radius:12px; font-weight:600; cursor:pointer; }
button:hover { transform:translateY(-2px); box-shadow:0 10px 25px rgba(211,84,0,0.3); }

/* Dropzone */
.drop-zone { border:2px dashed #e17055; padding:40px; text-align:center; border-radius:15px; margin-top:1rem; cursor:pointer; }
.drop-zone.dragover { background:#ffe8e0; border-color:#d35400; transform:scale(1.02); }
.preview { display:flex; flex-wrap:wrap; gap:10px; margin-top:1rem;}
.preview img { width:100px; height:100px; object-fit:cover; border-radius:12px; }

/* Table */
.table-wrapper { margin-top:3rem; overflow-x:auto; }
table { width:100%; border-collapse:collapse; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 5px 20px rgba(0,0,0,0.05);}
th, td { padding:12px 15px; border-bottom:1px solid #e5e7eb; text-align:left;}
th { background:#e17055; color:#fff;}
tr:hover { background:#fff5f0; }
body.dark table { background:#2f3640; color:#f5f6fa; }
body.dark th { background:#e17055; color:#fff;}
body.dark tr:hover { background:#3d3d3d; }

/* Messages */
.success { background:#d4edda; color:#155724; padding:12px; border-radius:10px; margin-bottom:1rem; font-weight:500;}
.error { background:#f8d7da; color:#721c24; padding:12px; border-radius:10px; margin-bottom:1rem; font-weight:500;}
</style>
</head>
<body>

<div class="sidebar">
    <h2>🍰 Artisan Admin</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="add_product.php">Products</a>
    <a href="orders.php">Orders</a>
    <a href="archive.php">Archive</a>
    <a href="logout.php">Logout</a>
    <button onclick="toggleDark()" style="margin-top:1rem;padding:0.5rem 1rem;">🌙 Dark Mode</button>
</div>

<div class="main">
<div class="form-card">
<h2>Add Product</h2>
<?php if($message): ?><div class="success"><?php echo $message;?></div><?php endif; ?>
<?php if($error): ?><div class="error"><?php echo $error;?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<label>Product Name</label>
<input type="text" name="name" required>

<label>Details</label>
<textarea name="details" required></textarea>

<label>Price (₱)</label>
<input type="number" step="0.01" name="price" required>

<label>Category</label>
<select name="category" required>
    <option value="">-- Select Category --</option>
    <option value="Drinks">Drinks</option>
    <option value="Breads">Breads</option>
    <option value="Cakes">Cakes</option>
    <option value="Pastries">Pastries</option>
    <option value="Cookies">Cookies</option>
    <option value="Sandwiches">Sandwiches</option>
    <option value="Others">Others</option>
</select>

<label>Stock Quantity</label>
<input type="number" name="stock" value="0" required>

<label>Status</label>
<select name="status">
    <option value="Active">Active</option>
    <option value="Hidden">Hidden</option>
</select>

<label>Product Images (Multiple)</label>
<div class="drop-zone" id="dropZone">Drag & Drop Images Here or Click
<input type="file" name="images[]" id="fileInput" multiple hidden></div>
<div class="preview" id="preview"></div>

<button type="submit" name="add_product">Add Product</button>
</form>
</div>

<!-- Active Products Table -->
<?php if($latest_products && $latest_products->num_rows > 0): ?>
<div class="table-wrapper">
<h3>Active Products</h3>
<table>
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Price</th>
<th>Category</th>
<th>Status</th>
<th>Stock</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php while($row=$latest_products->fetch_assoc()): ?>
<tr>
<td><?php echo $row['id'];?></td>
<td><?php echo htmlspecialchars($row['name']);?></td>
<td>₱<?php echo number_format($row['price'],2);?></td>
<td><?php echo htmlspecialchars($row['category']);?></td>
<td><?php echo $row['status'];?></td>
<td><?php echo $row['stock'];?></td>
<td>
<a href="edit_product.php?id=<?php echo $row['id'];?>" style="padding:5px 10px;background:#3498db;color:#fff;border-radius:5px;text-decoration:none;">Edit</a>
<a href="delete_product.php?id=<?php echo $row['id'];?>" onclick="return confirm('Delete this product?');" style="padding:5px 10px;background:#e74c3c;color:#fff;border-radius:5px;text-decoration:none;">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</div>

<script>
// Dark Mode
function toggleDark(){document.body.classList.toggle('dark');}

// Dropzone Image Preview
const dropZone=document.getElementById("dropZone");
const fileInput=document.getElementById("fileInput");
const preview=document.getElementById("preview");

dropZone.addEventListener("click",()=>fileInput.click());
fileInput.addEventListener("change", handleFiles);
dropZone.addEventListener("dragover", e=>{e.preventDefault(); dropZone.classList.add("dragover");});
dropZone.addEventListener("dragleave",()=>dropZone.classList.remove("dragover"));
dropZone.addEventListener("drop", e=>{ e.preventDefault(); dropZone.classList.remove("dragover"); fileInput.files=e.dataTransfer.files; handleFiles(); });

function handleFiles(){
    preview.innerHTML="";
    Array.from(fileInput.files).forEach(file=>{
        if(file.type.startsWith("image/")){
            const reader=new FileReader();
            reader.onload=e=>{
                const wrapper=document.createElement("div");
                const img=document.createElement("img");
                img.src=e.target.result;
                wrapper.appendChild(img);
                preview.appendChild(wrapper);
            };
            reader.readAsDataURL(file);
        }
    });
}
</script>
</body>
</html>