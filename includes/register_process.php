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
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = "Username sudah digunakan, coba yang lain.";
        header("Location: ../register.php?error=" . urlencode($error));
        exit;
    }

    // Hash password (gunakan md5 sesuai struktur tabel kamu, meskipun sebaiknya pakai password_hash)
    $hashedPassword = md5($password);

    // Insert user baru
    $insert = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
    $insert->bind_param("sss", $username, $email, $hashedPassword);

    if ($insert->execute()) {
        // Register berhasil
        header("Location: ../login.php?success=" . urlencode("Registrasi berhasil! Silakan login."));
        exit;
    } else {
        // Gagal insert
        $error = "Gagal melakukan registrasi.";
        header("Location: ../register.php?error=" . urlencode($error));
        exit;
    }
}
?>