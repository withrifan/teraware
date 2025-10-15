<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Teraware</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .register-container {
            max-width: 500px;
            margin: 5rem auto;
            padding: 2rem;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <h2 class="text-center mb-4">Buat Akun TERAWARE</h2>
            
            <?php
            // Menampilkan pesan error jika ada
            if (isset($_GET['error'])) {
                echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
            }
            ?>

            <form action="auth/register_process.php" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Nomor HP</label>
                    <input type="text" class="form-control" id="phone" name="phone">
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Alamat Pengiriman</label>
                    <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Daftar</button>
                </div>
            </form>
            <p class="text-center mt-3">
                Sudah punya akun? <a href="login.php">Masuk di sini</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>