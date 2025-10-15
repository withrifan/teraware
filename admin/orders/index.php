<?php
require_once '../../config/database.php';
include_once '../includes/header.php';

$orders_query = "SELECT t.*, u.name FROM transactions t JOIN users u ON t.user_id = u.user_id ORDER BY t.order_date DESC";
$orders_result = pg_query($dbconn, $orders_query);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manajemen Pesanan</h1>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead><tr><th>ID</th><th>Pembeli</th><th>Tanggal</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
            <?php while($order = pg_fetch_assoc($orders_result)): ?>
            <tr>
                <td>#<?php echo $order['transaction_id']; ?></td>
                <td><?php echo htmlspecialchars($order['name']); ?></td>
                <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                <td>Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></td>
                <td>
                    <form action="update_status.php" method="POST" class="d-inline">
                        <input type="hidden" name="transaction_id" value="<?php echo $order['transaction_id']; ?>">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="diproses" <?php if($order['order_status'] == 'diproses') echo 'selected'; ?>>Diproses</option>
                            <option value="dikirim" <?php if($order['order_status'] == 'dikirim') echo 'selected'; ?>>Dikirim</option>
                            <option value="selesai" <?php if($order['order_status'] == 'selesai') echo 'selected'; ?>>Selesai</option>
                        </select>
                    </form>
                </td>
                <td><a href="#" class="btn btn-sm btn-info">Detail</a></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include_once '../includes/footer.php'; ?>