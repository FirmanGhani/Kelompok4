<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Format email tidak valid.";
        header("Location: ../register.php");
        exit();
    }

    // Cek password cocok
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Password dan konfirmasi tidak cocok.";
        header("Location: ../register.php");
        exit();
    }

    // Cek apakah username dan email sudah dipakai
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Username atau email sudah digunakan.";
        header("Location: ../register.php");
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $default_role = 'user';

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $default_role);

    if ($stmt->execute()) {
        header("Location: ../login.php?success=1");
        exit();
    } else {
        $_SESSION['error'] = "Gagal mendaftarkan pengguna.";
        header("Location: ../register.php");
        exit();
    }
}

$_SESSION['error'] = "Akses tidak sah.";
header("Location: ../register.php");
exit();