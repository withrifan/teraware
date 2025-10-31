<?php
require_once 'config/database.php';
include_once 'includes/header.php';

function format_rupiah($number)
{
    return 'Rp ' . number_format($number, 0, ',', '.');
}

$product_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($product_id == 0) {
    echo "<main class='container my-5'><div class='alert alert-danger'>Produk tidak valid atau tidak ditemukan.</div></main>";
    include_once 'includes/footer.php';
    exit();
}

$query_product = "
    SELECT p.*, c.category_name
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    WHERE p.product_id = $1";
$result_product = pg_query_params($dbconn, $query_product, array($product_id));

if (pg_num_rows($result_product) == 0) {
    echo "<main class='container my-5'><div class='alert alert-danger'>Produk tidak ditemukan.</div></main>";
    include_once 'includes/footer.php';
    exit();
}
$product = pg_fetch_assoc($result_product);

// AMBIL SEMUA GAMBAR untuk produk ini
$images_query = "SELECT image_id, image_path FROM product_images WHERE product_id = $1 ORDER BY image_id ASC";
$images_result = pg_query_params($dbconn, $images_query, array($product_id));
$product_images = pg_fetch_all($images_result);

// Ambil jumlah terjual
$sold_query = "SELECT SUM(quantity) as total_sold FROM transaction_items WHERE product_id = $1";
$sold_result = pg_query_params($dbconn, $sold_query, array($product_id));
$sold_data = pg_fetch_assoc($sold_result);
$jumlah_terjual = $sold_data['total_sold'] ? (int) $sold_data['total_sold'] : 0;
?>

<main class="container my-5">
    <div class="row">
        <div class="col-md-6 mb-4">
            <?php if ($product_images): ?>
                <div id="productImageCarousel" class="carousel slide shadow-sm border rounded" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <?php foreach ($product_images as $index => $img): ?>
                            <button type="button" data-bs-target="#productImageCarousel"
                                data-bs-slide-to="<?php echo $index; ?>" class="<?php echo ($index == 0) ? 'active' : ''; ?>"
                                aria-current="<?php echo ($index == 0) ? 'true' : 'false'; ?>"
                                aria-label="Slide <?php echo $index + 1; ?>"></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="carousel-inner">
                        <?php foreach ($product_images as $index => $img): ?>
                            <div class="carousel-item <?php echo ($index == 0) ? 'active' : ''; ?>">
                                <img src="/teraware/<?php echo htmlspecialchars($img['image_path']); ?>" class="d-block w-100"
                                    alt="Gambar Produk <?php echo $index + 1; ?>"
                                    style="aspect-ratio: 1 / 1; object-fit: contain;">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#productImageCarousel"
                        data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"
                            style="background-color: rgba(0,0,0,0.3); border-radius: 50%;"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#productImageCarousel"
                        data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"
                            style="background-color: rgba(0,0,0,0.3); border-radius: 50%;"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            <?php else: ?>
                <img src="/teraware/assets/images/placeholder.png" class="img-fluid rounded shadow-sm border"
                    alt="Placeholder">
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="products.php">Produk</a></li>
                    <li class="breadcrumb-item"><a
                            href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?php echo htmlspecialchars($product['name']); ?></li>
                </ol>
            </nav>
            <h1 class="fw-bold display-6"><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="d-flex align-items-center mb-3 text-muted">
                <div class="text-warning me-2"><i class="fas fa-star"></i>
                    <?php echo htmlspecialchars($product['rating']); ?></div>
                <span class="mx-2">|</span>
                <span>Terjual: <?php echo $jumlah_terjual; ?></span>
            </div>
            <h2 class="text-primary fw-bolder mb-4"><?php echo format_rupiah($product['price']); ?></h2>

            <div class="row align-items-center mb-4">
                <div class="col-auto">
                    <label for="quantity" class="form-label">Jumlah:</label>
                    <div class="input-group" style="width: 150px;">
                        <button class="btn btn-outline-secondary" type="button" id="button-minus">-</button>
                        <input type="text" id="quantity" name="quantity_display" class="form-control text-center"
                            value="1" min="1" max="<?php echo $product['stock']; ?>">
                        <button class="btn btn-outline-secondary" type="button" id="button-plus">+</button>
                    </div>
                </div>
                <div class="col-auto align-self-end">
                    <span class="text-muted">Stok: <?php echo $product['stock']; ?></span>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex">
                <form id="addToCartForm" class="flex-grow-1">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <input type="hidden" name="quantity" id="ajaxQuantity" value="1">
                    <button type="submit" class="btn btn-primary btn-lg w-100"><i
                            class="fas fa-shopping-cart me-2"></i>Tambah ke Keranjang</button>
                </form>

                <form action="/teraware/checkout/buy_now_process.php" method="POST" class="flex-grow-1">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <input type="hidden" name="quantity" id="buyNowQuantity" value="1">
                    <button type="submit" class="btn btn-success btn-lg w-100"><i class="fas fa-bolt me-2"></i>Beli
                        Langsung</button>
                </form>
            </div>
        </div>
    </div>
    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab"
                                href="#description">Deskripsi</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#specs">Spesifikasi</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content p-3">
                        <div class="tab-pane fade show active" id="description">
                            <h5 class="card-title">Deskripsi Produk</h5>
                            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        </div>
                        <div class="tab-pane fade" id="specs">
                            <h5 class="card-title">Spesifikasi Teknis</h5>
                            <p><?php echo nl2br(htmlspecialchars($product['specifications'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const quantityInput = document.getElementById('quantity'); // Ini input display
        const ajaxQuantityInput = document.getElementById('ajaxQuantity');
        const buyNowQuantityInput = document.getElementById('buyNowQuantity');
        const maxStock = parseInt(quantityInput.getAttribute('max'));

        const syncQuantity = () => {
            const val = quantityInput.value;
            ajaxQuantityInput.value = val;
            buyNowQuantityInput.value = val;
        };

        document.getElementById('button-plus').addEventListener('click', () => {
            let currentVal = parseInt(quantityInput.value);
            if (currentVal < maxStock) {
                quantityInput.value = currentVal + 1;
                syncQuantity();
            }
        });
        document.getElementById('button-minus').addEventListener('click', () => {
            let currentVal = parseInt(quantityInput.value);
            if (currentVal > 1) {
                quantityInput.value = currentVal - 1;
                syncQuantity();
            }
        });

        quantityInput.addEventListener('change', () => { // Jika user mengetik langsung
            let currentVal = parseInt(quantityInput.value);
            if (isNaN(currentVal) || currentVal < 1) {
                quantityInput.value = 1;
            } else if (currentVal > maxStock) {
                quantityInput.value = maxStock;
            }
            syncQuantity();
        });

        // --- Kode AJAX untuk "Tambah ke Keranjang" ---
        const form = document.getElementById('addToCartForm');
        if (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                const formData = new FormData(form);
                fetch('/teraware/cart/add_ajax.php', { method: 'POST', body: formData })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(errorData => { throw new Error(errorData.message || 'Server error.'); });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            const cartBadge = document.getElementById('cartBadge');
                            if (cartBadge) {
                                cartBadge.textContent = data.cart_count;
                                cartBadge.style.display = data.cart_count > 0 ? 'inline-block' : 'none'; // Tampilkan/sembunyikan badge
                            }
                            showNotification(data.message, 'success');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification(error.message, 'danger');
                    });
            });
        }
    });

    // Fungsi notifikasi
    function showNotification(message, type = 'success') {
        const oldNotification = document.querySelector('.toast-notification');
        if (oldNotification) oldNotification.remove();
        const notification = document.createElement('div');
        notification.className = `toast-notification alert alert-${type} position-fixed bottom-0 end-0 m-3`;
        notification.style.zIndex = "1050";
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => { notification.remove(); }, 3000);
    }
</script>