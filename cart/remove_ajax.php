<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Silakan login.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_item_id = isset($_POST['cart_item_id']) ? (int)$_POST['cart_item_id'] : 0;

if ($cart_item_id > 0) {
    // Hapus item
    $query = "DELETE FROM cart_items WHERE cart_item_id = $1 AND user_id = $2";
    pg_query_params($dbconn, $query, array($cart_item_id, $user_id));
    
    // Hitung ulang total
    $total_query = "SELECT SUM(p.price * ci.quantity) as cart_total FROM cart_items ci JOIN products p ON ci.product_id = p.product_id WHERE ci.user_id = $1";
    $total_result = pg_query_params($dbconn, $total_query, array($user_id));
    $cart_total = pg_fetch_result($total_result, 0, 'cart_total') ?: 0;

    $count_query = "SELECT SUM(quantity) as total_items FROM cart_items WHERE user_id = $1";
    $count_result = pg_query_params($dbconn, $count_query, array($user_id));
    $cart_count = pg_fetch_result($count_result, 0, 'total_items') ?: 0;
    
    echo json_encode([
        'status' => 'success',
        'cart_total' => (float)$cart_total,
        'cart_count' => (int)$cart_count
    ]);
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Input tidak valid.']);
}
exit();
?>