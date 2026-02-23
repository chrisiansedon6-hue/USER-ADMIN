<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$response = ['status'=>'error', 'message'=>'Something went wrong'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['name']);
    $details = trim($_POST['details']);
    $price = floatval($_POST['price']);
    $category = $_POST['category']; 
    $stock = intval($_POST['stock']);
    $status = $_POST['status'];

    if(empty($name) || empty($details) || empty($price) || empty($category)){
        $response['message'] = 'Please fill all required fields';
    } else {
        $stmt = $conn->prepare("INSERT INTO products(name, details, price, category, stock, status) VALUES(?,?,?,?,?,?)");
        $stmt->bind_param("ssdiss", $name, $details, $price, $category, $stock, $status);
        $stmt->execute();
        $product_id = $stmt->insert_id;

        // Handle images
        $uploadedImages = [];
        $allowed = ['jpg','jpeg','png','webp'];
        if(!empty($_FILES['images'])){
            foreach($_FILES['images']['name'] as $key => $img){
                $tmp = $_FILES['images']['tmp_name'][$key];
                $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
                if(in_array($ext, $allowed)){
                    $new_name = uniqid().".".$ext;
                    move_uploaded_file($tmp, "uploaded_img/".$new_name);
                    $stmt2 = $conn->prepare("INSERT INTO product_images(product_id,image) VALUES(?,?)");
                    $stmt2->bind_param("is",$product_id,$new_name);
                    $stmt2->execute();
                    $uploadedImages[] = $new_name;
                }
            }
        }

        // Return the new product info
        $response['status'] = 'success';
        $response['message'] = 'Product added successfully!';
        $response['product'] = [
            'id'=>$product_id,
            'name'=>$name,
            'price'=>$price,
            'category'=>$category,
            'status'=>$status,
            'stock'=>$stock,
            'images'=>$uploadedImages
        ];
    }
}

echo json_encode($response);
?>