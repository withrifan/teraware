<?php
// Ganti bagian atas file checkout/index.php Anda dengan ini

require_once '../config/database.php';
include_once '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php?error=Silakan login untuk melanjutkan.');
    exit();
}

$user_id = $_SESSION['user_id'];
$checkout_type = isset($_GET['type']) && $_GET['type'] == 'buy_now' ? 'buy_now' : 'cart';
$cart_items = [];
$total_price = 0;

// Ambil data user untuk pre-fill form
$user_query = "SELECT name, phone_number, address FROM users WHERE user_id = $1";
$user_result = pg_query_params($dbconn, $user_query, array($user_id));
$user_data = pg_fetch_assoc($user_result);

if ($checkout_type == 'buy_now' && isset($_SESSION['buy_now_item'])) {
    // Logika untuk "Beli Langsung"
    $item = $_SESSION['buy_now_item'];
    $product_id = $item['product_id'];
    $quantity = $item['quantity'];

    $product_query = "SELECT name, price FROM products WHERE product_id = $1";
    $product_result = pg_query_params($dbconn, $product_query, array($product_id));
    
    if ($product_data = pg_fetch_assoc($product_result)) {
        $subtotal = $product_data['price'] * $quantity;
        $total_price = $subtotal;
        $cart_items[] = [
            'name' => $product_data['name'],
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
} else {
    // Logika untuk keranjang utama (yang sudah ada sebelumnya)
    $cart_query = "SELECT p.name, p.price, ci.quantity, (p.price * ci.quantity) as subtotal FROM cart_items ci JOIN products p ON ci.product_id = p.product_id WHERE ci.user_id = $1";
    $cart_result = pg_query_params($dbconn, $cart_query, array($user_id));
    if ($cart_result && pg_num_rows($cart_result) > 0) {
        while($item = pg_fetch_assoc($cart_result)) {
            $cart_items[] = $item;
            $total_price += $item['subtotal'];
        }
    }
}

if (empty($cart_items)) {
    header('Location: /cart/index.php?error=Keranjang Anda kosong.');
    exit();
}

function format_rupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}
?>

<main class="container my-5">
    <h1 class="mb-4">Checkout</h1>
    <form action="process.php" method="POST">
        <input type="hidden" name="checkout_type" value="<?php echo $checkout_type; ?>">
        
        <div class="row">
            <div class="col-md-7">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Detail Pengiriman</h4>
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Penerima</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Nomor HP</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user_data['phone_number']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat Lengkap</label>
                            <textarea class="form-control" id="address" name="address" rows="4" required><?php echo htmlspecialchars($user_data['address']); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Ringkasan Pesanan</h4>
                        <?php
                        foreach ($cart_items as $item) {
                        ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                            <span><?php echo format_rupiah($item['subtotal']); ?></span>
                        </div>
                        <?php } ?>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span>Subtotal</span>
                            <strong><?php echo format_rupiah($total_price); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Ongkos Kirim</span>
                            <strong class="text-success">GRATIS</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between h5">
                            <strong>Total Pembayaran</strong>
                            <strong><?php echo format_rupiah($total_price); ?></strong>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Buat Pesanan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</main>

<?php include_once '../includes/footer.php'; ?>