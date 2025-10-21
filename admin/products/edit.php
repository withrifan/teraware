<?php
require_once '../../config/database.php';
include_once '../includes/header.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id == 0) {
    header('Location: index.php');
    exit();
}

// Ambil data produk yang akan diedit
$product_query = "
    SELECT p.*, pi.image_path 
    FROM products p 
    LEFT JOIN product_images pi ON p.product_id = pi.product_id 
    WHERE p.product_id = $1";
$product_result = pg_query_params($dbconn, $product_query, array($product_id));
$product = pg_fetch_assoc($product_result);

if (!$product) {
    // Jika produk tidak ditemukan, kembali ke halaman utama
    header('Location: index.php?error=Produk tidak ditemukan.');
    exit();
}

// Ambil semua kategori
$categories_query = "SELECT * FROM categories ORDER BY category_name ASC";
$categories_result = pg_query($dbconn, $categories_query);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Produk: <?php echo htmlspecialchars($product['name']); ?></h1>
    <a href="index.php" class="btn btn-secondary">Kembali</a>
</div>

<form action="edit_process.php" method="POST" enctype="multipart/form-data" class="row g-3">
    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
    <input type="hidden" name="old_image_path" value="<?php echo $product['image_path'] ?? ''; ?>">

    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Produk</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="specifications" class="form-label">Spesifikasi</label>
                    <textarea class="form-control" id="specifications" name="specifications" rows="5"><?php echo htmlspecialchars($product['specifications']); ?></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="price" class="form-label">Harga</label>
                    <input type="number" class="form-control" id="price" name="price" value="<?php echo $product['price']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="stock" class="form-label">Stok</label>
                    <input type="number" class="form-control" id="stock" name="stock" value="<?php echo $product['stock']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="category" class="form-label">Kategori</label>
                    <select class="form-select" id="category" name="category_id" required>
                        <?php while($category = pg_fetch_assoc($categories_result)): ?>
                        <option value="<?php echo $category['category_id']; ?>" <?php echo ($product['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Gambar Saat Ini</label>
                    <img src="<?php echo htmlspecialchars($product['image_path'] ?? 'assets/images/placeholder.png'); ?>" width="100" class="img-thumbnail">
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Ganti Gambar (Opsional)</label>
                    <input class="form-control" type="file" id="image" name="image">
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 mt-3">
        <button type="submit" class="btn btn-primary">Update Produk</button>
    </div>
</form>

<?php include_once '../includes/footer.php'; ?>