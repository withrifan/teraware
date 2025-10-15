<?php

session_start();

// Hapus semua variabel session
$_SESSION = array();

// Hancurkan session
session_destroy();

// Redirect ke halaman login dengan pesan
header("Location: ../login.php?success=Anda telah berhasil logout.");
exit();
?>