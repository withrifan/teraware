<?php
// Hubungkan ke database dan panggil header
require_once 'config/database.php';
include_once 'includes/header.php';

// Fungsi untuk format Rupiah
function format_rupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

// Ambil ID produk dari URL dan validasi
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id == 0) {
    echo "<main class='container my-5'><div class='alert alert-danger'>Produk tidak valid atau tidak ditemukan.</div></main>";
    include_once 'includes/footer.php';
    exit();
}

// Query untuk mengambil data produk spesifik
$query_product = "
    SELECT p.*, c.category_name, pi.image_path
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN (
        SELECT product_id, MIN(image_path) as image_path 
        FROM product_images 
        GROUP BY product_id
    ) as pi ON p.product_id = pi.product_id
    WHERE p.product_id = $1";
$result_product = pg_query_params($dbconn, $query_product, array($product_id));

if (pg_num_rows($result_product) == 0) {
    echo "<main class='container my-5'><div class='alert alert-danger'>Produk tidak ditemukan.</div></main>";
    include_once 'includes/footer.php';
    exit();
}
$product = pg_fetch_assoc($result_product);

// Query BARU untuk menghitung jumlah produk terjual
$sold_query = "SELECT SUM(quantity) as total_sold FROM transaction_items WHERE product_id = $1";
$sold_result = pg_query_params($dbconn, $sold_query, array($product_id));
$sold_data = pg_fetch_assoc($sold_result);
$jumlah_terjual = $sold_data['total_sold'] ? (int)$sold_data['total_sold'] : 0;

?>

<main class="container my-5">
    <div class="row">
        <div class="col-md-6 mb-4">
            <img src="/<?php echo htmlspecialchars($product['image_path'] ?? 'assets/images/placeholder.png'); ?>" class="img-fluid rounded shadow-sm border" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>

        <div class="col-md-6">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="products.php">Produk</a></li>
                    <li class="breadcrumb-item"><a href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
                </ol>
            </nav>

            <h1 class="fw-bold display-6"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div class="d-flex align-items-center mb-3 text-muted">
                <div class="text-warning me-2">
                    <i class="fas fa-star"></i> <?php echo htmlspecialchars($product['rating']); ?>
                </div>
                <span class="mx-2">|</span>
                <span>Terjual: <?php echo $jumlah_terjual; ?></span>
            </div>

            <h2 class="text-primary fw-bolder mb-4"><?php echo format_rupiah($product['price']); ?></h2>

            <!-- MODIFIKASI: Struktur form sesuai contoh -->
            <div id="action-buttons">
                <form id="addToCartForm" class="d-inline">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    
                    <!-- Input quantity dipindahkan ke dalam form addToCartForm -->
                    <div class="row align-items-center mb-4">
                        <div class="col-auto">
                            <label for="quantity" class="form-label">Jumlah:</label>
                            <div class="input-group" style="width: 150px;">
                                <button class="btn btn-outline-secondary" type="button" id="button-minus">-</button>
                                <input type="text" id="quantity" name="quantity" class="form-control text-center" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                <button class="btn btn-outline-secondary" type="button" id="button-plus">+</button>
                            </div>
                        </div>
                        <div class="col-auto align-self-end">
                            <span class="text-muted">Stok: <?php echo $product['stock']; ?></span>
                        </div>
                    </div>
                </form>

                <!-- Form untuk Beli Langsung -->
                <form action="/checkout/buy_now_process.php" method="POST" class="d-inline">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <input type="hidden" name="quantity" id="buyNowQuantity" value="1">
                    
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" form="addToCartForm" class="btn btn-primary btn-lg flex-grow-1"><i class="fas fa-shopping-cart me-2"></i>Tambah ke Keranjang</button>
                        <button type="submit" class="btn btn-success btn-lg flex-grow-1"><i class="fas fa-bolt me-2"></i>Beli Langsung</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#description">Deskripsi</a></li>
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
document.addEventListener('DOMContentLoaded', function() {
    // --- KODE UNTUK TOMBOL KUANTITAS +/- ---
    const btnMinus = document.getElementById('button-minus');
    const btnPlus = document.getElementById('button-plus');
    const quantityInput = document.getElementById('quantity');
    const buyNowQuantityInput = document.getElementById('buyNowQuantity');
    const maxStock = parseInt(quantityInput.getAttribute('max'));

    // Fungsi untuk mensinkronkan kuantitas ke form "Beli Langsung"
    function syncQuantity() {
        buyNowQuantityInput.value = quantityInput.value;
    }

    btnPlus.addEventListener('click', function() {
        let currentValue = parseInt(quantityInput.value);
        if (currentValue < maxStock) {
            quantityInput.value = currentValue + 1;
            syncQuantity(); // Panggil fungsi sinkronisasi
        }
    });

    btnMinus.addEventListener('click', function() {
        let currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
            syncQuantity(); // Panggil fungsi sinkronisasi
        }
    });

    quantityInput.addEventListener('change', syncQuantity);

    // --- Kode AJAX untuk "Tambah ke Keranjang" ---
    const form = document.getElementById('addToCartForm');
    if (form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(form);
            
            fetch('/cart/add_ajax.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(errorData => {
                            throw new Error(errorData.message || 'Terjadi kesalahan pada server.');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        const cartBadge = document.getElementById('cartBadge');
                        if (cartBadge) {
                            cartBadge.textContent = data.cart_count;
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

// Fungsi untuk menampilkan notifikasi
function showNotification(message, type = 'success') {
    const oldNotification = document.querySelector('.toast-notification');
    if (oldNotification) {
        oldNotification.remove();
    }
    const notification = document.createElement('div');
    notification.className = `toast-notification alert alert-${type} position-fixed bottom-0 end-0 m-3`;
    notification.style.zIndex = "1050";
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>