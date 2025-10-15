<?php
// Selalu mulai session di awal
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Ambil data kategori untuk navigasi
$category_query = "SELECT category_id, category_name FROM categories ORDER BY category_name ASC";
$category_result = pg_query($dbconn, $category_query);
$categories = pg_fetch_all($category_result);

// Hitung jumlah item di keranjang jika user login
$cart_item_count = 0;
if (isset($_SESSION['user_id'])) {
    $count_query = "SELECT SUM(quantity) as total_items FROM cart_items WHERE user_id = $1";
    $count_result = pg_query_params($dbconn, $count_query, array($_SESSION['user_id']));
    if ($count_result) {
        $count_row = pg_fetch_assoc($count_result);
        $cart_item_count = $count_row['total_items'] ? (int)$count_row['total_items'] : 0;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Teraware - Laptop, PC & Hardware Komputer</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet"> 
</head>
<body>
    <header class="header sticky-top bg-light shadow-sm">
        <div class="header-main py-3">
            <div class="container d-flex justify-content-between align-items-center">
                <a href="/index.php" class="logo text-decoration-none h2 fw-bold text-primary">TERAWARE</a>
                
                <form action="/products.php" method="GET" class="flex-grow-1 mx-4" style="max-width: 500px;">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Cari laptop, PC, hardware..." name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </form>

                <div class="header-actions d-flex align-items-center">
                    <a href="/cart/index.php" class="action-btn text-decoration-none text-dark me-3">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Keranjang</span>
                        <?php if ($cart_item_count > 0): ?>
                            <span class="badge bg-danger rounded-pill ms-1" id="cartBadge"><?php echo $cart_item_count; ?></span>
                        <?php else: ?>
                            <span class="badge bg-danger rounded-pill ms-1" id="cartBadge" style="display: none;">0</span>
                        <?php endif; ?>
                    </a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="dropdown">
                            <a href="#" class="action-btn text-decoration-none text-dark dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user"></i>
                                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/buyer/profile.php">Profil Saya</a></li>
                                <li><a class="dropdown-item" href="/buyer/orders.php">Riwayat Pesanan</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/auth/logout.php">Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="/login.php" class="action-btn text-decoration-none text-dark">
                            <i class="fas fa-user"></i>
                            <span>Login</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <nav class="nav bg-body-tertiary border-top">
            <div class="container">
                <ul class="nav">
                    <li class="nav-item"><a href="/index.php" class="nav-link text-dark">Beranda</a></li>
                    <li class="nav-item"><a href="/products.php" class="nav-link text-dark">Semua Produk</a></li>
                    <?php if ($categories): ?>
                        <?php foreach ($categories as $category): ?>
                            <li class="nav-item"><a href="/products.php?category=<?php echo $category['category_id']; ?>" class="nav-link text-dark"><?php echo htmlspecialchars($category['category_name']); ?></a></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>