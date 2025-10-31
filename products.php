<?php
require_once 'config/database.php';
include_once 'includes/header.php';

function format_rupiah($number)
{
    return 'Rp ' . number_format($number, 0, ',', '.');
}

// Menyiapkan query dasar
$query_products = "
    SELECT p.*, pi.image_path 
    FROM products p
    LEFT JOIN (
        SELECT product_id, MIN(image_path) AS image_path 
        FROM product_images 
        GROUP BY product_id
    ) AS pi ON p.product_id = pi.product_id
";

// Array untuk menampung kondisi WHERE dan parameter query
$where_clauses = [];
$params = [];
$param_counter = 1;

// Filter berdasarkan KATEGORI
if (!empty($_GET['category'])) {
    $where_clauses[] = "p.category_id = $" . $param_counter++;
    $params[] = (int) $_GET['category'];
}

// Filter berdasarkan PENCARIAN
if (!empty($_GET['search'])) {
    $where_clauses[] = "(p.name ILIKE $" . $param_counter . " OR p.description ILIKE $" . $param_counter . ")";
    $params[] = '%' . $_GET['search'] . '%';
}

// Gabungkan semua kondisi WHERE jika ada
if (!empty($where_clauses)) {
    $query_products .= " WHERE " . implode(' AND ', $where_clauses);
}

$query_products .= " ORDER BY p.product_id DESC";
$result_products = pg_query_params($dbconn, $query_products, $params);
?>

<style>
    .product-card-clickable {
        transition: transform .2s ease-out, box-shadow .2s ease-out;
        cursor: pointer;
        /* Add pointer cursor */
    }

    .product-card-clickable:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
    }
</style>

<main class="container my-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Katalog Produk</h1>
            <hr>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        <?php
        if ($result_products && pg_num_rows($result_products) > 0) {
            while ($product = pg_fetch_assoc($result_products)) {
                // Simpan URL detail produk ke variabel
                $detail_url = "product_detail.php?id=" . $product['product_id'];
                ?>
                <div class="col">
                    <div class="card h-100 shadow border-0 product-card-clickable" data-url="<?php echo $detail_url; ?>">
                        <img src="<?php echo htmlspecialchars($product['image_path'] ?? 'assets/images/placeholder.png'); ?>"
                            class="card-img-top p-3" alt="<?php echo htmlspecialchars($product['name']); ?>"
                            style="height: 220px; object-fit: contain;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title" style="font-size: 1rem;"><?php echo htmlspecialchars($product['name']); ?>
                            </h5>
                            <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                                <p class="card-text fw-bold text-primary fs-6 mb-0">
                                    <?php echo format_rupiah($product['price']); ?></p>
                                <div class="text-warning small"><i class="fas fa-star"></i>
                                    <?php echo htmlspecialchars($product['rating']); ?></div>
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
            echo "<div class='col-12'><p class='text-center'>Tidak ada produk yang ditemukan.</p></div>";
        }
        ?>
    </div>
</main>

<?php include_once 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const clickableCards = document.querySelectorAll('.product-card-clickable');
        clickableCards.forEach(card => {
            card.addEventListener('click', function (event) {
                // Cek apakah yang diklik BUKAN tombol "Lihat Detail" atau link di dalamnya
                if (!event.target.closest('.stretched-link')) {
                    // Jika bukan tombol, arahkan ke URL dari atribut data-url
                    window.location.href = card.dataset.url;
                }
                // Jika yang diklik adalah tombol, biarkan link tombol bekerja
            });
        });
    });
</script>