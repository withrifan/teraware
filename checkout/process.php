<?php
session_start();
require_once '../config/database.php';

// Wajib login
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $checkout_type = $_POST['checkout_type'];
    $cart_items = [];
    $total_price = 0;

    // Mulai transaksi database
    pg_query($dbconn, "BEGIN");

    try {
        // 1. Ambil item berdasarkan tipe checkout
        if ($checkout_type == 'buy_now' && isset($_SESSION['buy_now_item'])) {
            // Logika ambil item "Beli Langsung" dari session
            $item = $_SESSION['buy_now_item'];
            $product_query = "SELECT price, stock FROM products WHERE product_id = $1";
            $product_result = pg_query_params($dbconn, $product_query, array($item['product_id']));
            $product_data = pg_fetch_assoc($product_result);

            $cart_items[] = [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $product_data['price'],
                'stock' => $product_data['stock']
            ];
            $total_price = $product_data['price'] * $item['quantity'];
        } else {
            // Logika ambil item dari keranjang utama (database)
            $cart_query = "SELECT ci.product_id, ci.quantity, p.price, p.stock FROM cart_items ci JOIN products p ON ci.product_id = p.product_id WHERE ci.user_id = $1";
            $cart_result = pg_query_params($dbconn, $cart_query, array($user_id));
            
            if (pg_num_rows($cart_result) == 0) {
                throw new Exception("Keranjang kosong.");
            }
            
            while($item = pg_fetch_assoc($cart_result)) {
                $cart_items[] = $item;
                $total_price += $item['price'] * $item['quantity'];
            }
        }
        
        if (empty($cart_items)) { 
            throw new Exception("Keranjang kosong."); 
        }

        // 2. Cek ketersediaan stok untuk semua item
        foreach ($cart_items as $item) {
            if ($item['stock'] < $item['quantity']) {
                throw new Exception("Stok produk tidak mencukupi.");
            }
        }

        // 3. Buat pesanan baru di tabel 'transactions'
        $shipping_cost = 0; // Gratis ongkir
        $order_status = 'diproses';
        $insert_trans_query = "INSERT INTO transactions (user_id, sub_total, shipping_cost, total_price, order_status) VALUES ($1, $2, $3, $4, $5) RETURNING transaction_id";
        $trans_result = pg_query_params($dbconn, $insert_trans_query, array($user_id, $total_price, $shipping_cost, $total_price + $shipping_cost, $order_status));
        
        if (!$trans_result) {
            throw new Exception("Gagal membuat transaksi.");
        }
        $transaction_id = pg_fetch_result($trans_result, 0, 'transaction_id');

        // 4. Pindahkan item ke 'transaction_items' dan kurangi stok
        foreach ($cart_items as $item) {
            // Pindahkan item
            $insert_item_query = "INSERT INTO transaction_items (transaction_id, product_id, quantity, price_per_item, total_item_price) VALUES ($1, $2, $3, $4, $5)";
            $item_result = pg_query_params($dbconn, $insert_item_query, array($transaction_id, $item['product_id'], $item['quantity'], $item['price'], $item['price'] * $item['quantity']));
            
            if (!$item_result) {
                throw new Exception("Gagal menyimpan item transaksi.");
            }

            // Kurangi stok
            $new_stock = $item['stock'] - $item['quantity'];
            $update_stock_query = "UPDATE products SET stock = $1 WHERE product_id = $2";
            $stock_result = pg_query_params($dbconn, $update_stock_query, array($new_stock, $item['product_id']));

            if (!$stock_result) {
                throw new Exception("Gagal memperbarui stok produk.");
            }
        }

        // 5. Bersihkan data berdasarkan tipe checkout
        if ($checkout_type == 'buy_now') {
            unset($_SESSION['buy_now_item']); // Hapus session "Beli Langsung"
        } else {
            $clear_cart_query = "DELETE FROM cart_items WHERE user_id = $1"; // Kosongkan keranjang utama
            pg_query_params($dbconn, $clear_cart_query, array($user_id));
        }

        // Jika semua berhasil, commit transaksi
        pg_query($dbconn, "COMMIT");

        // Redirect ke halaman riwayat pesanan
        header('Location: /buyer/orders.php?success=Pesanan Anda berhasil dibuat!');
        exit();

    } catch (Exception $e) {
        // Jika ada error, rollback semua perubahan
        pg_query($dbconn, "ROLLBACK");
        header('Location: /cart/index.php?error=' . urlencode($e->getMessage()));
        exit();
    }
}
?>