<?php
// Cek dulu apakah session sudah aktif
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Gunakan __DIR__ untuk path yang andal
require_once __DIR__ . '/../config/database.php';

// Atur header untuk respons JSON
header('Content-Type: application/json');

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Silakan login untuk menambahkan produk.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($product_id <= 0 || $quantity <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Input tidak valid.']);
    exit();
}

// Logika untuk menambah atau update item di keranjang
$check_query = "SELECT * FROM cart_items WHERE user_id = $1 AND product_id = $2";
$check_result = pg_query_params($dbconn, $check_query, array($user_id, $product_id));

if (pg_num_rows($check_result) > 0) {
    $update_query = "UPDATE cart_items SET quantity = quantity + $1 WHERE user_id = $2 AND product_id = $3";
    pg_query_params($dbconn, $update_query, array($quantity, $user_id, $product_id));
} else {
    $insert_query = "INSERT INTO cart_items (user_id, product_id, quantity) VALUES ($1, $2, $3)";
    pg_query_params($dbconn, $insert_query, array($user_id, $product_id, $quantity));
}

// Hitung ulang total item di keranjang
$count_query = "SELECT SUM(quantity) as total_items FROM cart_items WHERE user_id = $1";
$count_result = pg_query_params($dbconn, $count_query, array($user_id));
$cart_item_count = 0;
if ($count_result) {
    $count_row = pg_fetch_assoc($count_result);
    $cart_item_count = $count_row['total_items'] ? (int)$count_row['total_items'] : 0;
}

// Kirim respons sukses
echo json_encode(['status' => 'success', 'message' => 'Produk berhasil ditambahkan!', 'cart_count' => $cart_item_count]);
exit();
?>