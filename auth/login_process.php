<?php
// Memulai session di baris paling atas
session_start();

// Menghubungkan ke file koneksi database
require_once '../config/database.php';

// Cek apakah request adalah POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = pg_escape_string($dbconn, $_POST['email']);
    $password = $_POST['password'];

    // Validasi dasar
    if (empty($email) || empty($password)) {
        header('Location: ../login.php?error=Email dan password wajib diisi');
        exit();
    }

    // Cari user berdasarkan email
    $query = "SELECT user_id, name, email, password, role FROM users WHERE email = $1";
    $result = pg_query_params($dbconn, $query, array($email));

    if (pg_num_rows($result) === 1) {
        // Jika user ditemukan
        $user = pg_fetch_assoc($result);

        // Verifikasi password yang di-hash
        if (password_verify($password, $user['password'])) {
            // Jika password cocok, simpan data user ke session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Arahkan berdasarkan role
            if ($user['role'] === 'admin') {
                header('Location: ../admin/index.php');
                exit();
            } else {
                header('Location: ../index.php');
                exit();
            }
        } else {
            // Jika password salah
            header('Location: ../login.php?error=Password yang Anda masukkan salah.');
            exit();
        }
    } else {
        // Jika email tidak ditemukan
        header('Location: ../login.php?error=Email tidak ditemukan.');
        exit();
    }
} else {
    // Jika akses bukan POST, redirect ke halaman utama
    header('Location: ../index.php');
    exit();
}
?>