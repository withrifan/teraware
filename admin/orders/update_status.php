<?php
session_start();
require_once '../../config/database.php';
require_once '../auth_check.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $transaction_id = (int)$_POST['transaction_id'];
    $status = pg_escape_string($dbconn, $_POST['status']);
    
    // Validasi status
    $allowed_statuses = ['diproses', 'dikirim', 'selesai'];
    if (in_array($status, $allowed_statuses)) {
        $update_query = "UPDATE transactions SET order_status = $1 WHERE transaction_id = $2";
        pg_query_params($dbconn, $update_query, array($status, $transaction_id));
    }
}

header('Location: index.php');
exit();
?>