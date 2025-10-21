<?php 
require_once '../config/database.php';
include_once 'includes/header.php'; 

// Query untuk statistik
$total_products = pg_fetch_result(pg_query($dbconn, "SELECT COUNT(*) FROM products"), 0, 0);
$pending_orders = pg_fetch_result(pg_query($dbconn, "SELECT COUNT(*) FROM transactions WHERE order_status = 'diproses'"), 0, 0);
$total_users = pg_fetch_result(pg_query($dbconn, "SELECT COUNT(*) FROM users WHERE role = 'buyer'"), 0, 0);
$total_revenue = pg_fetch_result(pg_query($dbconn, "SELECT COALESCE(SUM(total_price), 0) FROM transactions WHERE order_status = 'selesai'"), 0, 0);

// Query untuk pesanan terbaru
$recent_orders_query = "SELECT t.*, u.name FROM transactions t JOIN users u ON t.user_id = u.user_id ORDER BY t.order_date DESC LIMIT 5";
$recent_orders_result = pg_query($dbconn, $recent_orders_query);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<div class="row">
    <div class="col-md-3 mb-3"><div class="card text-white bg-primary"><div class="card-body"><h4><?php echo $total_products; ?></h4><p>Total Produk</p></div></div></div>
    <div class="col-md-3 mb-3"><div class="card text-white bg-warning"><div class="card-body"><h4><?php echo $pending_orders; ?></h4><p>Pesanan Diproses</p></div></div></div>
    <div class="col-md-3 mb-3"><div class="card text-white bg-success"><div class="card-body"><h4>Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></h4><p>Total Pendapatan</p></div></div></div>
    <div class="col-md-3 mb-3"><div class="card text-white bg-info"><div class="card-body"><h4><?php echo $total_users; ?></h4><p>Total Pembeli</p></div></div></div>
</div>

<h2 class="mt-4">Pesanan Terbaru</h2>
<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead><tr><th>ID Pesanan</th><th>Nama Pembeli</th><th>Tanggal</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
            <?php while($order = pg_fetch_assoc($recent_orders_result)): ?>
            <tr>
                <td>#<?php echo $order['transaction_id']; ?></td>
                <td><?php echo htmlspecialchars($order['name']); ?></td>
                <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                <td>Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></td>
                <td><span class="badge bg-warning"><?php echo ucfirst($order['order_status']); ?></span></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include_once 'includes/footer.php'; ?>