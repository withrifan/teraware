</main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cari elemen notifikasi berdasarkan ID yang kita buat tadi
            const alertBox = document.getElementById('auto-dismiss-alert');

            // Jika notifikasi ada di halaman
            if (alertBox) {
                // Tunggu 3.5 detik (3500 milidetik)
                setTimeout(() => {
                    // Mulai proses fade out (menghilang perlahan)
                    alertBox.style.transition = 'opacity 0.5s ease';
                    alertBox.style.opacity = '0';
                    
                    // Setelah animasi fade out selesai, hapus elemen sepenuhnya
                    setTimeout(() => {
                        alertBox.remove();
                    }, 500); // 0.5 detik, sesuai durasi transisi CSS
                }, 3500);
            }
        });
    </script>
    </body>
</html>