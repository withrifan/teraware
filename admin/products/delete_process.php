<?php
session_start();
require_once '../../config/database.php';
require_once '../includes/auth_check.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id == 0) {
    header('Location: index.php');
    exit();
}

pg_query($dbconn, "BEGIN");

try {
    // 1. Ambil path gambar untuk dihapus dari server
    $image_query = "SELECT image_path FROM product_images WHERE product_id = $1";
    $image_result = pg_query_params($dbconn, $image_query, array($product_id));
    
    if (pg_num_rows($image_result) > 0) {
        $image_data = pg_fetch_assoc($image_result);
        $image_path = "../../" . $image_data['image_path'];
        
        // 2. Hapus file gambar dari folder uploads
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    // 3. Hapus data dari database. 
    // Karena ada ON DELETE CASCADE, data di product_images akan ikut terhapus.
    $delete_query = "DELETE FROM products WHERE product_id = $1";
    $delete_result = pg_query_params($dbconn, $delete_query, array($product_id));

    if (!$delete_result) {
        throw new Exception("Gagal menghapus produk dari database.");
    }

    pg_query($dbconn, "COMMIT");
    header('Location: index.php?success=Produk berhasil dihapus.');
    exit();

} catch (Exception $e) {
    pg_query($dbconn, "ROLLBACK");
    header('Location: index.php?error=' . urlencode($e->getMessage()));
    exit();
}
?>