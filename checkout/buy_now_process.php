<?php
session_start();
require_once '../config/database.php';

// Wajib login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?error=Silakan login untuk melanjutkan.');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if ($product_id > 0 && $quantity > 0) {
        // Simpan item "Beli Langsung" di session, terpisah dari keranjang utama
        $_SESSION['buy_now_item'] = [
            'product_id' => $product_id,
            'quantity' => $quantity
        ];
        
        // Arahkan ke halaman checkout dengan penanda khusus
        header('Location: ../checkout/index.php?type=buy_now');
        exit();
    }
}

// Jika gagal, kembali ke halaman utama
header('Location: ../index.php');
exit();
?>