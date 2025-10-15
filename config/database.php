<?php

$host = 'localhost';
$port = '5432';
$dbname = 'terawaredb';
$user = 'teraware';
$password = 'Tera100%'; 

// String koneksi
$conn_string = "host={$host} port={$port} dbname={$dbname} user={$user} password={$password}";

// Membuat koneksi
$dbconn = pg_connect($conn_string);

// Cek koneksi
if (!$dbconn) {
    die("Koneksi ke database gagal: " . pg_last_error());
}

// Set timezone agar sesuai dengan waktu di Indonesia
pg_query($dbconn, "SET TIME ZONE 'Asia/Jakarta'");

?>