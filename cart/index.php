<?php
require_once '../config/database.php';
include_once '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php?error=Anda harus login untuk melihat keranjang.');
    exit();
}

$user_id = $_SESSION['user_id'];
$query_cart = "
    SELECT 
        ci.cart_item_id, p.product_id, p.name, p.price, p.stock, ci.quantity,
        (SELECT image_path FROM product_images WHERE product_id = p.product_id LIMIT 1) AS image_path
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.product_id
    WHERE ci.user_id = $1 ORDER BY ci.cart_item_id ASC";
$result_cart = pg_query_params($dbconn, $query_cart, array($user_id));

function format_rupiah($number) {
    return 'Rp' . number_format(abs($number), 0, ',', '.');
}

// Placeholder untuk diskon, Anda bisa membuatnya dinamis
$discount_amount = 500000; 
?>

<style>
/* CSS UNTUK TAMPILAN SESUAI DESAIN */
.cart-container {
    max-width: 1200px;
}

.cart-item-card, .order-summary-card {
    background-color: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0,0,0,.05);
}

/* === BAGIAN ITEM KERANJANG (KIRI) === */
.cart-item-card {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1.5rem;
}

.cart-item-card img {
    width: 120px;
    height: 120px;
    object-fit: contain;
    margin-right: 1.5rem;
    border-radius: 4px;
}

.cart-item-details {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 120px; /* Samakan dengan tinggi gambar */
}

.cart-item-details h5 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
    line-height: 1.4;
}

.cart-item-details .price-per-item {
    font-size: 1rem;
    color: #212529;
    font-weight: 500;
    margin-bottom: 1rem;
}

/* Kontainer untuk quantity dan subtotal */
.cart-item-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto; /* Mendorong ke bawah */
}

.cart-item-subtotal {
    font-weight: bold;
    font-size: 1.2rem;
    color: #0d6efd;
}

.quantity-control {
    display: flex;
    align-items: center;
    border: 1px solid #ced4da;
    border-radius: 50px; /* Membuat lebih rounded */
    padding: 0.25rem;
    width: fit-content;
}

.quantity-control button {
    border: none;
    background-color: transparent;
    font-weight: 500;
    font-size: 1.2rem;
    padding: 0 0.8rem;
    cursor: pointer;
    color: #6c757d;
}
.quantity-control button:hover {
    color: #0d6efd;
}

.quantity-control input {
    width: 40px;
    text-align: center;
    border: none;
    background: transparent;
    font-weight: 600;
    font-size: 1.1rem;
}
.quantity-control input:focus { outline: none; }
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}

/* === BAGIAN RINGKASAN PESANAN (KANAN) === */
.order-summary-card h5 { 
    font-weight: 600; 
    margin-bottom: 1.5rem; 
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #eee;
}
.summary-row { 
    display: flex; 
    justify-content: space-between; 
    margin-bottom: 1rem; 
    color: #495057;
    font-size: 1rem;
}
.summary-row.total { 
    font-weight: bold; 
    font-size: 1.15rem; 
    color: #212529; 
    margin-top: 1rem;
}
.summary-row span:last-child {
    font-weight: 500;
}
.summary-row.total span:last-child {
    font-weight: bold;
    color: #0d6efd;
}
</style>

<main class="container my-5 cart-container">
    <h1 class="mb-4">Keranjang Belanja</h1>

    <div class="row" id="cart-container">
        <?php if ($result_cart && pg_num_rows($result_cart) > 0): ?>
            <div class="col-lg-8" id="cart-items-list">
                <?php
                $sub_total = 0;
                while ($item = pg_fetch_assoc($result_cart)):
                    $item_total = $item['price'] * $item['quantity'];
                    $sub_total += $item_total;
                ?>
                <div class="cart-item-card" id="cart-item-<?php echo $item['cart_item_id']; ?>">
                    <img src="/<?php echo htmlspecialchars($item['image_path'] ?? 'assets/images/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    
                    <div class="cart-item-details">
                        <div>
                            <a href="/product_detail.php?id=<?php echo $item['product_id']; ?>" class="text-dark text-decoration-none">
                                <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                            </a>
                            <p class="price-per-item"><?php echo format_rupiah($item['price']); ?></p>
                        </div>

                        <div class="cart-item-actions">
                            <div class="quantity-control">
                                <button class="quantity-btn" data-action="minus" data-id="<?php echo $item['cart_item_id']; ?>">-</button>
                                <input type="text" readonly class="quantity-input" value="<?php echo $item['quantity']; ?>" data-id="<?php echo $item['cart_item_id']; ?>" max="<?php echo $item['stock']; ?>">
                                <button class="quantity-btn" data-action="plus" data-id="<?php echo $item['cart_item_id']; ?>">+</button>
                            </div>
                            
                            <div class="cart-item-subtotal" id="subtotal-<?php echo $item['cart_item_id']; ?>">
                                <?php echo format_rupiah($item_total); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <div class="col-lg-4">
                <div class="order-summary-card">
                    <h5>Ringkasan pesanan</h5>
                    <div class="summary-row">
                        <span>Subtotal Pesanan</span>
                        <span id="subtotal-summary"><?php echo format_rupiah($sub_total); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Pengiriman</span>
                        <span>Gratis</span>
                    </div>
                    <div class="summary-row">
                        <span>Diskon</span>
                        <span id="discount-summary">- <?php echo format_rupiah($discount_amount); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total Pembayaran</span>
                        <span id="grand-total"><?php echo format_rupiah($sub_total - $discount_amount); ?></span>
                    </div>
                    <div class="d-grid mt-3">
                        <a href="/checkout/index.php" class="btn btn-primary btn-lg">Lanjut ke Checkout</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div id="empty-cart-message" class="col-12 text-center py-5">
                <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                <h3>Keranjang Anda Kosong</h3>
                <a href="/index.php" class="btn btn-primary mt-2">Mulai Belanja</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cartItemsList = document.getElementById('cart-items-list');
    const staticDiscount = <?php echo $discount_amount; ?>; 

    const formatRupiah = (number) => {
        // Hapus simbol minus jika ada sebelum format
        const absNumber = Math.abs(number);
        const formatted = new Intl.NumberFormat('id-ID', { 
            style: 'currency', 
            currency: 'IDR', 
            minimumFractionDigits: 0 
        }).format(absNumber);
        
        // Tambahkan tanda minus kembali jika angka aslinya negatif
        return number < 0 ? `- ${formatted}` : formatted;
    }

    function updateSummary(cartTotal) {
        if (document.getElementById('subtotal-summary')) {
            const grandTotal = cartTotal > staticDiscount ? cartTotal - staticDiscount : 0;
            document.getElementById('subtotal-summary').textContent = formatRupiah(cartTotal);
            document.getElementById('grand-total').textContent = formatRupiah(grandTotal);
        }
    }
    
    function updateQuantity(cartItemId, newQuantity) {
        const formData = new FormData();
        formData.append('cart_item_id', cartItemId);
        formData.append('quantity', newQuantity);

        fetch('update_quantity_ajax.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById(`subtotal-${cartItemId}`).textContent = formatRupiah(data.item_subtotal);
                document.getElementById('cartBadge').textContent = data.cart_count;
                updateSummary(data.cart_total);
            } else {
                // Mungkin bisa ditambahkan notifikasi error
                console.error(data.message);
            }
        }).catch(console.error);
    }

    function removeItem(cartItemId) {
        if (!confirm('Apakah Anda yakin ingin menghapus produk ini dari keranjang?')) return;
        
        const itemCard = document.getElementById(`cart-item-${cartItemId}`);
        if (!itemCard) return;

        itemCard.style.opacity = '0.5'; // Beri efek visual saat proses
        
        const formData = new FormData();
        formData.append('cart_item_id', cartItemId);

        fetch('remove_ajax.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                itemCard.style.transition = 'all 0.3s ease-out';
                itemCard.style.opacity = '0';
                itemCard.style.transform = 'translateX(-50px)';
                setTimeout(() => {
                    itemCard.remove();
                    document.getElementById('cartBadge').textContent = data.cart_count;
                    updateSummary(data.cart_total);

                    if (data.cart_count === 0) {
                        document.getElementById('cart-container').innerHTML = `<div id="empty-cart-message" class="col-12 text-center py-5"><i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i><h3>Keranjang Anda Kosong</h3><a href="/index.php" class="btn btn-primary mt-2">Mulai Belanja</a></div>`;
                    }
                }, 300);
            }
        }).catch(() => { itemCard.style.opacity = '1'; });
    }

    if(cartItemsList){
        cartItemsList.addEventListener('click', function(event) {
            const target = event.target.closest('.quantity-btn'); // Lebih robust
            if (target) {
                const action = target.dataset.action;
                const id = target.dataset.id;
                const input = document.querySelector(`.quantity-input[data-id='${id}']`);
                let currentValue = parseInt(input.value);
                const max = parseInt(input.getAttribute('max'));
                
                if (action === 'plus' && currentValue < max) {
                    input.value = currentValue + 1;
                    updateQuantity(id, input.value);
                } else if (action === 'minus') {
                    if (currentValue <= 1) {
                        removeItem(id);
                    } else {
                        input.value = currentValue - 1;
                        updateQuantity(id, input.value);
                    }
                }
            }
        });
    }
});
</script>

<?php include_once '../includes/footer.php'; ?>