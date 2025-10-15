<?php
require_once '../../config/database.php';
include_once '../includes/header.php';

$categories_query = "SELECT * FROM categories ORDER BY category_name ASC";
$categories_result = pg_query($dbconn, $categories_query);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Tambah Produk Baru</h1>
    <a href="index.php" class="btn btn-secondary">Kembali</a>
</div>

<form action="add_process.php" method="POST" enctype="multipart/form-data" class="row g-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Produk</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                </div>
                <div class="mb-3">
                    <label for="specifications" class="form-label">Spesifikasi</label>
                    <textarea class="form-control" id="specifications" name="specifications" rows="5"></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="price" class="form-label">Harga</label>
                    <input type="number" class="form-control" id="price" name="price" required step="1000">
                </div>
                <div class="mb-3">
                    <label for="stock" class="form-label">Stok</label>
                    <input type="number" class="form-control" id="stock" name="stock" required>
                </div>
                <div class="mb-3">
                    <label for="category" class="form-label">Kategori</label>
                    <select class="form-select" id="category" name="category_id" required>
                        <option value="">Pilih Kategori...</option>
                        <?php while($category = pg_fetch_assoc($categories_result)): ?>
                        <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Gambar Produk</label>
                    <input class="form-control" type="file" id="image" name="image" required>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 mt-3">
        <button type="submit" class="btn btn-primary">Simpan Produk</button>
    </div>
</form>

<?php include_once '../includes/footer.php'; ?>