<?php
require_once 'config/database.php';
include_once 'includes/header.php';

function format_rupiah($number)
{
    return 'Rp ' . number_format($number, 0, ',', '.');
}

// Query untuk produk terlaris
$featured_products_query = "
    SELECT
        p.product_id, p.name, p.price, p.rating,
        SUM(ti.quantity) AS total_sold,
        (SELECT image_path FROM product_images WHERE product_id = p.product_id LIMIT 1) AS image_path
    FROM products p
    JOIN transaction_items ti ON p.product_id = ti.product_id
    GROUP BY p.product_id, p.name, p.price, p.rating
    ORDER BY total_sold DESC
    LIMIT 8;
";
$featured_products_result = pg_query($dbconn, $featured_products_query);
?>
<style>
    .product-card-clickable {
        transition: transform .2s ease-out, box-shadow .2s ease-out;
        cursor: pointer;
    }

    .product-card-clickable:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
    }
</style>

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

<main class="container my-5">
    <section id="featured-products">
        <div class="text-center mb-4">
            <h2 class="h3 fw-bold">Produk Terlaris 🔥</h2>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            <?php
            if ($featured_products_result && pg_num_rows($featured_products_result) > 0) {
                while ($product = pg_fetch_assoc($featured_products_result)) {
                    $detail_url = "product_detail.php?id=" . $product['product_id'];
                    ?>
                    <div class="col">
                        <div class="card h-100 shadow border-0 product-card-clickable" data-url="<?php echo $detail_url; ?>">
                            <img src="<?php echo htmlspecialchars($product['image_path'] ?? 'assets/images/placeholder.png'); ?>"
                                class="card-img-top p-3" alt="<?php echo htmlspecialchars($product['name']); ?>"
                                style="height: 220px; object-fit: contain;">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title" style="font-size: 1rem;">
                                    <?php echo htmlspecialchars($product['name']); ?></h5>
                                <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                                    <p class="card-text fw-bold text-primary fs-6 mb-0">
                                        <?php echo format_rupiah($product['price']); ?></p>
                                    <div class="text-warning small">
                                        <i class="fas fa-star"></i> <?php echo htmlspecialchars($product['rating']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 p-3">
                                <a href="<?php echo $detail_url; ?>" class="btn btn-primary w-100 btn-sm stretched-link">Lihat
                                    Detail</a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<div class='col-12'><p class='text-center'>Belum ada produk yang terjual.</p></div>";
            }
            ?>
        </div>
        <div class="text-center mt-4">
            <a href="products.php" class="btn btn-outline-primary">Lihat Semua Produk</a>
        </div>

    </section>
</main>

<?php include_once 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const clickableCards = document.querySelectorAll('.product-card-clickable');
        clickableCards.forEach(card => {
            card.addEventListener('click', function (event) {
                if (!event.target.closest('.stretched-link')) {
                    window.location.href = card.dataset.url;
                }
            });
        });
    });
</script>