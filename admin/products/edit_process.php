<?php
session_start();
require_once '../../config/database.php';
require_once '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = (int) $_POST['product_id'];
    $name = pg_escape_string($dbconn, $_POST['name']);
    $category_id = (int) $_POST['category_id'];
    $price = (float) $_POST['price'];
    $stock = (int) $_POST['stock'];
    $description = pg_escape_string($dbconn, $_POST['description'] ?? '');
    $specifications = pg_escape_string($dbconn, $_POST['specifications'] ?? '');

    $project_root = dirname(dirname(__DIR__));
    $target_dir = $project_root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
    $new_image_paths = []; // Simpan path baru di sini

    pg_query($dbconn, "BEGIN");
    try {
        // Update data teks produk
        $update_product_query = "UPDATE products SET name = $1, description = $2, specifications = $3, price = $4, stock = $5, category_id = $6 WHERE product_id = $7";
        pg_query_params($dbconn, $update_product_query, array($name, $description, $specifications, $price, $stock, $category_id, $product_id));

        // Cek jika ada gambar baru yang di-upload
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {

            // Validasi jumlah file baru
            if (count($_FILES['images']['name']) > 5) {
                throw new Exception("Maksimal 5 gambar yang boleh diupload.");
            }

            // 1. Ambil dan Hapus gambar lama (file & database)
            $old_images_query = "SELECT image_path FROM product_images WHERE product_id = $1";
            $old_images_result = pg_query_params($dbconn, $old_images_query, array($product_id));
            if ($old_images_result) {
                while ($img = pg_fetch_assoc($old_images_result)) {
                    $full_old_path = $project_root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $img['image_path']);
                    if (file_exists($full_old_path)) {
                        unlink($full_old_path);
                    }
                }
            }
            $delete_old_images_query = "DELETE FROM product_images WHERE product_id = $1";
            pg_query_params($dbconn, $delete_old_images_query, array($product_id));

            // 2. Proses upload gambar baru
            $image_count = count($_FILES['images']['name']);
            for ($i = 0; $i < $image_count; $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES["images"]["tmp_name"][$i];
                    $original_name = basename($_FILES["images"]["name"][$i]);
                    $image_name = uniqid() . '_' . $original_name;
                    $target_file = $target_dir . $image_name;

                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $image_path = "uploads/products/" . $image_name;
                        // 3. Simpan path baru ke database
                        $insert_image_query = "INSERT INTO product_images (product_id, image_path) VALUES ($1, $2)";
                        pg_query_params($dbconn, $insert_image_query, array($product_id, $image_path));
                        $new_image_paths[] = $image_path; // Simpan path jika berhasil
                    } else {
                        throw new Exception("Gagal memindahkan file baru: " . $original_name);
                    }
                } else {
                    throw new Exception("Error upload pada file baru: " . $_FILES['images']['name'][$i] . " Kode: " . $_FILES['images']['error'][$i]);
                }
            }
        }

        // Jika semua berhasil
        pg_query($dbconn, "COMMIT");
        header('Location: index.php?success=Produk berhasil diperbarui.');
        exit();

    } catch (Exception $e) {
        pg_query($dbconn, "ROLLBACK");
        // Hapus file baru yang mungkin sudah terlanjur di-upload jika terjadi error
        foreach ($new_image_paths as $path) {
            $full_path = $project_root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
            if (file_exists($full_path)) {
                unlink($full_path);
            }
        }
        header('Location: edit.php?id=' . $product_id . '&error=' . urlencode($e->getMessage()));
        exit();
    }
}
?>