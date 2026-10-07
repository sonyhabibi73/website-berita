<?php
// =============================================================
//  FILE: index.php — HALAMAN 1: BERANDA / HIGHLIGHT
//  FUNGSI: Menampilkan berita terbaru sebagai HEADLINE besar,
//          dan sisa berita lainnya dalam grid kartu di bawahnya.
//
//  ALUR LOGIKA:
//  1. Include data_berita.php → dapat array $berita & fungsi helper
//  2. Urutkan semua berita dari terbaru ke terlama
//  3. Ambil berita pertama (index 0) → dijadikan HEADLINE
//  4. Sisa berita (index 1 dst) → tampil di GRID kartu
// =============================================================

// Sertakan file data berita (path relatif dari root/index.php)
require_once 'data_berita.php';

// Variabel untuk header.php: judul tab browser & menu aktif
$page_title    = 'Beranda';
$halaman_aktif = 'beranda';

// LANGKAH 1: Urutkan semua berita dari terbaru ke terlama
$semua_berita = getAllBerita($berita); // fungsi dari data_berita.php

// LANGKAH 2: Pisahkan headline dan berita grid
// array_shift() mengambil & menghapus elemen PERTAMA dari array
$headline    = array_shift($semua_berita); // berita terbaru → headline
$grid_berita = $semua_berita;              // sisa → grid kartu

// Sertakan header (harus setelah variabel $page_title & $halaman_aktif didefinisikan)
include 'includes/header.php';
?>

<!-- ============================================================
     SECTION HEADLINE — BERITA UTAMA BESAR
     ============================================================ -->
<section class="headline-section">
    <div class="container">
        <div class="headline-badge">BERITA UTAMA</div>

        <article class="headline-card">
            <!-- Gambar Headline -->
            <div class="headline-img-wrap">
                <img
                    src="<?= htmlspecialchars($headline['gambar']) ?>"
                    alt="<?= htmlspecialchars($headline['judul']) ?>"
                    class="headline-img"
                    onerror="this.src='assets/img/placeholder.jpg'"
                >
                <!-- Badge kategori di atas gambar -->
                <span class="badge badge-<?= $headline['kategori'] ?>">
                    <?= ucfirst($headline['kategori']) ?>
                </span>
            </div>

            <!-- Isi Teks Headline -->
            <div class="headline-body">
                <time class="headline-date">
                     <?= formatTanggal($headline['tanggal']) ?>
                </time>

                <!-- htmlspecialchars() untuk mencegah XSS attack -->
                <h1 class="headline-title">
                    <?= htmlspecialchars($headline['judul']) ?>
                </h1>

                <p class="headline-excerpt">
                    <?= htmlspecialchars($headline['isi_singkat']) ?>
                </p>

                <!-- Link ke halaman detail, kirim id berita via URL query string -->
                <a href="detail.php?id=<?= $headline['id'] ?>" class="btn btn-primary">
                    Baca Selengkapnya →
                </a>
            </div>
        </article>

    </div>
</section>

<!-- ============================================================
     SECTION GRID BERITA LAINNYA
     ============================================================ -->
<section class="grid-section">
    <div class="container">
        <h2 class="section-title">Berita Terkini Lainnya</h2>

        <?php if (empty($grid_berita)): ?>
            <!-- Jika tidak ada berita selain headline -->
            <p class="empty-msg">Belum ada berita lain. Tambahkan data di data_berita.php!</p>

        <?php else: ?>
            <!-- GRID KARTU BERITA
                 Loop foreach menampilkan setiap berita dalam $grid_berita
                 sebagai kartu yang bisa diklik menuju halaman detail -->
            <div class="berita-grid">
                <?php foreach ($grid_berita as $artikel): ?>
                <article class="berita-card">
                    <!-- Gambar kartu -->
                    <a href="detail.php?id=<?= $artikel['id'] ?>" class="card-img-link">
                        <img
                            src="<?= htmlspecialchars($artikel['gambar']) ?>"
                            alt="<?= htmlspecialchars($artikel['judul']) ?>"
                            class="card-img"
                            onerror="this.src='assets/img/placeholder.jpg'"
                        >
                        <span class="badge badge-<?= $artikel['kategori'] ?>">
                            <?= ucfirst($artikel['kategori']) ?>
                        </span>
                    </a>

                    <!-- Isi kartu -->
                    <div class="card-body">
                        <time class="card-date">
                            <?= formatTanggal($artikel['tanggal']) ?>
                        </time>
                        <h3 class="card-title">
                            <a href="detail.php?id=<?= $artikel['id'] ?>">
                                <?= htmlspecialchars($artikel['judul']) ?>
                            </a>
                        </h3>
                        <p class="card-excerpt">
                            <?= htmlspecialchars(truncate($artikel['isi_singkat'], 110)) ?>
                        </p>
                        <a href="detail.php?id=<?= $artikel['id'] ?>" class="card-link">
                            Selengkapnya →
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div><!-- /berita-grid -->

        <?php endif; ?>

    </div>
</section>

<?php
// Sertakan footer — selalu di paling bawah sebelum tag tutup
include 'includes/footer.php';
?>
