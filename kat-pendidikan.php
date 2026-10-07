<?php
// =============================================================
//  FILE: kat-pendidikan.php — KATEGORI PENDIDIKAN
//  FUNGSI: Menampilkan semua berita berkategori 'pendidikan'.
//
//  CARA KERJA:
//  1. Definisikan variabel info kategori ini
//  2. Include _template_kategori.php yang mengurus logika & tampilan
//  3. Template akan memanggil getBeritaByKategori($berita, 'pendidikan')
//     → fungsi itu loop semua $berita, ambil yang kategori = 'pendidikan'
// =============================================================

$nama_kategori    = 'pendidikan';           // Harus sama persis dengan isi 'kategori' di data_berita.php
$label_kategori   = 'Pendidikan';           // Label tampilan (kapital dll)
$deskripsi_kategori = 'Informasi terbaru seputar dunia pendidikan: sekolah, kampus, beasiswa, kurikulum, dan inovasi pembelajaran.';

include '_template_kategori.php';
