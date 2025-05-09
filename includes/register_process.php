<?php
require_once 'config.php';

$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];
$confirmPassword = $_POST['confirm_password'];

// Basic validation
if ($password !== $confirmPassword) {
    $error = "Password tidak cocok!";
    header("Location: ../register.php?error=" . urlencode($error));
    exit;
}

// Check if email or username already exists
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
$stmt->execute([$email, $username]);
$user = $stmt->fetch();

if ($user) {
    $error = "Email atau Username sudah digunakan.";
    header("Location: ../register.php?error=" . urlencode($error));
    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Insert into DB
$stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->execute([$username, $email, $hashedPassword, 'user']);

// Redirect to login or home
header("Location: ../login.php?success=1");
exit;
?>