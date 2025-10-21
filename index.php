<?php
// Hubungkan ke database
require_once 'config/database.php';

// Memanggil header
include_once 'includes/header.php';

// Fungsi untuk format Rupiah
function format_rupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

// Mengambil data produk unggulan (misal 6 produk dengan rating tertinggi)
$query_products = "
    SELECT p.*, pi.image_path 
    FROM products p
    LEFT JOIN (
        SELECT product_id, MIN(image_path) AS image_path 
        FROM product_images 
        GROUP BY product_id
    ) AS pi ON p.product_id = pi.product_id
    ORDER BY p.rating DESC 
    LIMIT 6";

$result_products = pg_query($dbconn, $query_products);
?>

<main>
    <section class="hero bg-primary text-white text-center py-5">
        <div class="container">
            <h1 class="display-4 fw-bold">Toko Teknologi Terpercaya #1 di Indonesia</h1>
            <p class="lead col-lg-8 mx-auto">
                Dapatkan laptop, PC, dan hardware komputer terbaik dengan harga
                terjangkau dan garansi resmi.
            </p>
            <a href="products.php" class="btn btn-warning btn-lg fw-bold">
                Mulai Belanja Sekarang
            </a>
        </div>
    </section>

    <section class="products py-5 bg-light">
        <div class="container">
            <h2 class="section-title text-center h1 fw-bold mb-5">Produk Unggulan</h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php
                if (pg_num_rows($result_products) > 0) {
                    while ($product = pg_fetch_assoc($result_products)) {
                ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm border-0 product-card">
                                <img src="/<?php echo htmlspecialchars($product['image_path'] ?? 'assets/images/placeholder.png'); ?>" class="card-img-top p-3" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height: 250px; object-fit: contain;">
                                
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <p class="card-text text-muted small flex-grow-1"><?php echo htmlspecialchars($product['specifications']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <p class="card-text fw-bold text-primary fs-5 mb-0"><?php echo format_rupiah($product['price']); ?></p>
                                        <div class="text-warning">
                                            <i class="fas fa-star"></i> <?php echo htmlspecialchars($product['rating']); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-0 p-3">
                                    <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-primary w-100">Lihat Detail</a>
                                </div>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo "<p class='text-center'>Belum ada produk unggulan.</p>";
                }
                ?>
            </div>
        </div>
    </section>
</main>
<?php
// Memanggil footer
include_once 'includes/footer.php';
?>