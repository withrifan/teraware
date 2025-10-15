<?php
require_once __DIR__ . 'admin\includes\auth_check.php'; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Teraware</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS BARU UNTUK TAMPILAN ADMIN YANG LEBIH BAIK */
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #343a40; /* Warna gelap untuk sidebar */
        }
        .sidebar-sticky {
            height: calc(100vh - 48px);
            overflow-x: hidden;
            overflow-y: auto;
        }
        .sidebar .nav-link {
            font-weight: 500;
            color: #adb5bd; /* Warna teks abu-abu */
            padding: .75rem 1.5rem;
            display: flex;
            align-items: center;
        }
        .sidebar .nav-link .fa-fw {
            width: 1.25rem;
            margin-right: 0.75rem;
            opacity: 0.6;
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: #495057;
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: #0d6efd; /* Warna biru untuk link aktif */
        }
        .main-content {
            padding-top: 24px;
        }
    </style>
</head>
<body>
    
<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="/admin/index.php">TERAWARE Admin Panel</a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="navbar-nav w-100">
        <div class="nav-item text-nowrap d-flex justify-content-end">
            <a class="nav-link px-3" href="/auth/logout.php">Sign out <i class="fas fa-sign-out-alt ms-1"></i></a>
        </div>
    </div>
</header>

<div class="container-fluid">
    <div class="row">
        <?php include_once 'sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
         

<?php
// Mendapatkan path script yang sedang berjalan untuk menandai link aktif
$current_page = $_SERVER['PHP_SELF'];
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky pt-3 sidebar-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($current_page, '/admin/index.php') !== false) ? 'active' : ''; ?>" href="/admin/index.php">
                    <i class="fas fa-tachometer-alt fa-fw"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($current_page, '/admin/products') !== false) ? 'active' : ''; ?>" href="/admin/products/index.php">
                    <i class="fas fa-box-open fa-fw"></i> Manajemen Produk
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($current_page, '/admin/orders') !== false) ? 'active' : ''; ?>" href="/admin/orders/index.php">
                    <i class="fas fa-file-invoice-dollar fa-fw"></i> Manajemen Pesanan
                </a>
            </li>
        </ul>
    </div>
</nav>