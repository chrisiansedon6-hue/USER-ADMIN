<?php
require_once '../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get input safely
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        die("Please fill in all fields");
    }

    if ($password !== $confirm_password) {
        die("Passwords do not match");
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare insert statement
    $stmt = $conn->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters and execute
    $stmt->bind_param("sss", $name, $email, $hashedPassword);

    if ($stmt->execute()) {

        // Auto-login after signup
        $_SESSION['admin_id'] = $stmt->insert_id;
        $_SESSION['admin_name'] = $name;

        // Redirect to dashboard
        header("Location: dashboard.php");
        exit;

    } else {
        // Check for duplicate email
        if (strpos($stmt->error, 'Duplicate') !== false) {
            die("Email already exists. Please login instead.");
        }
        die("Execute failed: " . $stmt->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Signup - Artisan Pastries</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="login-card">
    <h2>Admin Signup</h2>
    <?php if($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php elseif($success): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm" placeholder="Confirm Password" required>
        <button type="submit">Sign Up</button>
    </form>
    <p class="footer-link">
        Already have an account? <a href="admin_login.php">Login</a>
    </p>
    <p class="footer-link">
        <a href="../login.php">← Back to User Login</a>
    </p>
</div>
</body>
</html>