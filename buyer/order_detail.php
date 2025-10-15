<?php
require_once '../config/database.php';
include_once '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$transaction_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data transaksi utama, pastikan transaksi ini milik user yang login!
$trans_query = "SELECT * FROM transactions WHERE transaction_id = $1 AND user_id = $2";
$trans_result = pg_query_params($dbconn, $trans_query, array($transaction_id, $user_id));

if (pg_num_rows($trans_result) == 0) {
    echo "<div class='container my-5'>Pesanan tidak ditemukan atau Anda tidak memiliki akses.</div>";
    include_once '../includes/footer.php';
    exit();
}
$transaction = pg_fetch_assoc($trans_result);

// Ambil item-item yang ada di transaksi ini
$items_query = "
    SELECT ti.quantity, ti.price_per_item, p.name 
    FROM transaction_items ti 
    JOIN products p ON ti.product_id = p.product_id 
    WHERE ti.transaction_id = $1
";
$items_result = pg_query_params($dbconn, $items_query, array($transaction_id));

function format_rupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}
?>

<main class="container my-5">
    <a href="orders.php" class="btn btn-outline-secondary mb-3"><i class="fas fa-arrow-left"></i> Kembali ke Riwayat</a>
    <div class="card">
        <div class="card-header">
            <h3>Detail Pesanan #<?php echo str_pad($transaction['transaction_id'], 6, '0', STR_PAD_LEFT); ?></h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Tanggal Pesanan:</strong> <?php echo date('d F Y, H:i', strtotime($transaction['order_date'])); ?></p>
                    <p><strong>Status:</strong> <span class="badge bg-primary"><?php echo ucfirst($transaction['order_status']); ?></span></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p><strong>Total Pembayaran:</strong></p>
                    <h4 class="text-primary"><?php echo format_rupiah($transaction['total_price']); ?></h4>
                </div>
            </div>
            <hr>
            <h5 class="mt-4">Produk yang Dipesan:</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($item = pg_fetch_assoc($items_result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo format_rupiah($item['price_per_item']); ?></td>
                        <td><?php echo format_rupiah($item['price_per_item'] * $item['quantity']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include_once '../includes/footer.php'; ?>