<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

if (isset($_GET['id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_item_id = (int)$_GET['id'];

    if ($cart_item_id > 0) {
        // Hapus item dari keranjang, pastikan item tersebut milik user yang sedang login
        $query = "DELETE FROM cart_items WHERE cart_item_id = $1 AND user_id = $2";
        pg_query_params($dbconn, $query, array($cart_item_id, $user_id));
    }
}

header('Location: index.php');
exit();
?>