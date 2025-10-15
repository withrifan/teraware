<?php
// Memulai session
session_start();

// Menghubungkan ke file koneksi database
require_once '../config/database.php';

// Cek apakah request adalah POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $name = pg_escape_string($dbconn, $_POST['name']);
    $email = pg_escape_string($dbconn, $_POST['email']);
    $password = $_POST['password'];
    $phone = pg_escape_string($dbconn, $_POST['phone']);
    $address = pg_escape_string($dbconn, $_POST['address']);
    
    // Validasi dasar
    if (empty($name) || empty($email) || empty($password)) {
        header('Location: ../register.php?error=Nama, email, dan password wajib diisi');
        exit();
    }
    
    // Cek apakah email sudah terdaftar
    $query_check_email = "SELECT email FROM users WHERE email = $1";
    $result_check = pg_query_params($dbconn, $query_check_email, array($email));
    
    if (pg_num_rows($result_check) > 0) {
        // Jika email sudah ada, kembalikan ke halaman register dengan pesan error
        header('Location: ../register.php?error=Email sudah terdaftar. Silakan gunakan email lain.');
        exit();
    }
    
    // Hash password sebelum disimpan ke database untuk keamanan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Role default untuk registrasi adalah 'buyer'
    $role = 'buyer';
    
    // Query untuk memasukkan data user baru
    $query_insert = "INSERT INTO users (name, email, password, phone_number, address, role) VALUES ($1, $2, $3, $4, $5, $6)";
    
    $result_insert = pg_query_params($dbconn, $query_insert, array($name, $email, $hashed_password, $phone, $address, $role));
    
    if ($result_insert) {
        // Jika registrasi berhasil, redirect ke halaman login dengan pesan sukses
        header('Location: ../login.php?success=Registrasi berhasil! Silakan masuk.');
        exit();
    } else {
        // Jika gagal, kembali ke halaman register dengan pesan error
        header('Location: ../register.php?error=Terjadi kesalahan saat registrasi.');
        exit();
    }
} else {
    // Jika akses bukan POST, redirect ke halaman utama
    header('Location: ../index.php');
    exit();
}
?>