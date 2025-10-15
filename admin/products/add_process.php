<?php
session_start();
require_once '../../config/database.php';
require_once '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Proses upload gambar
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
        
        $image_name = uniqid() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        
        // Pindahkan file yang di-upload
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = "uploads/products/" . $image_name;
            
            // Ambil data form
            $name = pg_escape_string($dbconn, $_POST['name']);
            $category_id = (int)$_POST['category_id'];
            $price = (float)$_POST['price'];
            $stock = (int)$_POST['stock'];
            $description = pg_escape_string($dbconn, $_POST['description']);
            $specifications = pg_escape_string($dbconn, $_POST['specifications']);
            
            pg_query($dbconn, "BEGIN");
            
            $insert_product_query = "INSERT INTO products (category_id, name, description, specifications, price, stock, condition, rating) VALUES ($1, $2, $3, $4, $5, $6, 'baru', 4.5) RETURNING product_id";
            $product_result = pg_query_params($dbconn, $insert_product_query, array($category_id, $name, $description, $specifications, $price, $stock));
            
            if ($product_result) {
                $product_id = pg_fetch_result($product_result, 0, 'product_id');
                $insert_image_query = "INSERT INTO product_images (product_id, image_path) VALUES ($1, $2)";
                pg_query_params($dbconn, $insert_image_query, array($product_id, $image_path));
                
                pg_query($dbconn, "COMMIT");
                header('Location: index.php?success=Produk berhasil ditambahkan.');
                exit();
            }
        }
    }
    // Jika ada kegagalan di mana pun, batalkan dan beri pesan error
    pg_query($dbconn, "ROLLBACK");
    header('Location: add.php?error=Gagal mengupload gambar atau menyimpan produk.');
    exit();
}
?>