<?php
$host = 'localhost';
$database = 'my_database';
$username = 'root';
$password = ''; // default for Laragon

// Membuat koneksi
$conn = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>