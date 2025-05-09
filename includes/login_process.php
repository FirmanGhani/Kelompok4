<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Mencari pengguna berdasarkan username
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Jika pengguna ditemukan dan password cocok
    if ($user && password_verify($password, $user['password'])) {
        // Set session untuk login
        session_start();
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirect ke dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Username atau password salah.";
        include('login.php');
    }
}
?> 