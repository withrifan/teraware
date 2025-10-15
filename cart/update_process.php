<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $cart_item_id = isset($_POST['cart_item_id']) ? (int)$_POST['cart_item_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

    if ($cart_item_id > 0 && $quantity > 0) {
        // Update kuantitas item, pastikan item tersebut milik user yang sedang login
        $query = "UPDATE cart_items SET quantity = $1 WHERE cart_item_id = $2 AND user_id = $3";
        pg_query_params($dbconn, $query, array($quantity, $cart_item_id, $user_id));
    }
}

header('Location: index.php');
exit();
?>