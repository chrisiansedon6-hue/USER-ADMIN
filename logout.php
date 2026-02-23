<?php
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Clear cookies
setcookie('user_email', '', time() - 3600, "/");
setcookie('user_name', '', time() - 3600, "/");

// Redirect to login page
header('Location: index.php');
exit;