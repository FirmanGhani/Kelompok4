<?php
session_start();

// Hapus semua session variables, destroy the session, lalu redirect ke halaman login
// Hapus semua session variables
session_unset();

// Hancurkan sesi
session_destroy();

// Redirect ke halaman login
header("Location: login.php");


exit;
?> 