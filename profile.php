<?php
require_once 'config.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch user data
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email)) {
        $error = "Name and Email cannot be empty.";
    } elseif (!empty($password) && $password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $email, $hashed_password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
            $stmt->bind_param("ssi", $name, $email, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $success = "Profile updated successfully.";
        } else {
            $error = "Failed to update profile. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile - Artisan Pastries</title>
<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body { font-family:'Quicksand', sans-serif; margin:0; padding:0; background:#f9f9f9; color:#333;}
.container { max-width:600px; margin:4rem auto; background:#fff; padding:2rem; border-radius:0.5rem; box-shadow:0 4px 16px rgba(0,0,0,0.1);}
h1 { text-align:center; margin-bottom:1rem; color:#e17055;}
form { display:flex; flex-direction:column; gap:1rem;}
input { padding:0.75rem 1rem; border:1px solid #d1d5db; border-radius:0.5rem; font-size:1rem;}
button { padding:0.75rem 1rem; border:none; border-radius:0.5rem; background:#e17055; color:#fff; font-weight:600; cursor:pointer; transition:all 0.3s;}
button:hover { background:#fdcb6e;}
.alert { padding:0.75rem 1rem; border-radius:0.5rem; text-align:center;}
.alert-error { background:#fee2e2; color:#b91c1c;}
.alert-success { background:#d1fae5; color:#065f46;}
.back-link { text-align:center; margin-top:1rem;}
.back-link a { color:#e17055; font-weight:500; }
</style>
</head>
<body>

<div class="container">
    <h1>My Profile</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label>New Password (leave blank to keep current)</label>
        <input type="password" name="password" placeholder="New Password">

        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" placeholder="Confirm Password">

        <button type="submit">Update Profile</button>
    </form>

    <div class="back-link">
        <a href="index.php">← Back to Home</a>
    </div>
</div>

</body>
</html>