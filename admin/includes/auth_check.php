<?php
// Cek dulu apakah session sudah aktif sebelum memulainya
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Panggil file koneksi dengan NAMA dan PATH yang benar
require_once __DIR__ . 'admin\includes\auth_check.php';

// Cek apakah user sudah login DAN rolenya adalah 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Jika tidak, redirect ke halaman login dengan pesan error
    header('Location: /login.php?error=Akses ditolak. Anda bukan admin.');
    exit();
}
?>