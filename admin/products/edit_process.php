<?php
session_start();
require_once '../../config/database.php';
require_once '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = (int)$_POST['product_id'];
    $name = pg_escape_string($dbconn, $_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    
    // AMBIL DATA DESKRIPSI & SPESIFIKASI (BARU)
    $description = pg_escape_string($dbconn, $_POST['description'] ?? '');
    $specifications = pg_escape_string($dbconn, $_POST['specifications'] ?? '');
    
    $old_image_path = $_POST['old_image_path'];
    $new_image_path = $old_image_path;
    $is_new_image_uploaded = false;
    
    $project_root = dirname(dirname(__DIR__));

    // Cek jika ada gambar baru di-upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
        $target_dir = $project_root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
        $image_name = uniqid() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $full_old_path = $project_root . DIRECTORY_SEPARATOR . $old_image_path;
            if ($old_image_path && file_exists($full_old_path)) {
                unlink($full_old_path);
            }
            $new_image_path = "uploads/products/" . $image_name;
            $is_new_image_uploaded = true;
        }
    }

    // UPDATE QUERY (DIPERBARUI)
    $update_product_query = "UPDATE products SET name = $1, description = $2, specifications = $3, price = $4, stock = $5, category_id = $6 WHERE product_id = $7";
    pg_query_params($dbconn, $update_product_query, array($name, $description, $specifications, $price, $stock, $category_id, $product_id));

    if ($is_new_image_uploaded) {
        $update_image_query = "UPDATE product_images SET image_path = $1 WHERE product_id = $2";
        pg_query_params($dbconn, $update_image_query, array($new_image_path, $product_id));
    }
    
    header('Location: index.php?success=Produk berhasil diperbarui.');
    exit();
}
?>