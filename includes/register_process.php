<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validasi input kosong
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "Semua field harus diisi.";
        header("Location: ../register.php");
        exit();
    }

    // Validasi username (minimal 3 karakter, hanya huruf, angka, dan underscore)
    if (strlen($username) < 3) {
        $_SESSION['error'] = "Username minimal 3 karakter.";
        header("Location: ../register.php");
        exit();
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $_SESSION['error'] = "Username hanya boleh mengandung huruf, angka, dan underscore.";
        header("Location: ../register.php");
        exit();
    }

    // Validasi format email yang lebih ketat
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Format email tidak valid.";
        header("Location: ../register.php");
        exit();
    }

    // Validasi email dengan regex tambahan untuk memastikan format yang benar
    $email_pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
    if (!preg_match($email_pattern, $email)) {
        $_SESSION['error'] = "Format email tidak sesuai standar. Contoh: user@example.com";
        header("Location: ../register.php");
        exit();
    }

    // Validasi domain email (cek apakah ada titik setelah @)
    $email_parts = explode('@', $email);
    if (count($email_parts) != 2 || strpos($email_parts[1], '.') === false) {
        $_SESSION['error'] = "Email harus memiliki domain yang valid (contoh: @gmail.com).";
        header("Location: ../register.php");
        exit();
    }

    // Cek password cocok
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Password dan konfirmasi tidak cocok.";
        header("Location: ../register.php");
        exit();
    }

    // Ambil pengaturan kekuatan password dari database
    $table_check = $conn->query("SHOW TABLES LIKE 'system_settings'");
    $strength_setting = 'medium'; // default

    if ($table_check->num_rows > 0) {
        $stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_name = 'password_strength'");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $strength_setting = $result->fetch_assoc()['setting_value'];
        }
        $stmt->close();
    }

    // Validasi kekuatan password menggunakan fungsi yang sama seperti change_password
    $validation_error = validate_password($password, $strength_setting);
    if ($validation_error) {
        $_SESSION['error'] = $validation_error;
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
        // Log aktivitas registrasi
        $user_id = $conn->insert_id;
        require_once 'log_activity.php';
        log_activity($user_id, $username, 'user_registration', 'New user registered');
        
        header("Location: ../login.php?success=1");
        exit();
    } else {
        $_SESSION['error'] = "Gagal mendaftarkan pengguna.";
        header("Location: ../register.php");
        exit();
    }
}

/**
 * Fungsi untuk validasi kekuatan password (sama seperti di change_password.php)
 */
function validate_password($password, $strength) {
    switch ($strength) {
        case 'high':
            if (strlen($password) < 8) {
                return "Password minimal 8 karakter";
            }
            if (!preg_match('/[A-Z]/', $password)) {
                return "Password harus mengandung minimal 1 huruf besar";
            }
            if (!preg_match('/[a-z]/', $password)) {
                return "Password harus mengandung minimal 1 huruf kecil";  
            }
            if (!preg_match('/[0-9]/', $password)) {
                return "Password harus mengandung minimal 1 angka";
            }
            if (!preg_match('/[\W]/', $password)) {
                return "Password harus mengandung minimal 1 simbol";
            }
            break;
        case 'medium':
            if (strlen($password) < 6) {
                return "Password minimal 6 karakter";
            }
            if (!preg_match('/[A-Za-z]/', $password)) {
                return "Password harus mengandung huruf";
            }
            if (!preg_match('/[0-9]/', $password)) {
                return "Password harus mengandung angka";
            }
            break;
        case 'low':
            if (strlen($password) < 4) {
                return "Password minimal 4 karakter";
            }
            break;
    }
    return false;
}

$_SESSION['error'] = "Akses tidak sah.";
header("Location: ../register.php");
exit();