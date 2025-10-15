<?php
session_start();
require_once '../config/database.php';

// Wajib login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?error=Silakan login untuk melanjutkan.');
    exit();
}

$user_id = $_SESSION['user_id'];
// Ambil data dari $_GET karena kita menggunakan link, bukan form POST
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;

if ($product_id <= 0 || $quantity <= 0) {
    header('Location: ../index.php?error=Input tidak valid.');
    exit();
}

// Logika tambah/update keranjang
$check_query = "SELECT * FROM cart_items WHERE user_id = $1 AND product_id = $2";
$check_result = pg_query_params($dbconn, $check_query, array($user_id, $product_id));

if (pg_num_rows($check_result) > 0) {
    $update_query = "UPDATE cart_items SET quantity = quantity + $1 WHERE user_id = $2 AND product_id = $3";
    pg_query_params($dbconn, $update_query, array($quantity, $user_id, $product_id));
} else {
    $insert_query = "INSERT INTO cart_items (user_id, product_id, quantity) VALUES ($1, $2, $3)";
    pg_query_params($dbconn, $insert_query, array($user_id, $product_id, $quantity));
}

// Logika pengalihan (redirect) BARU
if (isset($_GET['redirect']) && $_GET['redirect'] == 'checkout') {
    header('Location: ../checkout/index.php'); // Arahkan ke checkout
} else {
    header('Location: index.php'); // Arahkan ke halaman keranjang (default)
}
exit();
?>