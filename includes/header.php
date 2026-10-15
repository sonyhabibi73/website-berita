<?php
// =============================================================
//  FILE: includes/header.php
//  FUNGSI: Menampilkan bagian atas (header) yang sama di
//          semua halaman — logo, navigasi, dan hamburger menu.
//
//  CARA PAKAI: Di setiap halaman .php, tulis:
//      <?php include 'includes/header.php'; ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Title halaman dinamis: setiap halaman bisa set $page_title sendiri -->
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' — ' : '' ?>KimiNEWS</title>

    <!-- Google Fonts: Schibsted Grotesk — satu keluarga, dipakai perusahaan berita -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Schibsted+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="assets/img/favicon.png" type="image/png">
    
    <!-- CSS Utama -->
    <link rel="stylesheet" href="assets/css/style.css?v=20261016c">
</head>
<body>

<!-- ============================================================
     HEADER / NAVBAR
     Struktur: logo di kiri | nav links di kanan | hamburger di HP
     ============================================================ -->
<header class="site-header">
    <div class="container header-inner">

        <!-- TOMBOL HAMBURGER (hanya muncul di layar kecil / HP)
             DI KIRI agar tidak ketutup nav drawer yang slide dari kiri.
             Saat diklik, JavaScript akan toggle class 'nav-open' di nav -->
        <button class="hamburger" id="hamburgerBtn" aria-label="Buka menu navigasi">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- LOGO / NAMA SITUS (di kanan di HP, di kiri di desktop) -->
        <a href="index.php" class="logo">
            <span class="logo-text">Kimi<strong>NEWS</strong></span>
        </a>

        <!-- NAVIGASI UTAMA
             Class 'aktif' akan ditambahkan oleh PHP sesuai $halaman_aktif -->
        <nav class="main-nav" id="mainNav">

            <a href="index.php"
               class="nav-link <?= $halaman_aktif === 'beranda' ? 'aktif' : '' ?>">
                Beranda
            </a>

            <!-- Kategori pindah ke rel di bawah header supaya nav muat satu baris -->
            <a href="galeri.php"
               class="nav-link <?= $halaman_aktif === 'galeri' ? 'aktif' : '' ?>">
                 Galeri
            </a>

            <a href="video.php"
               class="nav-link <?= $halaman_aktif === 'video' ? 'aktif' : '' ?>">
                 Video
            </a>

            <a href="indeks.php"
               class="nav-link <?= $halaman_aktif === 'indeks' ? 'aktif' : '' ?>">
                 Indeks
            </a>

            <!-- Asisten Berita TIDAK di sini: aksesnya lewat tombol
                 mengambang (FAB) yang dirender includes/chat.php -->

            <a href="tentang.php"
               class="nav-link <?= $halaman_aktif === 'tentang' ? 'aktif' : '' ?>">
                Tentang
            </a>

            <a href="kirim-tips.php"
               class="nav-link <?= $halaman_aktif === 'kirim-tips' ? 'aktif' : '' ?>">
                 Kirim Tips
            </a>

            <a href="kontak.php"
               class="nav-link <?= $halaman_aktif === 'kontak' ? 'aktif' : '' ?>">
                Kontak
            </a>

        </nav><!-- /main-nav -->
    </div><!-- /container -->

    <!-- REL KATEGORI — 6 kanal selalu terlihat, tanpa dropdown -->
    <div class="cat-rail">
        <div class="container cat-rail-inner">
            <?php
            $kategori_semua = ['teknologi', 'politik', 'olahraga', 'kesehatan', 'pendidikan', 'hiburan'];
            foreach ($kategori_semua as $k):
                $aktif_rail = ($halaman_aktif === 'kategori' && isset($nama_kategori) && $nama_kategori === $k);
            ?>
                <a href="kat-<?= $k ?>.php" class="cat-link <?= $aktif_rail ? 'aktif' : '' ?>">
                    <?= ucfirst($k) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</header>

<!-- Overlay gelap saat menu HP terbuka (klik untuk menutup) -->
<div class="nav-overlay" id="navOverlay"></div>

<!-- JavaScript untuk hamburger menu -->
<script src="assets/js/script.js?v=20261016c" defer></script>

<!-- KONTEN HALAMAN MULAI DI SINI -->
<main class="site-main">
