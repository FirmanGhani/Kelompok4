<?php
session_start();
require_once 'config.php';

$username = $_POST['username'];
$password = $_POST['password'];

// Fetch user by username
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // Login successful, set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    header("Location: ../dashboard.php"); // Redirect to protected area
    exit;
} else {
    $error = "Username atau password salah.";
    header("Location: ../login.php?error=" . urlencode($error));
    exit;
}
?>