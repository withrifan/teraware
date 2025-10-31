<?php
session_start();
require_once '../../config/database.php';
require_once '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Validasi jumlah file
    if (!isset($_FILES['images']) || count($_FILES['images']['name']) == 0 || count($_FILES['images']['name']) > 5) {
        header('Location: add.php?error=Harap pilih 1 hingga 5 gambar.');
        exit();
    }

    // Ambil data form
    $name = pg_escape_string($dbconn, $_POST['name']);
    $category_id = (int) $_POST['category_id'];
    $price = (float) $_POST['price'];
    $stock = (int) $_POST['stock'];
    $description = pg_escape_string($dbconn, $_POST['description']);
    $specifications = pg_escape_string($dbconn, $_POST['specifications']);

    $project_root = dirname(dirname(__DIR__));
    $target_dir = $project_root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
    $uploaded_paths = []; // Array untuk menyimpan path gambar yang berhasil diupload

    pg_query($dbconn, "BEGIN"); // Mulai transaksi

    try {
        // 1. Simpan data produk utama
        $insert_product_query = "INSERT INTO products (category_id, name, description, specifications, price, stock, condition, rating) VALUES ($1, $2, $3, $4, $5, $6, 'baru', 4.5) RETURNING product_id";
        $product_result = pg_query_params($dbconn, $insert_product_query, array($category_id, $name, $description, $specifications, $price, $stock));

        if (!$product_result)
            throw new Exception("Gagal menyimpan data produk.");

        $product_id = pg_fetch_result($product_result, 0, 'product_id');

        // 2. Proses upload setiap gambar
        $image_count = count($_FILES['images']['name']);
        for ($i = 0; $i < $image_count; $i++) {
            // Cek error individual file
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES["images"]["tmp_name"][$i];
                $original_name = basename($_FILES["images"]["name"][$i]);
                $image_name = uniqid() . '_' . $original_name;
                $target_file = $target_dir . $image_name;

                if (move_uploaded_file($tmp_name, $target_file)) {
                    $image_path = "uploads/products/" . $image_name;
                    $uploaded_paths[] = $image_path; // Simpan path jika berhasil

                    // 3. Simpan path gambar ke database
                    $insert_image_query = "INSERT INTO product_images (product_id, image_path) VALUES ($1, $2)";
                    pg_query_params($dbconn, $insert_image_query, array($product_id, $image_path));
                } else {
                    throw new Exception("Gagal memindahkan file gambar: " . $original_name);
                }
            } else {
                throw new Exception("Error upload pada file: " . $_FILES['images']['name'][$i] . " Kode: " . $_FILES['images']['error'][$i]);
            }
        }

        // Jika semua berhasil
        pg_query($dbconn, "COMMIT");
        header('Location: index.php?success=Produk berhasil ditambahkan.');
        exit();

    } catch (Exception $e) {
        pg_query($dbconn, "ROLLBACK"); // Batalkan semua jika ada error

        // Hapus file yang sudah terlanjur di-upload
        foreach ($uploaded_paths as $path) {
            $full_path = $project_root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
            if (file_exists($full_path)) {
                unlink($full_path);
            }
        }

        header('Location: add.php?error=' . urlencode($e->getMessage()));
        exit();
    }
}
?>