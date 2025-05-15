<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validasi data tidak boleh kosong
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Semua field wajib diisi!";
        header("Location: ../register.php?error=" . urlencode($error));
        exit;
    }

    // Cek apakah password dan konfirmasi sama
    if ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok!";
        header("Location: ../register.php?error=" . urlencode($error));
        exit;
    }

    // Cek apakah username sudah ada
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $error = "Username sudah digunakan, coba yang lain.";
        header("Location: ../register.php?error=" . urlencode($error));
        exit;
    }

    // Hash password (gunakan md5 sesuai struktur tabel kamu)
    $hashedPassword = md5($password);

    // Insert user baru
    $insert = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
    $success = $insert->execute([$username, $email, $hashedPassword]);

    if ($success) {
        header("Location: ../login.php?success=" . urlencode("Registrasi berhasil! Silakan login."));
        exit;
    } else {
        $error = "Gagal melakukan registrasi.";
        header("Location: ../register.php?error=" . urlencode($error));
        exit;
    }
}
?>
