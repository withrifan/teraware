<?php
require_once '../../config/database.php';
include_once '../includes/header.php';

// Menyiapkan query dasar
$base_query = "
    SELECT p.product_id, p.name, p.price, p.stock, c.category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.category_id
";
$params = [];
$where_clauses = [];

// Logika untuk PENCARIAN
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search_term)) {
    // Cek apakah input adalah angka (untuk ID) atau teks (untuk nama)
    if (is_numeric($search_term)) {
        $where_clauses[] = "p.product_id = $1";
        $params[] = $search_term;
    } else {
        $where_clauses[] = "p.name ILIKE $1";
        $params[] = '%' . $search_term . '%';
    }
}

// Gabungkan kondisi WHERE jika ada
if (!empty($where_clauses)) {
    $base_query .= " WHERE " . implode(' AND ', $where_clauses);
}

$base_query .= " ORDER BY p.product_id DESC";
$products_result = pg_query_params($dbconn, $base_query, $params);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manajemen Produk</h1>
</div>

<?php
// Menampilkan pesan sukses dari proses lain (tambah, edit, hapus)
if (isset($_GET['success'])) {
    echo '<div id="auto-dismiss-alert" class="alert alert-success">' . htmlspecialchars($_GET['success']) . '</div>';
}
if (isset($_GET['error'])) {
    echo '<div id="auto-dismiss-alert" class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
}
?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Produk</h5>
        <div class="d-flex align-items-center">
            <form action="index.php" method="GET" class="me-2">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari ID atau Nama..." value="<?php echo htmlspecialchars($search_term); ?>">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
            <a href="add.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Tambah Produk
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products_result && pg_num_rows($products_result) > 0): ?>
                        <?php while($product = pg_fetch_assoc($products_result)): ?>
                        <tr>
                            <td><?php echo $product['product_id']; ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                            <td><?php echo $product['stock']; ?></td>
                            <td class="text-center">
                                <a href="edit.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete_process.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada produk yang ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>