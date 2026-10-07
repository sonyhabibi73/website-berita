<?php
// =============================================================
//  FILE: kat-teknologi.php — HALAMAN 6: KATEGORI TEKNOLOGI
//  FUNGSI: Menampilkan semua berita berkategori 'teknologi'.
//
//  CARA KERJA:
//  1. Definisikan variabel info kategori ini
//  2. Include _template_kategori.php yang mengurus logika & tampilan
//  3. Template akan memanggil getBeritaByKategori($berita, 'teknologi')
//     → fungsi itu loop semua $berita, ambil yang kategori = 'teknologi'
// =============================================================

$nama_kategori    = 'teknologi';           // Harus sama persis dengan isi 'kategori' di data_berita.php
$label_kategori   = 'Teknologi';           // Label tampilan (kapital dll)
$deskripsi_kategori = 'Berita terkini seputar perkembangan teknologi, gadget, startup, kecerdasan buatan, dan dunia digital.';

// Include template — dia yang urus selebihnya
include '_template_kategori.php';
