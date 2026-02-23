<?php
require_once '../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Check empty fields
    if (empty($email) || empty($password)) {
        header("Location: ../login.php?admin_error=Please+fill+all+fields");
        exit;
    }

    // Prepare statement
    $stmt = $conn->prepare("SELECT id, name, email, password FROM admins WHERE email = ?");
    if (!$stmt) {
        // DB error
        header("Location: ../login.php?admin_error=Database+error");
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    // Verify login
    if ($admin && password_verify($password, $admin['password'])) {
        // Login success
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];

        // Redirect to dashboard
        header("Location: dashboard.php");
        exit;

    } else {
        // Invalid credentials
        header("Location: ../login.php?admin_error=Invalid+email+or+password");
        exit;
    }
} else {
    // Prevent direct access
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - Artisan Pastries</title>
<link rel="stylesheet" href="css/admin.css">
<style>
body {
    font-family: 'Quicksand', sans-serif;
    background:#f5f5f5;
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
}
.login-card {
    background:#fff;
    padding:2rem;
    border-radius:10px;
    box-shadow:0 8px 20px rgba(0,0,0,0.1);
    width:100%;
    max-width:400px;
}
.login-card h2 {
    text-align:center;
    color:#e17055;
    margin-bottom:1.5rem;
}
.login-card input {
    width:100%;
    padding:0.75rem;
    margin-bottom:1rem;
    border-radius:5px;
    border:1px solid #ddd;
    font-size:1rem;
}
.login-card button {
    width:100%;
    padding:0.75rem;
    background:#e17055;
    color:#fff;
    border:none;
    border-radius:5px;
    font-weight:bold;
    cursor:pointer;
    transition:all 0.3s;
}
.login-card button:hover {
    background:#fdcb6e;
    color:#333;
}
.error {
    color:#d63031;
    text-align:center;
    margin-bottom:1rem;
}
</style>
</head>
<body>
<div class="login-card">
    <h2>Admin Login</h2>
    <?php if($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>