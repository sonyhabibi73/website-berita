<?php
// =============================================================
//  FILE: koneksi.example.php — TEMPLATE KONEKSI DATABASE
//
//  CARA SETUP:
//     cp koneksi.example.php koneksi.php
//
//  File koneksi.php masuk .gitignore karena berisi kredensial.
//  Isi nilai di bawah sesuai lingkungan kamu, lalu ganti nama
//  filinya jadi koneksi.php.
//
//  DATABASE 'PESAN' BELUM PUNYA FILE DUMP. Buat tabelnya dengan
//  pernyataan CREATE TABLE yang ada di README.md, atau dari MySQL:
//     mysql -uroot -e "CREATE DATABASE IF NOT EXISTS PESAN"
// =============================================================
$host     = "localhost";
$username = "root";       // Bawaan XAMPP default-nya root
$password = "";           // Bawaan XAMPP default-nya kosong
$database = "PESAN";      // Huruf kapital, sesuai aslinya

$koneksi = mysqli_connect($host, $username, $password, $database);

// Cek jika koneksi gagal biar ketahuan salahnya di mana
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>
