<?php
session_start();

// Mengatur error reporting
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

require_once 'config.php'; // koneksi ke database


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Cek apakah username ada di database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Jika username tidak ditemukan
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Username tidak ditemukan.";
        header("Location: ../login.php");
        exit();
    }

    // Ambil data user
    $user = $result->fetch_assoc();

    // Verifikasi password
    if (!password_verify($password, $user['password'])) {
        $_SESSION['error'] = "Password salah.";
        header("Location: ../login.php");
        exit();
    }

    // Jika login berhasil
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    // Redirect ke dashboard
    header("Location: ../dashboard.php");
    exit();
}

// Jika request bukan POST
$_SESSION['error'] = "Akses tidak sah.";
header("Location: ../login.php");
exit();