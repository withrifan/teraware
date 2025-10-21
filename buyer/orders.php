<?php
require_once '../config/database.php';
include_once '../includes/header.php';

// Wajib login
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil semua transaksi user, urutkan dari yang terbaru
$orders_query = "SELECT * FROM transactions WHERE user_id = $1 ORDER BY order_date DESC";
$orders_result = pg_query_params($dbconn, $orders_query, array($user_id));

function format_rupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

function get_status_badge($status) {
    switch ($status) {
        case 'diproses': return 'bg-primary';
        case 'dikirim': return 'bg-warning text-dark';
        case 'selesai': return 'bg-success';
        default: return 'bg-secondary';
    }
}
?>

<main class="container my-5">
    <h1 class="mb-4">Riwayat Pesanan Saya</h1>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>

    <?php if (pg_num_rows($orders_result) > 0): ?>
        <div class="list-group">
            <?php while ($order = pg_fetch_assoc($orders_result)): ?>
                <a href="order_detail.php?id=<?php echo $order['transaction_id']; ?>" class="list-group-item list-group-item-action flex-column align-items-start mb-3 shadow-sm">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">Pesanan #<?php echo str_pad($order['transaction_id'], 6, '0', STR_PAD_LEFT); ?></h5>
                        <small><?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></small>
                    </div>
                    <p class="mb-1">Total: <strong><?php echo format_rupiah($order['total_price']); ?></strong></p>
                    <small>Status: <span class="badge <?php echo get_status_badge($order['order_status']); ?>"><?php echo ucfirst($order['order_status']); ?></span></small>
                </a>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-history fa-4x text-muted mb-3"></i>
            <h3>Anda Belum Memiliki Pesanan</h3>
            <p class="text-muted">Semua pesanan Anda akan ditampilkan di sini.</p>
            <a href="/index.php" class="btn btn-primary mt-2">Mulai Belanja</a>
        </div>
    <?php endif; ?>
</main>

<?php include_once '../includes/footer.php'; ?>